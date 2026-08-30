<?php

namespace App\Enums;

enum PhoneVerificationMethod: string
{
    case BackendOtp = 'backend_otp';
    case FirebaseOtp = 'firebase_otp';
    case KastanaOtp = 'kastana';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Value returned to mobile apps. Kastana uses the existing backend OTP flow.
     */
    public function appValue(): string
    {
        return match ($this) {
            self::KastanaOtp => self::BackendOtp->value,
            default => $this->value,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::BackendOtp  => __('Backend OTP (WhatsApp)'),
            self::FirebaseOtp => __('Firebase Phone Auth'),
            self::KastanaOtp  => __('Backend OTP (Kastana SMS)'),
        };
    }
}
