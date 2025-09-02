<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME20',
                'name' => 'Welcome 20% Off',
                'description' => 'Get 20% off on your first ride',
                'type' => 'percentage',
                'value' => 20,
                'minimum_ride_amount' => 10,
                'maximum_discount' => 50,
                'usage_limit' => 100,
                'user_usage_limit' => 1,
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addDays(30),
            ],
            [
                'code' => 'SAVE5',
                'name' => '$5 Off Any Ride',
                'description' => 'Save $5 on any ride',
                'type' => 'fixed',
                'value' => 5,
                'minimum_ride_amount' => 15,
                'maximum_discount' => null,
                'usage_limit' => 50,
                'user_usage_limit' => 2,
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addDays(15),
            ],
            [
                'code' => 'WEEKEND30',
                'name' => 'Weekend Special 30%',
                'description' => 'Special weekend discount',
                'type' => 'percentage',
                'value' => 30,
                'minimum_ride_amount' => 20,
                'maximum_discount' => 100,
                'usage_limit' => null, // unlimited
                'user_usage_limit' => 3,
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addDays(60),
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::create($coupon);
        }
    }
}