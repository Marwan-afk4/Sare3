<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReferralController extends Controller
{
    protected ReferralService $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    /**
     * Generate referral link for driver
     */
    public function generateLink(Request $request): JsonResponse
    {
        $driver = $request->user();
        
        $referralData = $this->referralService->generateReferralToken($driver);

        return response()->json([
            'success' => true,
            'message' => 'Referral link generated successfully',
            'data' => $referralData
        ]);
    }

    /**
     * Get driver's referral statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $driver = $request->user();
        
        $stats = $this->referralService->getReferralStats($driver);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get driver's discount status
     */
    public function getDiscountStatus(Request $request): JsonResponse
    {
        $driver = $request->user();
        
        $discountStatus = $this->referralService->getUserDiscountStatus($driver);

        return response()->json([
            'success' => true,
            'data' => $discountStatus
        ]);
    }

    /**
     * Apply referral code (used after driver signs in)
     */
    public function applyCode(Request $request): JsonResponse
    {
        $request->validate([
            'referral_code' => 'required|string'
        ]);

        $driver = $request->user();

        // Check if driver already has a referrer
        if ($driver->referrer_id) {
            return response()->json([
                'success' => false,
                'message' => 'You have already used a referral code'
            ], 400);
        }

        // Find referral by code or token
        $referral = \App\Models\Referral::where('token', $request->referral_code)
            ->orWhereHas('referrer', function($query) use ($request) {
                $query->where('referral_code', $request->referral_code);
            })
            ->whereNull('referred_user_id')
            ->where('is_active', true)
            ->first();

        if (!$referral) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired referral code'
            ], 400);
        }

        // Check if driver is trying to refer themselves
        if ($referral->referrer_id === $driver->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot use your own referral code'
            ], 400);
        }

        // Apply the referral
        $success = $this->referralService->applyReferralCode($referral->token, $driver);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Referral code applied successfully! You will get discount on your next rides.',
                'data' => [
                    'discount_percentage' => \App\Models\AppSetting::getReferralDiscountPercentage(),
                    'discount_rides' => \App\Models\AppSetting::getReferralDiscountRides()
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to apply referral code'
        ], 400);
    }

    /**
     * Validate referral code before applying
     */
    public function validateCode(Request $request): JsonResponse
    {
        $request->validate([
            'referral_code' => 'required|string'
        ]);

        $driver = $request->user();

        // Check if driver already has a referrer
        if ($driver->referrer_id) {
            return response()->json([
                'success' => false,
                'message' => 'You have already used a referral code',
                'can_apply' => false
            ]);
        }

        // Find referral by code or token
        $referral = \App\Models\Referral::where('token', $request->referral_code)
            ->orWhereHas('referrer', function($query) use ($request) {
                $query->where('referral_code', $request->referral_code);
            })
            ->whereNull('referred_user_id')
            ->where('is_active', true)
            ->first();

        if (!$referral) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired referral code',
                'can_apply' => false
            ]);
        }

        // Check if driver is trying to refer themselves
        if ($referral->referrer_id === $driver->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot use your own referral code',
                'can_apply' => false
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Referral code is valid',
            'can_apply' => true,
            'data' => [
                'referrer_name' => $referral->referrer->name ?? 'Unknown',
                'discount_percentage' => \App\Models\AppSetting::getReferralDiscountPercentage(),
                'discount_rides' => \App\Models\AppSetting::getReferralDiscountRides()
            ]
        ]);
    }
}