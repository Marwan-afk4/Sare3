<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Ride;


class RideVerificationService
{
    public function __construct()
    {
    }

    /**
     * Check if ride verification is enabled
     */
    public function isVerificationEnabled(): bool
    {
        return AppSetting::isRideVerificationEnabled();
    }

    /**
     * Generate verification code for a ride
     */
    public function generateCodeForRide(Ride $ride): ?string
    {
        if (!$this->isVerificationEnabled()) {
            return null;
        }

        $code = $ride->generateVerificationCode();
        


        return $code;
    }

    /**
     * Verify code for a ride
     */
    public function verifyCodeForRide(Ride $ride, string $code): bool
    {
        if (!$this->isVerificationEnabled()) {
            return true; // If disabled, always pass
        }

        if ($ride->verifyCode($code)) {

            
            return true;
        }

        return false;
    }

    /**
     * Check if ride can be started
     */
    public function canStartRide(Ride $ride): bool
    {
        return $ride->canStart();
    }

    /**
     * Get verification status for a ride
     */
    public function getVerificationStatus(Ride $ride): array
    {
        return [
            'verification_enabled' => $this->isVerificationEnabled(),
            'verification_required' => $ride->requiresVerificationCode(),
            'verification_code_exists' => !empty($ride->verification_code),
            'verification_verified' => $ride->verification_code_verified,
            'can_start_ride' => $this->canStartRide($ride),
            'code_generated_at' => $ride->verification_code_generated_at?->toIso8601String()
        ];
    }

    /**
     * Update Firebase with verification data
     */

}