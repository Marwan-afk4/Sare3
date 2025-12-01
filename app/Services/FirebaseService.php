<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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

    /**
     * Get driver location from Firebase Realtime Database
     * 
     * @param int $driverId
     * @return array|null Returns array with latitude, longitude, bearing, and timestamp or null if not found
     */
    public function getDriverLocation($driverId)
    {
        if (!$this->database) {
            return null;
        }

        try {
            $driverData = $this->database->getReference("drivers/{$driverId}")->getValue();
            
            if (!$driverData || !isset($driverData['latitude']) || !isset($driverData['longitude'])) {
                return null;
            }

            return [
                'latitude' => (float) $driverData['latitude'],
                'longitude' => (float) $driverData['longitude'],
                'bearing' => isset($driverData['bearing']) ? (float) $driverData['bearing'] : 0,
                'timestamp' => $driverData['timestamp'] ?? null,
                'is_available' => true,
                'driver_id' => $driverId,
            ];
        } catch (\Exception $e) {
            Log::error("Failed to get driver location for driver {$driverId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if driver is available (exists in Firebase Realtime DB)
     * 
     * @param int $driverId
     * @return bool
     */
    public function isDriverAvailable($driverId)
    {
        if (!$this->database) {
            return false;
        }

        try {
            $driverData = $this->database->getReference("drivers/{$driverId}")->getValue();
            return !empty($driverData);
        } catch (\Exception $e) {
            Log::error("Failed to check driver availability for driver {$driverId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get multiple drivers' locations from Firebase
     * 
     * @param array $driverIds Array of driver IDs
     * @return array Array of driver locations keyed by driver ID
     */
    public function getMultipleDriverLocations(array $driverIds)
    {
        if (!$this->database || empty($driverIds)) {
            return [];
        }

        $locations = [];
        
        try {
            foreach ($driverIds as $driverId) {
                $location = $this->getDriverLocation($driverId);
                if ($location) {
                    $locations[$driverId] = $location;
                }
            }
            
            return $locations;
        } catch (\Exception $e) {
            Log::error("Failed to get multiple driver locations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all available drivers from Firebase with caching
     * Cache for 10 seconds to prevent excessive Firebase calls
     * 
     * @param bool $forceRefresh Force refresh cache
     * @return array Array of all available drivers with their locations
     */
    public function getAllAvailableDrivers($forceRefresh = false)
    {
        if (!$this->database) {
            return [];
        }

        $cacheKey = 'firebase_available_drivers';
        
        // Return cached data if available and not forcing refresh
        if (!$forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $allDrivers = $this->database->getReference('drivers')->getValue();
            
            if (!$allDrivers) {
                Cache::put($cacheKey, [], 10); // Cache empty result for 10 seconds
                return [];
            }

            $availableDrivers = [];
            foreach ($allDrivers as $driverId => $driverData) {
                if (isset($driverData['latitude']) && isset($driverData['longitude'])) {
                    $availableDrivers[$driverId] = [
                        'driver_id' => $driverId,
                        'latitude' => (float) $driverData['latitude'],
                        'longitude' => (float) $driverData['longitude'],
                        'bearing' => isset($driverData['bearing']) ? (float) $driverData['bearing'] : 0,
                        'timestamp' => $driverData['timestamp'] ?? null,
                        'is_available' => true,
                    ];
                }
            }
            
            // Cache the result for 10 seconds
            Cache::put($cacheKey, $availableDrivers, 10);
            
            return $availableDrivers;
        } catch (\Exception $e) {
            Log::error("Failed to get all available drivers: " . $e->getMessage());
            // Cache empty result to prevent hammering Firebase on repeated errors
            Cache::put($cacheKey, [], 5);
            return [];
        }
    }
}