<?php

namespace App\Services;

use App\Models\OtpLimit;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PhoneVerificationService
{
    public function normalizePhone(string $phone): string
    {
        if (! str_starts_with($phone, '+')) {
            return '+'.ltrim($phone, '+');
        }

        return $phone;
    }

    public function findUserByPhone(string $phone): ?User
    {
        $normalized = $this->normalizePhone($phone);
        $rawPhone = ltrim($normalized, '+');

        return User::where('phone', $normalized)
            ->orWhere('phone', $rawPhone)
            ->first();
    }

    public function findOrCreateUserByPhone(string $phone): User
    {
        $phone = $this->normalizePhone($phone);
        $rawPhone = ltrim($phone, '+');
        $defaultOtpLimit = OtpLimit::where('type', 'user')->value('otp_limit');

        $user = User::where('phone', $phone)
            ->orWhere('phone', $rawPhone)
            ->first();

        if ($user) {
            if ($user->phone !== $phone) {
                $user->update(['phone' => $phone]);
            }

            return $user;
        }

        return User::create([
            'phone'          => $phone,
            'role'           => 'user',
            'otp_limit'      => $defaultOtpLimit ?? 5,
            'status'         => 'pending',
            'phone_verified' => false,
        ]);
    }

    public function finalizePhoneVerification(User $user): JsonResponse
    {
        $user->update([
            'phone_verified' => true,
            'otp_code'       => null,
            'otp_expires_at' => null,
            'status'         => 'approved',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'        => 'OTP verified successfully',
            'token'          => $token,
            'user_otp_limit' => $user->otp_limit,
        ]);
    }
}
