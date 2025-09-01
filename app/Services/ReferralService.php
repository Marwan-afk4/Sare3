<?php

namespace App\Services;

use App\Models\User;
use App\Models\Referral;
use App\Models\ReferralDiscount;
use App\Models\ReferrerDiscount;
use App\Models\Ride;
use App\Models\AppSetting;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReferralService
{
    /**
     * Generate referral token for user
     */
    public function generateReferralToken(User $user): array
    {
        // Generate unique token
        do {
            $token = Str::random(8) . $user->id;
        } while (Referral::where('token', $token)->exists());

        // Create referral record
        $referral = Referral::create([
            'referrer_id' => $user->id,
            'token' => $token,
            'is_active' => true,
            'expires_at' => Carbon::now()->addMonths(6) // Token expires in 6 months
        ]);

        // Ensure user has referral code
        $referralCode = $user->getReferralCode();

        return [
            'token' => $token,
            'referral_code' => $referralCode,
            'link' => $this->generateReferralLink($token),
            'expires_at' => $referral->expires_at->toDateTimeString()
        ];
    }

    /**
     * Generate referral link
     */
    private function generateReferralLink(string $token): string
    {
        return url('/api/phone-otp') . '?ref=' . $token;
    }

    /**
     * Apply referral code during user registration
     */
    public function applyReferralCode(string $token, User $newUser): bool
    {
        $referral = Referral::where('token', $token)
            ->whereNull('referred_user_id')
            ->where('is_active', true)
            ->first();

        if (!$referral) {
            return false;
        }

        // Check if token is expired
        if ($referral->expires_at && $referral->expires_at->isPast()) {
            return false;
        }

        // Mark referral as accepted and set discount parameters
        $referral->markAsAccepted($newUser);

        // Update new user's referrer
        $newUser->update(['referrer_id' => $referral->referrer_id]);

        return true;
    }

    /**
     * Calculate discount for ride if user has active referral
     */
    public function calculateRideDiscount(User $user, float $originalAmount): array
    {
        $activeReferral = $user->activeReferral;

        if (!$activeReferral || !$activeReferral->isValidForDiscount()) {
            return [
                'has_discount' => false,
                'original_amount' => $originalAmount,
                'discount_amount' => 0,
                'final_amount' => $originalAmount,
                'discount_percentage' => 0,
                'remaining_rides' => 0
            ];
        }

        $discountPercentage = $activeReferral->discount_percentage;
        $discountAmount = ($originalAmount * $discountPercentage) / 100;
        $finalAmount = $originalAmount - $discountAmount;

        return [
            'has_discount' => true,
            'original_amount' => $originalAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'discount_percentage' => $discountPercentage,
            'remaining_rides' => $activeReferral->getRemainingRides(),
            'referral_id' => $activeReferral->id
        ];
    }

    /**
     * Apply discount to completed ride
     */
    public function applyDiscountToRide(Ride $ride, array $discountData): void
    {
        if (!$discountData['has_discount']) {
            return;
        }

        // Create discount record for referred user
        ReferralDiscount::create([
            'referral_id' => $discountData['referral_id'],
            'ride_id' => $ride->id,
            'original_amount' => $discountData['original_amount'],
            'discount_amount' => $discountData['discount_amount'],
            'final_amount' => $discountData['final_amount'],
            'discount_percentage' => $discountData['discount_percentage']
        ]);

        // Update referral usage
        $referral = Referral::find($discountData['referral_id']);
        $referral->useDiscountRide();

        // Update ride amount
        $ride->update(['total_amount' => $discountData['final_amount']]);
    }

    /**
     * Calculate referrer discount for ride
     */
    public function calculateReferrerDiscount(User $referrer, float $originalAmount): array
    {
        // Find active referral where this user is the referrer
        $activeReferral = Referral::where('referrer_id', $referrer->id)
            ->where('referrer_rewards_active', true)
            ->whereColumn('referrer_used_rides_count', '<', 'referrer_discount_rides_count')
            ->first();

        if (!$activeReferral || !$activeReferral->isValidForReferrerReward()) {
            return [
                'has_discount' => false,
                'original_amount' => $originalAmount,
                'discount_amount' => 0,
                'final_amount' => $originalAmount,
                'discount_percentage' => 0,
                'remaining_rides' => 0
            ];
        }

        $discountPercentage = $activeReferral->referrer_discount_percentage;
        $discountAmount = ($originalAmount * $discountPercentage) / 100;
        $finalAmount = $originalAmount - $discountAmount;

        return [
            'has_discount' => true,
            'original_amount' => $originalAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'discount_percentage' => $discountPercentage,
            'remaining_rides' => $activeReferral->getReferrerRemainingRides(),
            'referral_id' => $activeReferral->id
        ];
    }

    /**
     * Apply referrer discount to completed ride
     */
    public function applyReferrerDiscountToRide(Ride $ride, array $discountData): void
    {
        if (!$discountData['has_discount']) {
            return;
        }

        // Create discount record for referrer
        ReferrerDiscount::create([
            'referral_id' => $discountData['referral_id'],
            'ride_id' => $ride->id,
            'referrer_id' => $ride->user_id, // The user taking the ride is the referrer
            'original_amount' => $discountData['original_amount'],
            'discount_amount' => $discountData['discount_amount'],
            'final_amount' => $discountData['final_amount'],
            'discount_percentage' => $discountData['discount_percentage']
        ]);

        // Update referrer reward usage
        $referral = Referral::find($discountData['referral_id']);
        $referral->useReferrerRewardRide();

        // Update ride amount
        $ride->update(['total_amount' => $discountData['final_amount']]);
    }

    /**
     * Get referral statistics for user
     */
    public function getReferralStats(User $user): array
    {
        $totalReferrals = $user->referralTokens()->whereNotNull('referred_user_id')->count();
        $activeReferrals = $user->referralTokens()
            ->where('is_active', true)
            ->whereColumn('used_rides_count', '<', 'discount_rides_count')
            ->count();

        $totalDiscountsGiven = ReferralDiscount::whereHas('referral', function($query) use ($user) {
            $query->where('referrer_id', $user->id);
        })->sum('discount_amount');

        return [
            'total_referrals' => $totalReferrals,
            'active_referrals' => $activeReferrals,
            'total_discounts_given' => $totalDiscountsGiven,
            'referral_code' => $user->getReferralCode()
        ];
    }

    /**
     * Get user's discount status
     */
    public function getUserDiscountStatus(User $user): array
    {
        $activeReferral = $user->activeReferral;
        
        // Check if user has referrer rewards (as a referrer)
        $referrerRewards = Referral::where('referrer_id', $user->id)
            ->where('referrer_rewards_active', true)
            ->whereColumn('referrer_used_rides_count', '<', 'referrer_discount_rides_count')
            ->first();

        $referralStatus = [
            'has_active_discount' => false,
            'discount_percentage' => 0,
            'remaining_rides' => 0,
            'total_rides' => 0,
            'used_rides' => 0,
            'expires_at' => null
        ];

        $referrerStatus = [
            'has_referrer_rewards' => false,
            'referrer_discount_percentage' => 0,
            'referrer_remaining_rides' => 0,
            'referrer_total_rides' => 0,
            'referrer_used_rides' => 0
        ];

        if ($activeReferral) {
            $referralStatus = [
                'has_active_discount' => $activeReferral->isValidForDiscount(),
                'discount_percentage' => $activeReferral->discount_percentage,
                'remaining_rides' => $activeReferral->getRemainingRides(),
                'total_rides' => $activeReferral->discount_rides_count,
                'used_rides' => $activeReferral->used_rides_count,
                'expires_at' => $activeReferral->expires_at?->toDateTimeString()
            ];
        }

        if ($referrerRewards) {
            $referrerStatus = [
                'has_referrer_rewards' => $referrerRewards->isValidForReferrerReward(),
                'referrer_discount_percentage' => $referrerRewards->referrer_discount_percentage,
                'referrer_remaining_rides' => $referrerRewards->getReferrerRemainingRides(),
                'referrer_total_rides' => $referrerRewards->referrer_discount_rides_count,
                'referrer_used_rides' => $referrerRewards->referrer_used_rides_count
            ];
        }

        return array_merge($referralStatus, $referrerStatus);
    }
}