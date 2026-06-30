<?php

namespace App\Enums;

enum PhoneVerificationMethod: string
{
    case BackendOtp = 'backend_otp';
    case FirebaseOtp = 'firebase_otp';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::BackendOtp  => __('Backend OTP (WhatsApp)'),
            self::FirebaseOtp => __('Firebase Phone Auth'),
        };
    }
}
