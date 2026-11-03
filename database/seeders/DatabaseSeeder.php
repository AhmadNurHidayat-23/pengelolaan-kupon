<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (Coupon::exists()) {
            return;
        }
        $today = now();
        foreach ([
            ['code' => 'SELAMAT20', 'discount_type' => 'percentage', 'discount_value' => 20, 'used_count' => 12],
            ['code' => 'HEMAT25000', 'discount_type' => 'fixed', 'discount_value' => 25000, 'used_count' => 8],
            ['code' => 'AKHIRPEKAN', 'discount_type' => 'percentage', 'discount_value' => 15, 'is_active' => false],
            ['code' => 'BULANDEPAN', 'discount_type' => 'percentage', 'discount_value' => 10, 'starts_on' => $today->copy()->addDays(7)->toDateString()],
            ['code' => 'PROMOLALU', 'discount_type' => 'fixed', 'discount_value' => 10000, 'starts_on' => $today->copy()->subDays(20)->toDateString(), 'ends_on' => $today->copy()->subDay()->toDateString()],
            ['code' => 'TERBATAS', 'discount_type' => 'percentage', 'discount_value' => 50, 'used_count' => 10, 'max_uses' => 10],
        ] as $example) {
            Coupon::factory()->create($example);
        }
    }
}
