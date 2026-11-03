<?php

namespace Tests\Feature;

use App\Models\Coupon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CouponCrudTest extends TestCase
{
    use RefreshDatabase;

    private function data(array $overrides = []): array
    {
        return array_merge(['code' => 'HEMAT20', 'discount_type' => 'percentage', 'discount_value' => 20,
            'starts_on' => now()->toDateString(), 'ends_on' => now()->addDays(30)->toDateString(),
            'max_uses' => 10, 'is_active' => 1], $overrides);
    }

    public function test_create_normalizes_code_and_saves_a_coupon(): void
    {
        $this->get('/')->assertOk()->assertSee('Kelola Kupon dengan Mudah.');
        $this->get('/kupon/create')->assertOk();
        $this->post('/kupon', $this->data(['code' => '  hemat20  ']))->assertRedirect('/kupon/1');
        $this->assertDatabaseHas('coupons', ['code' => 'HEMAT20', 'used_count' => 0, 'discount_value' => 20]);
        $this->get('/kupon/1')->assertOk()->assertSee('20%');
    }

    public function test_duplicate_codes_are_rejected_including_different_case(): void
    {
        Coupon::factory()->create(['code' => 'HEMAT20']);
        $this->post('/kupon', $this->data(['code' => 'hemat20']))->assertSessionHasErrors('code');
        $this->assertDatabaseCount('coupons', 1);
    }

    public function test_edit_keeps_usage_and_can_change_type_and_active_status(): void
    {
        $coupon = Coupon::factory()->create(['used_count' => 3]);
        $this->get("/kupon/{$coupon->id}/edit")->assertOk();
        $this->put("/kupon/{$coupon->id}", $this->data(['discount_type' => 'fixed', 'discount_value' => 25000, 'is_active' => 0]))
            ->assertRedirect("/kupon/{$coupon->id}");
        $this->assertDatabaseHas('coupons', ['id' => $coupon->id, 'discount_type' => 'fixed', 'discount_value' => 25000, 'used_count' => 3, 'is_active' => false]);
        $this->get("/kupon/{$coupon->id}")->assertSee('Rp25.000')->assertSee('Nonaktif');
        $this->put("/kupon/{$coupon->id}", $this->data())->assertSessionDoesntHaveErrors('code');
    }

    public function test_edit_cannot_reduce_quota_below_consumed_usage_or_take_another_code(): void
    {
        $coupon = Coupon::factory()->create(['used_count' => 3]);
        Coupon::factory()->create(['code' => 'LAIN20']);
        $this->put("/kupon/{$coupon->id}", $this->data(['max_uses' => 2]))->assertSessionHasErrors('max_uses');
        $this->put("/kupon/{$coupon->id}", $this->data(['code' => 'LAIN20']))->assertSessionHasErrors('code');
        $this->assertSame(100, $coupon->fresh()->max_uses);
    }

    public function test_deletion_has_confirmation_and_removes_the_coupon(): void
    {
        $coupon = Coupon::factory()->create();
        $this->get("/kupon/{$coupon->id}/hapus")->assertOk()->assertSee('Ya, hapus kupon')->assertSee($coupon->code);
        $this->assertDatabaseCount('coupons', 1);
        $this->delete("/kupon/{$coupon->id}")->assertRedirect('/');
        $this->assertDatabaseCount('coupons', 0);
        $this->get("/kupon/{$coupon->id}")->assertNotFound();
    }

    public function test_search_filters_and_pagination_work(): void
    {
        $this->freezeSecond();
        $states = [
            'valid' => [], 'inactive' => ['is_active' => false],
            'upcoming' => ['starts_on' => now()->addDay()->toDateString()],
            'expired' => ['starts_on' => now()->subDays(2)->toDateString(), 'ends_on' => now()->subDay()->toDateString()],
            'exhausted' => ['max_uses' => 2, 'used_count' => 2],
        ];
        foreach ($states as $status => $attributes) {
            Coupon::factory()->create(array_merge($attributes, ['code' => strtoupper($status).'20']));
        }
        foreach ($states as $status => $attributes) {
            $response = $this->get('/?status='.$status)->assertOk()->assertSee(strtoupper($status).'20');
            foreach (array_diff(array_keys($states), [$status]) as $other) {
                $response->assertDontSee(strtoupper($other).'20');
            }
        }
        $this->get('/?q=VALID20')->assertSee('VALID20')->assertDontSee('INACTIVE20');
        $this->get('/?q=takada')->assertSee('Tidak ada kupon yang cocok.');
        foreach (range(1, 12) as $number) {
            Coupon::factory()->create(['code' => 'PAGE'.str_pad($number, 3, '0', STR_PAD_LEFT)]);
        }
        $this->get('/?q=PAGE')->assertSee('PAGE012')->assertDontSee('PAGE001');
        $this->get('/?q=PAGE&page=2')->assertSee('PAGE001')->assertDontSee('PAGE012');
    }

    #[DataProvider('invalidFields')]
    public function test_invalid_input_is_rejected(string $field, mixed $value): void
    {
        $this->post('/kupon', $this->data([$field => $value]))->assertSessionHasErrors($field);
        $this->assertDatabaseCount('coupons', 0);
    }

    public static function invalidFields(): array
    {
        return [
            'kode kosong' => ['code', ''], 'kode pendek' => ['code', 'AB'], 'kode ilegal' => ['code', 'HEMAT!'],
            'kode bukan teks' => ['code', ['HEMAT']], 'persentase berlebih' => ['discount_value', 101],
            'diskon nol' => ['discount_value', 0], 'diskon negatif' => ['discount_value', -1],
            'diskon pecahan' => ['discount_value', 1.5], 'jenis asing' => ['discount_type', 'other'],
            'tanggal tidak valid' => ['starts_on', '2026-02-30'], 'akhir sebelum mulai' => ['ends_on', '2000-01-01'],
            'kuota nol' => ['max_uses', 0], 'kuota pecahan' => ['max_uses', 1.5],
            'status tidak valid' => ['is_active', 'ya'], 'ubah pemakaian' => ['used_count', 5],
        ];
    }

    public function test_maximum_values_and_same_day_period_are_accepted(): void
    {
        $this->post('/kupon', $this->data(['code' => 'PENUH100', 'discount_value' => 100, 'ends_on' => now()->toDateString()]))->assertRedirect('/kupon/1');
        $this->post('/kupon', $this->data(['code' => 'NOMINAL', 'discount_type' => 'fixed', 'discount_value' => 1000000000, 'max_uses' => 1000000]))->assertRedirect('/kupon/2');
        $this->post('/kupon', $this->data(['code' => 'LEBIH', 'discount_type' => 'fixed', 'discount_value' => 1000000001]))->assertSessionHasErrors('discount_value');
    }

    public function test_database_enforces_unique_codes(): void
    {
        Coupon::factory()->create(['code' => 'UNIK20']);
        $this->expectException(QueryException::class);
        Coupon::factory()->create(['code' => 'UNIK20']);
    }
}
