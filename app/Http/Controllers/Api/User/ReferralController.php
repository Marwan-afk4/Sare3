<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class ReferralController extends Controller
{


    public function generateLink(Request $request)
    {
        $user = $request->user();

        // توليد كود referral عشوائي
        $token = Str::random(10);

        // حفظ في جدول referrals
        $referral = Referral::create([
            'referrer_id' => $user->id,
            'token'       => $token,
        ]);


        return response()->json([
            'message' => 'Referral link generated successfully',
            'link' => url('/api/phone-otp') . '?ref=' . $token, // هنا التعديل
            'token'   => $token,
        ]);
    }

}
