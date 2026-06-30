<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Concerns\HandlesFirebasePhoneLogin;
use App\Http\Controllers\Controller;
use App\Services\FirebasePhoneAuthService;
use App\Services\PhoneVerificationService;
use Illuminate\Http\Request;

class FirebasePhoneAuthController extends Controller
{
    use HandlesFirebasePhoneLogin;

    public function login(
        Request $request,
        FirebasePhoneAuthService $firebasePhoneAuth,
        PhoneVerificationService $phoneVerification
    ) {
        $verifiedPhone = $this->verifiedPhoneFromFirebaseRequest(
            $request,
            $firebasePhoneAuth,
            $phoneVerification
        );

        if ($verifiedPhone instanceof \Illuminate\Http\JsonResponse) {
            return $verifiedPhone;
        }

        $user = $phoneVerification->findOrCreateDriverByPhone($verifiedPhone);

        return $phoneVerification->finalizeDriverPhoneVerification($user);
    }
}
