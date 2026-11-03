<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Services\CouponRedeemer;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CouponUsageTest extends TestCase
{
    use RefreshDatabase;

    public function test_checking_is_read_only_and_normalizes_code(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'HEMAT20']);
        $this->get('/periksa')->assertOk()->assertSee('Hasil akan tampil di sini.');
        $this->post('/periksa', ['code' => ' hemat20 '])->assertOk()->assertSee('data-status="valid"', false)->assertSee('20%');
        $this->assertSame(0, $coupon->fresh()->used_count);
        $this->post('/periksa', ['code' => 'TIDAKADA'])->assertOk()->assertSee('Kode kupon tidak ditemukan.');
        $this->post('/periksa', ['code' => ''])->assertSessionHasErrors('code');
    }

    public function test_successful_simulations_increment_usage_until_limit_then_reject(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'DUAKALI', 'max_uses' => 2]);
        foreach ([1, 2] as $expected) {
            $this->post('/simulasi', ['code' => 'duakali'])->assertRedirect('/periksa')->assertSessionHas('simulation.success', true);
            $this->assertSame($expected, $coupon->fresh()->used_count);
        }
        $this->get('/periksa')->assertOk()->assertSee('Simulasi berhasil.')->assertSee('Batas penggunaan kupon sudah habis.');
        $this->post('/simulasi', ['code' => 'DUAKALI'])->assertSessionHas('simulation.success', false);
        $this->assertSame(2, $coupon->fresh()->used_count);
    }

    #[DataProvider('unusableStates')]
    public function test_unusable_coupons_are_rejected_without_consuming_usage(string $state, string $message): void
    {
        $attributes = match ($state) {
            'inactive' => ['is_active' => false],
            'upcoming' => ['starts_on' => now()->addDay()->toDateString()],
            'expired' => ['starts_on' => now()->subDays(2)->toDateString(), 'ends_on' => now()->subDay()->toDateString()],
            'exhausted' => ['max_uses' => 2, 'used_count' => 2],
        };
        $coupon = Coupon::factory()->create($attributes);
        $before = $coupon->used_count;
        $this->post('/periksa', ['code' => $coupon->code])->assertOk()->assertSee($message);
        $this->post('/simulasi', ['code' => $coupon->code])->assertSessionHas('simulation.success', false);
        $this->assertSame($before, $coupon->fresh()->used_count);
    }

    public static function unusableStates(): array
    {
        return [['inactive', 'Kupon dinonaktifkan'], ['upcoming', 'Kupon belum berlaku'],
            ['expired', 'Masa berlaku kupon sudah berakhir.'], ['exhausted', 'Batas penggunaan kupon sudah habis.']];
    }

    public function test_unknown_and_invalid_codes_do_not_consume_anything(): void
    {
        $this->post('/simulasi', ['code' => 'TAKADA'])->assertSessionHas('simulation.success', false);
        $this->post('/simulasi', ['code' => ['SALAH']])->assertSessionHasErrors('code');
        $this->assertDatabaseCount('coupons', 0);
    }

    public function test_dates_are_inclusive_until_jakarta_midnight(): void
    {
        $this->travelTo(Carbon::parse('2026-11-03 00:00:00', 'Asia/Jakarta'));
        $coupon = Coupon::factory()->create(['starts_on' => '2026-11-03', 'ends_on' => '2026-11-04']);
        $redeemer = app(CouponRedeemer::class);
        $this->assertTrue($redeemer->redeem($coupon->code)['success']);
        $this->travelTo(Carbon::parse('2026-11-04 23:59:59', 'Asia/Jakarta'));
        $this->assertTrue($redeemer->redeem($coupon->code)['success']);
        $this->travelTo(Carbon::parse('2026-11-05 00:00:00', 'Asia/Jakarta'));
        $this->assertFalse($redeemer->redeem($coupon->code)['success']);
        $this->assertSame(2, $coupon->fresh()->used_count);
        $this->travelBack();
    }

    public function test_database_blocks_usage_beyond_quota(): void
    {
        $coupon = Coupon::factory()->create(['max_uses' => 1]);
        $this->expectException(QueryException::class);
        DB::table('coupons')->where('id', $coupon->id)->update(['used_count' => 2]);
    }
}
