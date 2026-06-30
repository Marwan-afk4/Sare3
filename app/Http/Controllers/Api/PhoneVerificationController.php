<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;

class PhoneVerificationController extends Controller
{
    public function otpMethod()
    {
        return response()->json([
            'method' => AppSetting::getPhoneVerificationMethod(),
        ]);
    }
}
