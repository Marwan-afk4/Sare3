<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Referral;
use App\Models\ReferralDiscount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReferralController extends Controller
{
    /**
     * Get referral settings
     */
    public function getSettings(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'referred_user_discount_percentage' => AppSetting::getReferralDiscountPercentage(),
                'referred_user_discount_rides' => AppSetting::getReferralDiscountRides(),
                'referrer_reward_percentage' => AppSetting::getReferrerRewardPercentage(),
                'referrer_reward_rides' => AppSetting::getReferrerRewardRides()
            ]
        ]);
    }

    /**
     * Update referral settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $request->validate([
            'referred_user_discount_percentage' => 'required|numeric|min:0|max:100',
            'referred_user_discount_rides' => 'required|integer|min:1|max:50',
            'referrer_reward_percentage' => 'required|numeric|min:0|max:100',
            'referrer_reward_rides' => 'required|integer|min:1|max:100'
        ]);

        AppSetting::setReferralDiscountPercentage($request->referred_user_discount_percentage);
        AppSetting::setReferralDiscountRides($request->referred_user_discount_rides);
        AppSetting::setReferrerRewardPercentage($request->referrer_reward_percentage);
        AppSetting::setReferrerRewardRides($request->referrer_reward_rides);

        return response()->json([
            'success' => true,
            'message' => 'Referral settings updated successfully',
            'data' => [
                'referred_user_discount_percentage' => $request->referred_user_discount_percentage,
                'referred_user_discount_rides' => $request->referred_user_discount_rides,
                'referrer_reward_percentage' => $request->referrer_reward_percentage,
                'referrer_reward_rides' => $request->referrer_reward_rides
            ]
        ]);
    }

    /**
     * Get referral statistics
     */
    public function getStatistics(): JsonResponse
    {
        $totalReferrals = Referral::whereNotNull('referred_user_id')->count();
        $activeReferrals = Referral::where('is_active', true)
            ->whereColumn('used_rides_count', '<', 'discount_rides_count')
            ->count();
        
        $totalReferralDiscounts = ReferralDiscount::sum('discount_amount');
        $totalReferrerRewards = ReferrerDiscount::sum('discount_amount');
        $totalDiscounts = $totalReferralDiscounts + $totalReferrerRewards;
        $totalRidesWithDiscount = ReferralDiscount::count() + ReferrerDiscount::count();

        $topReferrers = Referral::selectRaw('referrer_id, COUNT(*) as referral_count')
            ->whereNotNull('referred_user_id')
            ->groupBy('referrer_id')
            ->orderByDesc('referral_count')
            ->limit(10)
            ->with('referrer:id,name,phone,role')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_referrals' => $totalReferrals,
                'active_referrals' => $activeReferrals,
                'total_discount_amount' => $totalDiscounts,
                'total_referral_discounts' => $totalReferralDiscounts,
                'total_referrer_rewards' => $totalReferrerRewards,
                'total_rides_with_discount' => $totalRidesWithDiscount,
                'top_referrers' => $topReferrers
            ]
        ]);
    }

    /**
     * Get detailed referral list
     */
    public function getReferralList(Request $request): JsonResponse
    {
        $query = Referral::with(['referrer:id,name,phone,role', 'referredUser:id,name,phone,role'])
            ->whereNotNull('referred_user_id');

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)
                      ->whereColumn('used_rides_count', '<', 'discount_rides_count');
            } elseif ($request->status === 'completed') {
                $query->where(function($q) {
                    $q->where('is_active', false)
                      ->orWhereColumn('used_rides_count', '>=', 'discount_rides_count');
                });
            }
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('accepted_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('accepted_at', '<=', $request->to_date);
        }

        $referrals = $query->orderByDesc('accepted_at')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $referrals
        ]);
    }
}