<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $database;

    public function __construct()
    {
        try {
            $firebase = (new Factory)
                ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                ->createDatabase();
            
            $this->database = $firebase;
        } catch (\Exception $e) {
            Log::error('Firebase initialization failed: ' . $e->getMessage());
            $this->database = null;
        }
    }

    /**
     * Update driver location in Firebase
     */
    public function updateDriverLocation($rideId, $lat, $lng, $firebaseRideId = null)
    {
        if (!$this->database) {
            return false;
        }

        try {
            $firebaseRideId = $firebaseRideId ?: 'ride_' . $rideId;
            
            $this->database->getReference("rides/{$firebaseRideId}/driver_location")->set([
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'timestamp' => now()->timestamp,
                'updated_at' => now()->toIso8601String(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Firebase driver location update failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update ride status in Firebase
     */
    public function updateRideStatus($rideId, $status, $firebaseRideId = null)
    {
        if (!$this->database) {
            return false;
        }

        try {
            $firebaseRideId = $firebaseRideId ?: 'ride_' . $rideId;
            
            $this->database->getReference("rides/{$firebaseRideId}/status")->set($status);
            $this->database->getReference("rides/{$firebaseRideId}/status_updated_at")->set(now()->toIso8601String());

            // If ride is completed, also set completion timestamp
            if (in_array($status, ['completed', 'finished', 'finshed'])) {
                $this->database->getReference("rides/{$firebaseRideId}/completed_at")->set(now()->toIso8601String());
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Firebase ride status update failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove ride data from Firebase (cleanup after completion)
     */
    public function cleanupRideData($rideId, $firebaseRideId = null)
    {
        if (!$this->database) {
            return false;
        }

        try {
            $firebaseRideId = $firebaseRideId ?: 'ride_' . $rideId;
            
            // Remove driver location tracking but keep final status
            $this->database->getReference("rides/{$firebaseRideId}/driver_location")->remove();
            
            return true;
        } catch (\Exception $e) {
            Log::error('Firebase ride cleanup failed: ' . $e->getMessage());
            return false;
        }
    }
}