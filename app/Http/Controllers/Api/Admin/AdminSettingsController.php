<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminSettingsController extends Controller
{
    /**
     * Get admin profit percentage
     */
    public function getProfitPercentage()
    {
        $percentage = AppSetting::getAdminProfitPercentage();
        
        return response()->json([
            'message' => 'Admin profit percentage retrieved successfully.',
            'admin_profit_percentage' => $percentage
        ]);
    }

    /**
     * Set admin profit percentage
     */
    public function setProfitPercentage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'percentage' => 'required|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $percentage = (float) $request->percentage;
        AppSetting::setAdminProfitPercentage($percentage);

        return response()->json([
            'message' => 'Admin profit percentage updated successfully.',
            'admin_profit_percentage' => $percentage
        ]);
    }

    /**
     * Get minimum driver wallet balance
     */
    public function getMinimumDriverWalletBalance()
    {
        $balance = AppSetting::getMinimumDriverWalletBalance();
        
        return response()->json([
            'message' => 'Minimum driver wallet balance retrieved successfully.',
            'minimum_driver_wallet_balance' => $balance
        ]);
    }

    /**
     * Set minimum driver wallet balance
     */
    public function setMinimumDriverWalletBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'balance' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $balance = (float) $request->balance;
        AppSetting::setMinimumDriverWalletBalance($balance);

        return response()->json([
            'message' => 'Minimum driver wallet balance updated successfully.',
            'minimum_driver_wallet_balance' => $balance
        ]);
    }

    /**
     * Get max percentage of fare payable from user wallet
     */
    public function getUserWalletPaymentPercentage()
    {
        $percentage = AppSetting::getUserWalletPaymentPercentage();

        return response()->json([
            'message' => 'User wallet payment percentage retrieved successfully.',
            'user_wallet_payment_percentage' => $percentage
        ]);
    }

    /**
     * Set max percentage of fare payable from user wallet
     */
    public function setUserWalletPaymentPercentage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'percentage' => 'required|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $percentage = (float) $request->percentage;
        AppSetting::setUserWalletPaymentPercentage($percentage);

        return response()->json([
            'message' => 'User wallet payment percentage updated successfully.',
            'user_wallet_payment_percentage' => $percentage
        ]);
    }

    /**
     * Get all admin settings
     */
    public function getAllSettings()
    {
        return response()->json([
            'message' => 'Admin settings retrieved successfully.',
            'settings' => [
                'admin_profit_percentage' => AppSetting::getAdminProfitPercentage(),
                'ride_verification_enabled' => AppSetting::isRideVerificationEnabled(),
                'minimum_driver_wallet_balance' => AppSetting::getMinimumDriverWalletBalance(),
                'user_wallet_payment_percentage' => AppSetting::getUserWalletPaymentPercentage(),
                'signup_gift_enabled' => AppSetting::isSignupGiftEnabled(),
                'signup_gift_amount' => AppSetting::getSignupGiftAmount(),
            ]
        ]);
    }
}