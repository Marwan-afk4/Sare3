<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingsController extends Controller
{
    /**
     * Get app settings for mobile apps
     */
    public function getSettings()
    {
        return response()->json([
            'message' => 'Settings retrieved successfully.',
            'settings' => [
                'ride_verification_enabled' => AppSetting::isRideVerificationEnabled(),
                'signup_gift_enabled' => AppSetting::isSignupGiftEnabled(),
                'signup_gift_amount' => AppSetting::getSignupGiftAmount(),
            ]
        ]);
    }

    /**
     * Check if ride verification is enabled
     */
    public function isRideVerificationEnabled()
    {
        return response()->json([
            'message' => 'Ride verification status retrieved.',
            'ride_verification_enabled' => AppSetting::isRideVerificationEnabled()
        ]);
    }
}