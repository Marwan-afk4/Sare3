<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AppSetting;

class ReferralSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default referral discount percentage (10%)
        AppSetting::updateOrCreate(
            ['key' => 'referral_discount_percentage'],
            [
                'value' => '10',
                'type' => 'string',
                'description' => 'Discount percentage for referral users'
            ]
        );

        // Default number of rides with discount (5 rides)
        AppSetting::updateOrCreate(
            ['key' => 'referral_discount_rides'],
            [
                'value' => '5',
                'type' => 'integer',
                'description' => 'Number of rides with referral discount'
            ]
        );

        // Default referrer reward percentage (5%)
        AppSetting::updateOrCreate(
            ['key' => 'referrer_reward_percentage'],
            [
                'value' => '5',
                'type' => 'string',
                'description' => 'Reward percentage for referrers'
            ]
        );

        // Default number of rides with referrer rewards (10 rides)
        AppSetting::updateOrCreate(
            ['key' => 'referrer_reward_rides'],
            [
                'value' => '10',
                'type' => 'integer',
                'description' => 'Number of rides with referrer rewards'
            ]
        );
    }
}