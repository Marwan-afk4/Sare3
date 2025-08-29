<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Ride;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class RideVerificationService
{
    protected Database $firebase;

    public function __construct()
    {
        $this->firebase = (new Factory)
            ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
            ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
            ->createDatabase();
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
        
        // Push to Firebase
        $this->updateFirebaseVerification($ride, [
            'verification_code' => $code,
            'verification_required' => true,
            'verification_verified' => false,
            'code_generated_at' => now()->toIso8601String()
        ]);

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
            // Update Firebase
            $this->updateFirebaseVerification($ride, [
                'verification_verified' => true,
                'verified_at' => now()->toIso8601String()
            ]);
            
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
    protected function updateFirebaseVerification(Ride $ride, array $data): void
    {
        try {
            $firebaseRideId = 'ride_' . $ride->id;
            
            // Store verification info for user access
            $this->firebase->getReference("rides/$firebaseRideId/user_verification")->update($data);
            
            // Store minimal verification status for driver (without code)
            $driverData = $data;
            if (isset($driverData['verification_code'])) {
                unset($driverData['verification_code']); // Remove code from driver's view
            }
            $this->firebase->getReference("rides/$firebaseRideId/verification")->update($driverData);
            
        } catch (\Exception $e) {
            \Log::error('Firebase verification update failed: ' . $e->getMessage());
        }
    }
}