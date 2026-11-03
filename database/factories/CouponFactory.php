<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    public function definition(): array
    {
        return ['code' => strtoupper(fake()->unique()->bothify('KUPON-####??')), 'discount_type' => 'percentage',
            'discount_value' => 20, 'starts_on' => now()->toDateString(), 'ends_on' => now()->addDays(30)->toDateString(),
            'max_uses' => 100, 'used_count' => 0, 'is_active' => true];
    }
}
