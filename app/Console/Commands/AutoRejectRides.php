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

                // Step 1: Mark the current driver as rejected
                $previousDriverId = $ride->driver_id;
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

                // Step 3: Get eligible drivers
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

                // Step 4: Filter out excluded drivers
                $eligibleDrivers = array_filter($allDrivers, function ($d) use ($excludedDriverIds) {
                    return !in_array($d['id'], $excludedDriverIds);
                });

                if (empty($eligibleDrivers)) {
                    $eligibleDrivers = $allDrivers; // cycle back if all are rejected
                }

                // Step 5: Find nearest driver by ETA
                $nearestDriver = $rideEstimateController->findNearestDriverByETA(
                    $ride->pickup_lat,
                    $ride->pickup_lng,
                    $eligibleDrivers,
                    $ride->id
                );

                if (empty($nearestDriver)) {
                    Log::warning("⚠️ No nearest driver found for ride {$ride->id}");
                    continue;
                }

                // ✅ Normalize driver ID safely
                $driverId = $nearestDriver['driver_id']
                    ?? ($nearestDriver['driver']['id'] ?? ($nearestDriver['id'] ?? null));

                if (!$driverId) {
                    Log::warning("⚠️ Invalid nearest driver structure for ride {$ride->id}: " . json_encode($nearestDriver));
                    continue;
                }

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

                // Step 6: Update Firebase
                $this->updateFirebaseRideStatus($ride, 'pending', $driverId);

                // Step 7: Send notification
                if ($driver->fcm_token) {
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

                    Log::info("📩 Notification sent to driver {$driverId}", ['response' => $response]);
                } else {
                    Log::warning("⚠️ No FCM token found for driver {$driverId}");
                }

                Log::info("✅ Reassigned ride {$ride->id} to driver {$driverId}");
            } catch (Exception $e) {
                Log::error("❌ Error processing ride {$ride->id}: " . $e->getMessage());
            }
        }

        Log::info('✅ Auto reject command finished at ' . now());
    }

    /**
     * ✅ Update Firebase
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
