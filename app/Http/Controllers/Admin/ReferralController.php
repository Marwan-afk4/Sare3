<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Referral;
use App\Models\ReferralDiscount;
use App\Models\ReferrerDiscount;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralController extends Controller
{
    /**
     * Display referral management dashboard
     */
    public function index(): View
    {
        $totalReferrals = Referral::whereNotNull('referred_user_id')->count();
        $activeReferrals = Referral::where('is_active', true)
            ->whereColumn('used_rides_count', '<', 'discount_rides_count')
            ->count();
        
        $totalReferralDiscounts = ReferralDiscount::sum('discount_amount') ?? 0;
        $totalReferrerRewards = ReferrerDiscount::sum('discount_amount') ?? 0;
        $totalDiscounts = $totalReferralDiscounts + $totalReferrerRewards;
        $totalRidesWithDiscount = ReferralDiscount::count() + ReferrerDiscount::count();

        $topReferrers = Referral::selectRaw('referrer_id, COUNT(*) as referral_count')
            ->whereNotNull('referred_user_id')
            ->groupBy('referrer_id')
            ->orderByDesc('referral_count')
            ->limit(10)
            ->with('referrer:id,name,phone,role')
            ->get();

        $recentReferrals = Referral::with(['referrer:id,name,phone,role', 'referredUser:id,name,phone,role'])
            ->whereNotNull('referred_user_id')
            ->orderByDesc('accepted_at')
            ->limit(10)
            ->get();

        return view('admin.referrals.index', compact(
            'totalReferrals',
            'activeReferrals', 
            'totalDiscounts',
            'totalReferralDiscounts',
            'totalReferrerRewards',
            'totalRidesWithDiscount',
            'topReferrers',
            'recentReferrals'
        ));
    }

    /**
     * Display detailed referral list
     */
    public function list(Request $request): View
    {
        $query = Referral::with(['referrer:id,name,phone,role', 'referredUser:id,name,phone,role'])
            ->whereNotNull('referred_user_id');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
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
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('accepted_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('accepted_at', '<=', $request->to_date);
        }

        $referrals = $query->orderByDesc('accepted_at')
            ->paginate($request->get('per_page', 15));

        return view('admin.referrals.list', compact('referrals'));
    }

    /**
     * Display referral settings
     */
    public function settings(): View
    {
        $settings = [
            'referred_user_discount_percentage' => AppSetting::getReferralDiscountPercentage(),
            'referred_user_discount_rides' => AppSetting::getReferralDiscountRides(),
            'referrer_reward_percentage' => AppSetting::getReferrerRewardPercentage(),
            'referrer_reward_rides' => AppSetting::getReferrerRewardRides()
        ];

        return view('admin.referrals.settings', compact('settings'));
    }

    /**
     * Update referral settings
     */
    public function updateSettings(Request $request)
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

        return redirect()->route('referrals.settings')
            ->with('success', 'Referral settings updated successfully');
    }
}