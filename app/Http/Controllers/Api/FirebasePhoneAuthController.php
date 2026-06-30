<?php

namespace App\Http\Controllers\Api;

use App\Enums\PhoneVerificationMethod;
use App\Exceptions\FirebasePhoneAuthException;
use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\FirebasePhoneAuthService;
use App\Services\PhoneVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FirebasePhoneAuthController extends Controller
{
    public function login(
        Request $request,
        FirebasePhoneAuthService $firebasePhoneAuth,
        PhoneVerificationService $phoneVerification
    ) {
        $this->normalizePhoneRequest($request);

        $validation = Validator::make($request->all(), [
            'firebase_id_token' => 'required|string',
            'phone'             => ['required', 'string', 'regex:/^\+?[1-9][0-9]{6,14}$/'],
        ], [
            'phone.regex' => 'Phone must be in international format (e.g. +9627XXXXXXXX or 9627XXXXXXXX)',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first(),
            ], 422);
        }

        if (AppSetting::getPhoneVerificationMethod() !== PhoneVerificationMethod::FirebaseOtp->value) {
            return response()->json([
                'message' => 'Firebase phone authentication is not the active verification method.',
            ], 403);
        }

        if (! $firebasePhoneAuth->isConfigured()) {
            return response()->json([
                'message' => 'Firebase phone authentication is not configured.',
            ], 503);
        }

        try {
            $verifiedPhone = $firebasePhoneAuth->verifyIdTokenAndGetPhone($request->firebase_id_token);
        } catch (FirebasePhoneAuthException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        }

        $submittedPhone = $phoneVerification->normalizePhone($request->phone);
        $tokenPhone = $phoneVerification->normalizePhone($verifiedPhone);

        if ($submittedPhone !== $tokenPhone) {
            return response()->json([
                'message' => 'Phone number does not match Firebase verification.',
            ], 422);
        }

        $user = $phoneVerification->findOrCreateUserByPhone($tokenPhone);

        return $phoneVerification->finalizePhoneVerification($user);
    }
}
