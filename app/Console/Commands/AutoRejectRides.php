<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ride;
use App\Models\User;
use App\Helpers\FcmHelper;
use App\Http\Controllers\Api\User\RideEstimateController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Exception;

class AutoRejectRides extends Command
{
    protected $signature = 'rides:auto-reject';
    protected $description = 'Automatically reject rides if the driver does not respond within 30 seconds';

    public function handle()
    {
        Log::info('🚀 Auto reject command started at ' . now());
        $expiredTime = Carbon::now()->subSeconds(30);

        $rides = Ride::where('status', 'pending')
            ->whereNotNull('driver_assigned_at')
            ->where('driver_assigned_at', '<=', $expiredTime)
            ->get();

        if ($rides->isEmpty()) {
            Log::info('No pending rides to auto reject.');
            return;
        }

        $rideEstimateController = new RideEstimateController();

        foreach ($rides as $ride) {
            try {
                Log::info("Processing ride ID {$ride->id}...");

                // // Step 1: Reject the current driver
                // $ride->update(['status' => 'rejected', 'driver_id' => null]);
                // Log::info("Auto-rejected ride ID {$ride->id} (driver ID: {$ride->driver_id})");

                // Step 1: Mark the current driver as rejected
                $previousDriverId = $ride->driver_id; // store before nulling
                $excludedDriverIds = $ride->rejected_drivers ?? [];

                if ($previousDriverId && !in_array($previousDriverId, $excludedDriverIds)) {
                    $excludedDriverIds[] = $previousDriverId;
                }

                $ride->update([
                    'status' => 'pending',
                    'driver_id' => null,
                    'rejected_drivers' => $excludedDriverIds,
                ]);
                Log::info("Auto-rejected driver {$previousDriverId} for ride {$ride->id}, ride reset to pending.");


                // Step 2: Update Firebase
                $this->updateFirebaseRideStatus($ride, 'pending');

                // Step 3: Add current driver to rejected list
                $excludedDriverIds = $ride->rejected_drivers ?? [];
                if ($ride->driver_id && !in_array($ride->driver_id, $excludedDriverIds)) {
                    $excludedDriverIds[] = $ride->driver_id;
                    $ride->update(['rejected_drivers' => $excludedDriverIds]);
                }

                // Step 4: Get eligible drivers
                $allDrivers = $rideEstimateController->getEligibleDrivers(
                    $ride->pickup_lat,
                    $ride->pickup_lng,
                    []
                );

                if (empty($allDrivers)) {
                    Log::info("No drivers available for ride {$ride->id}");
                    $ride->update(['driver_id' => null, 'status' => 'pending']);
                    $this->updateFirebaseRideStatus($ride, 'pending');
                    continue;
                }

                // 🧠 Log ALL available drivers with their ETA and distance if available
                Log::info("📋 All available drivers for ride {$ride->id}:");
                foreach ($allDrivers as $d) {
                    $driverName = $d['name'] ?? 'Unknown';
                    $eta = $d['eta_seconds'] ?? 'N/A';
                    $distance = $d['distance_km'] ?? 'N/A';
                    Log::info("   - Driver ID: {$d['id']} | Name: {$driverName} | ETA: {$eta}s | Distance: {$distance}km");
                }

                // Step 5: Filter out excluded drivers
                $eligibleDrivers = array_filter($allDrivers, function ($d) use ($excludedDriverIds) {
                    return !in_array($d['id'], $excludedDriverIds);
                });

                if (empty($eligibleDrivers)) {
                    $eligibleDrivers = $allDrivers; // cycle back to all drivers
                }

                // Step 6: Find nearest driver by ETA
                $nearestDriver = $rideEstimateController->findNearestDriverByETA(
                    $ride->pickup_lat,
                    $ride->pickup_lng,
                    $eligibleDrivers,
                    $ride->id
                );

                // 🧭 Log nearest driver selection details
                if (!empty($nearestDriver)) {
                    Log::info("🏁 Nearest driver selected for ride {$ride->id}: " . json_encode([
                        'id' => $nearestDriver['driver_id'] ?? $nearestDriver['id'] ?? null,
                        'eta_seconds' => $nearestDriver['eta_seconds'] ?? 'N/A',
                        'distance_km' => $nearestDriver['distance_km'] ?? 'N/A',
                        'name' => $nearestDriver['driver']['name'] ?? $nearestDriver['name'] ?? 'Unknown'
                    ]));
                } else {
                    Log::warning("⚠️ No nearest driver found for ride {$ride->id}");
                }

                // ✅ Normalize structure if it's nested
                if (isset($nearestDriver['driver_id'])) {
                    $driverId = $nearestDriver['driver_id'];
                } elseif (isset($nearestDriver['driver']['id'])) {
                    $driverId = $nearestDriver['driver']['id'];
                } elseif (isset($nearestDriver['id'])) {
                    $driverId = $nearestDriver['id'];
                } else {
                    Log::warning("⚠️ Invalid nearest driver structure for ride {$ride->id}: " . json_encode($nearestDriver));
                    continue;
                }

                // ✅ Fetch driver and validate
                $driver = User::find($driverId);
                if (!$driver) {
                    Log::warning("⚠️ Driver ID {$driverId} not found in database for ride {$ride->id}");
                    continue;
                }

                $ride->update([
                    'driver_id' => $driver->id,
                    'status' => 'pending',
                    'driver_assigned_at' => now(),
                    'reassigned_at' => now(),
                ]);

                // Step 8: Update Firebase
                $this->updateFirebaseRideStatus($ride, 'pending', $driverId);

                // ✅ Step 9: Send notification in your format
                $driver = User::find($driverId);
                if ($driver && $driver->fcm_token) {
                    $data = [
                        'title'    => 'New Ride Request',
                        'body'     => 'You have a new ride request!',
                        'msg_type' => 'ride_request',
                        'ride_id'  => (string) $ride->id,
                    ];

                    $response = FcmHelper::sendPushNotification(
                        $driver->fcm_token,
                        $data['title'],
                        $data['body'],
                        $data
                    );

                    Log::info("📩 Notification sent to driver {$driver->id}", ['response' => $response]);
                } else {
                    Log::warning("No FCM token found for driver {$nearestDriver['id']}");
                }

                Log::info("✅ Reassigned ride {$ride->id} to driver {$nearestDriver['id']}");
            } catch (Exception $e) {
                Log::error("❌ Error processing ride {$ride->id}: " . $e->getMessage());
            }
        }

        Log::info('✅ Auto reject command finished at ' . now());
    }

    /**
     * ✅ Update Firebase (New Version)
     */
    private function updateFirebaseRideStatus($ride, $status, $driverId = null)
    {
        try {
            $firebase = (new Factory)
                ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                ->createDatabase();

            $firebaseRideId = 'ride_' . $ride->id;

            $data = [
                'status' => $status,
                'auto_rejected_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ];

            if ($driverId) {
                $data['driver_id'] = $driverId;
            }

            $firebase->getReference("rides/$firebaseRideId")->update($data);

            Log::info("🔥 Firebase updated for ride {$ride->id} (status: {$status})");
        } catch (Exception $e) {
            Log::error("❌ Failed to update Firebase for ride {$ride->id}: " . $e->getMessage());
        }
    }
}
