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
     * Get all admin settings
     */
    public function getAllSettings()
    {
        return response()->json([
            'message' => 'Admin settings retrieved successfully.',
            'settings' => [
                'admin_profit_percentage' => AppSetting::getAdminProfitPercentage(),
                'ride_verification_enabled' => AppSetting::isRideVerificationEnabled()
            ]
        ]);
    }
}