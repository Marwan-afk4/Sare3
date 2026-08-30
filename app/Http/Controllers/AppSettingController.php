<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    public function index()
    {
        // Ensure admin profit percentage setting exists
        $this->ensureDefaultSettings();

        $settings = AppSetting::orderBy('key')->get()->map(function ($setting) {
            // Cast the value properly for display
            $setting->cast_value = AppSetting::get($setting->key);
            return $setting;
        });
        return view('settings.index', compact('settings'));
    }

    /**
     * Ensure default settings exist
     */
    private function ensureDefaultSettings()
    {
        // Ensure admin profit percentage setting exists
        if (!AppSetting::where('key', 'admin_profit_percentage')->exists()) {
            AppSetting::set('admin_profit_percentage', 10, 'string', 'Admin profit percentage from rides (0-100%)');
        }

        // Ensure wallet settings exist
        if (!AppSetting::where('key', 'minimum_driver_wallet_balance')->exists()) {
            AppSetting::set('minimum_driver_wallet_balance', 0, 'string', 'Minimum wallet balance required for drivers to go online');
        }

        if (!AppSetting::where('key', 'user_wallet_payment_percentage')->exists()) {
            AppSetting::set(
                'user_wallet_payment_percentage',
                100,
                'string',
                'Maximum percentage of the ride fare that can be paid from the user wallet (0-100%)'
            );
        }

        // Ensure ride verification setting exists
        if (!AppSetting::where('key', 'ride_verification_enabled')->exists()) {
            AppSetting::set('ride_verification_enabled', false, 'boolean', 'Enable 6-digit verification code for starting rides');
        }

        // Ensure phone verification method setting exists
        if (!AppSetting::where('key', 'phone_verification_method')->exists()) {
            AppSetting::setPhoneVerificationMethod('backend_otp');
        }

        if (!AppSetting::where('key', 'signup_gift_enabled')->exists()) {
            AppSetting::setSignupGiftEnabled(false);
        }

        if (!AppSetting::where('key', 'signup_gift_amount')->exists()) {
            AppSetting::setSignupGiftAmount(0);
        }

        // Ensure referral settings exist
        $this->ensureReferralSettings();
    }

    /**
     * Ensure referral settings exist
     */
    private function ensureReferralSettings()
    {
        $referralSettings = [
            'referral_discount_percentage' => [
                'value' => '10',
                'type' => 'string',
                'description' => 'Discount percentage for new users who use referral codes (0-100%)'
            ],
            'referral_discount_rides' => [
                'value' => '5',
                'type' => 'integer',
                'description' => 'Number of rides with discount for referred users (1-50)'
            ],
            'referrer_reward_percentage' => [
                'value' => '5',
                'type' => 'string',
                'description' => 'Reward percentage for users who refer others (0-100%)'
            ],
            'referrer_reward_rides' => [
                'value' => '10',
                'type' => 'integer',
                'description' => 'Number of rides with rewards for referrers (1-100)'
            ]
        ];

        foreach ($referralSettings as $key => $config) {
            if (!AppSetting::where('key', $key)->exists()) {
                AppSetting::set($key, $config['value'], $config['type'], $config['description']);
            }
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'array',
            'settings.admin_profit_percentage' => 'nullable|numeric|min:0|max:100',
            'settings.user_wallet_payment_percentage' => 'nullable|numeric|min:0|max:100',
            'settings.minimum_driver_wallet_balance' => 'nullable|numeric|min:0',
            'settings.ride_verification_enabled' => 'nullable|in:0,1,on',
            'settings.phone_verification_method' => 'nullable|in:backend_otp,firebase_otp',
            'settings.referral_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'settings.referral_discount_rides' => 'nullable|integer|min:1|max:50',
            'settings.referrer_reward_percentage' => 'nullable|numeric|min:0|max:100',
            'settings.referrer_reward_rides' => 'nullable|integer|min:1|max:100',
            'settings.signup_gift_enabled' => 'nullable|in:0,1,on',
            'settings.signup_gift_amount' => 'nullable|numeric|min:0|max:999999',
        ]);

        foreach ($request->settings as $key => $value) {
            $setting = AppSetting::where('key', $key)->first();

            if ($setting) {
                $processedValue = $this->processValue($value, $setting->type);
                AppSetting::set($key, $processedValue, $setting->type, $setting->description);
            }
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }

    private function processValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return $value === '1' || $value === 'on' || $value === true;
            case 'integer':
                return (int) $value;
            case 'json':
                return is_array($value) ? $value : json_decode($value, true);
            default:
                return $value;
        }
    }
}
