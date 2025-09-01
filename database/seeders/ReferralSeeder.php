<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Referral;
use App\Models\AppSetting;

class ReferralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure referral settings exist
        AppSetting::set('referral_discount_percentage', 10, 'string', 'Discount percentage for referral users');
        AppSetting::set('referral_discount_rides', 5, 'integer', 'Number of rides with referral discount');
        AppSetting::set('referrer_reward_percentage', 5, 'string', 'Reward percentage for referrers');
        AppSetting::set('referrer_reward_rides', 10, 'integer', 'Number of rides with referrer rewards');

        // Get some users to create referrals
        $users = User::limit(10)->get();
        
        if ($users->count() < 2) {
            // Create some sample users if they don't exist
            $referrer = User::create([
                'name' => 'John Doe',
                'phone' => '+1234567890',
                'email' => 'john@example.com',
                'role' => 'user',
                'password' => bcrypt('password'),
                'phone_verified_at' => now(),
            ]);

            $referredUser = User::create([
                'name' => 'Jane Smith',
                'phone' => '+1234567891',
                'email' => 'jane@example.com',
                'role' => 'user',
                'password' => bcrypt('password'),
                'phone_verified_at' => now(),
            ]);

            $driver = User::create([
                'name' => 'Mike Driver',
                'phone' => '+1234567892',
                'email' => 'mike@example.com',
                'role' => 'driver',
                'password' => bcrypt('password'),
                'phone_verified_at' => now(),
            ]);

            $users = collect([$referrer, $referredUser, $driver]);
        }

        // Create some sample referrals
        $referrals = [
            [
                'referrer_id' => $users->first()->id,
                'referred_user_id' => $users->skip(1)->first()->id,
                'token' => 'abc123def456',
                'accepted_at' => now()->subDays(5),
                'discount_percentage' => 10,
                'discount_rides_count' => 5,
                'used_rides_count' => 2,
                'is_active' => true,
                'expires_at' => now()->addMonths(6),
            ],
            [
                'referrer_id' => $users->skip(1)->first()->id,
                'referred_user_id' => $users->skip(2)->first()->id ?? $users->first()->id,
                'token' => 'xyz789abc123',
                'accepted_at' => now()->subDays(10),
                'discount_percentage' => 10,
                'discount_rides_count' => 5,
                'used_rides_count' => 5,
                'is_active' => false,
                'expires_at' => now()->addMonths(6),
            ],
            [
                'referrer_id' => $users->first()->id,
                'referred_user_id' => null, // Not accepted yet
                'token' => 'pending123',
                'accepted_at' => null,
                'discount_percentage' => 10,
                'discount_rides_count' => 5,
                'used_rides_count' => 0,
                'is_active' => true,
                'expires_at' => now()->addMonths(6),
            ],
        ];

        foreach ($referrals as $referralData) {
            Referral::create($referralData);
        }

        $this->command->info('Referral seeder completed successfully!');
    }
}