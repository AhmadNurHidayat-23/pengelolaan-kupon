<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Support\Facades\DB;

class CouponRedeemer
{
    /** @return array{success: bool, message: string, coupon: ?Coupon} */
    public function redeem(string $code): array
    {
        return DB::transaction(function () use ($code): array {
            // The first write acquires SQLite's lock before any reads, including on PHP 8.3.
            DB::statement('UPDATE coupons SET used_count = used_count WHERE code = ?', [$code]);
            $coupon = Coupon::where('code', $code)->first();
            if (! $coupon || $coupon->availability() !== 'valid') {
                return ['success' => false, 'message' => $coupon?->reason() ?? 'Kode kupon tidak ditemukan.', 'coupon' => $coupon];
            }
            $coupon->increment('used_count');

            return ['success' => true, 'message' => 'Simulasi berhasil. Penggunaan kupon '.$coupon->code.' bertambah satu.', 'coupon' => $coupon];
        }, 5);
    }
}
