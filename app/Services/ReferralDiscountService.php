<?php

namespace App\Services;

use App\Models\User;
use App\Models\Ride;
use App\Models\Referral;
use App\Models\ReferralDiscount;
use App\Models\ReferrerDiscount;
use App\Models\AppSetting;

class ReferralDiscountService
{
    /**
     * Apply referral discounts to a ride
     */
    public function applyDiscounts(Ride $ride, float $originalFare): array
    {
        $user = $ride->user;
        $finalFare = $originalFare;
        $appliedDiscounts = [];
        $totalDiscountAmount = 0;

        // 1. Check for user referral discount (user was referred by someone)
        $userReferralDiscount = $this->applyUserReferralDiscount($ride, $user, $originalFare);
        if ($userReferralDiscount) {
            $appliedDiscounts[] = $userReferralDiscount;
            $totalDiscountAmount += $userReferralDiscount['amount'];
        }

        // 2. Check for referrer reward discount (user referred others)
        $referrerRewardDiscount = $this->applyReferrerRewardDiscount($ride, $user, $originalFare);
        if ($referrerRewardDiscount) {
            $appliedDiscounts[] = $referrerRewardDiscount;
            $totalDiscountAmount += $referrerRewardDiscount['amount'];
        }

        $finalFare = max(0, $originalFare - $totalDiscountAmount);

        return [
            'original_fare' => $originalFare,
            'final_fare' => $finalFare,
            'total_discount_amount' => $totalDiscountAmount,
            'applied_discounts' => $appliedDiscounts
        ];
    }

    /**
     * Apply discount for users who were referred (referral discount)
     */
    private function applyUserReferralDiscount(Ride $ride, User $user, float $originalFare): ?array
    {
        // Find active referral where this user was referred
        $referral = Referral::where('referred_user_id', $user->id)
            ->where('is_active', true)
            ->whereColumn('used_rides_count', '<', 'discount_rides_count')
            ->first();

        if (!$referral) {
            return null;
        }

        $discountPercentage = $referral->discount_percentage;
        $discountAmount = ($originalFare * $discountPercentage) / 100;

        // Create discount record
        ReferralDiscount::create([
            'referral_id' => $referral->id,
            'ride_id' => $ride->id,
            'original_amount' => $originalFare,
            'discount_amount' => $discountAmount,
            'final_amount' => $originalFare - $discountAmount,
            'discount_percentage' => $discountPercentage
        ]);

        // Update referral usage
        $referral->useDiscountRide();

        return [
            'type' => 'referral',
            'percentage' => $discountPercentage,
            'amount' => $discountAmount,
            'remaining_rides' => $referral->getRemainingRides()
        ];
    }

    /**
     * Apply reward discount for users who referred others (referrer reward)
     */
    private function applyReferrerRewardDiscount(Ride $ride, User $user, float $originalFare): ?array
    {
        // Find an accepted referral where this user is the inviter and still has
        // reward rides remaining.
        $referral = Referral::where('referrer_id', $user->id)
            ->whereNotNull('referred_user_id') // Must be accepted
            ->where('referrer_rewards_active', true)
            ->get()
            ->first(fn (Referral $r) => $r->isValidForReferrerReward());

        if (!$referral) {
            return null;
        }

        $rewardPercentage = $referral->getEffectiveReferrerPercentage();
        $rewardAmount = ($originalFare * $rewardPercentage) / 100;

        // Create referrer discount record
        ReferrerDiscount::create([
            'referral_id' => $referral->id,
            'ride_id' => $ride->id,
            'referrer_id' => $user->id,
            'original_amount' => $originalFare,
            'discount_amount' => $rewardAmount,
            'final_amount' => $originalFare - $rewardAmount,
            'discount_percentage' => $rewardPercentage
        ]);

        // Track usage on the referral itself so reporting stays consistent.
        $referral->useReferrerRewardRide();

        return [
            'type' => 'referrer_reward',
            'percentage' => $rewardPercentage,
            'amount' => $rewardAmount,
            'remaining_rides' => $referral->getReferrerRemainingRides()
        ];
    }

    /**
     * Get discount preview for ride estimate
     */
    public function getDiscountPreview(User $user, float $estimatedFare): array
    {
        $previewDiscounts = [];
        $totalDiscountAmount = 0;

        // Check user referral discount
        $userReferral = Referral::where('referred_user_id', $user->id)
            ->where('is_active', true)
            ->whereColumn('used_rides_count', '<', 'discount_rides_count')
            ->first();

        if ($userReferral) {
            $discountAmount = ($estimatedFare * $userReferral->discount_percentage) / 100;
            $previewDiscounts[] = [
                'type' => 'referral',
                'percentage' => $userReferral->discount_percentage,
                'amount' => $discountAmount
            ];
            $totalDiscountAmount += $discountAmount;
        }

        // Check referrer reward discount
        $referrerReward = Referral::where('referrer_id', $user->id)
            ->whereNotNull('referred_user_id')
            ->where('referrer_rewards_active', true)
            ->get()
            ->first(fn (Referral $r) => $r->isValidForReferrerReward());

        if ($referrerReward) {
            $rewardPercentage = $referrerReward->getEffectiveReferrerPercentage();
            $rewardAmount = ($estimatedFare * $rewardPercentage) / 100;

            $previewDiscounts[] = [
                'type' => 'referrer_reward',
                'percentage' => $rewardPercentage,
                'amount' => $rewardAmount
            ];
            $totalDiscountAmount += $rewardAmount;
        }

        $finalCost = max(0, $estimatedFare - $totalDiscountAmount);

        return [
            'has_discount' => count($previewDiscounts) > 0,
            'total_discount_amount' => $totalDiscountAmount,
            'final_cost' => $finalCost,
            'applied_discounts' => $previewDiscounts,
            'referral_remaining_rides' => $userReferral ? $userReferral->getRemainingRides() : 0,
            'referrer_remaining_rides' => $referrerReward ? $referrerReward->getReferrerRemainingRides() : 0
        ];
    }
}