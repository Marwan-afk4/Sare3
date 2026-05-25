<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ride;
use App\Models\User;
use App\Helpers\FcmHelper;
use App\Http\Controllers\Api\User\RideEstimateController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use Exception;

class AutoRejectRides extends Command
{
    protected $signature = 'rides:auto-reject';
    protected $description = 'Automatically reject rides if the driver does not respond within 10 seconds';

    public function handle()
    {
        Log::info('🚀 Auto reject command started at ' . now());
        $expiredTime = Carbon::now()->subSeconds(10);

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




                // Step 3: Get all available drivers
                $allDrivers = $rideEstimateController->getEligibleDrivers(
                    $ride->pickup_lat,
                    $ride->pickup_lng,
                    [],
                    $ride->car_category_id
                );

                if (empty($allDrivers)) {
                    Log::info("No drivers available for ride {$ride->id}");
                    $ride->update(['driver_id' => null, 'status' => 'pending']);

                    continue;
                }

                // 🧠 Log ALL available drivers (ETA will be calculated in next step)
                Log::info("📋 Found {count} available drivers for ride {$ride->id} (ETA calculation pending):", ['count' => count($allDrivers)]);
                foreach ($allDrivers as $d) {
                    $driverName = $d['name'] ?? 'Unknown';
                    Log::info("   - Driver ID: {$d['id']} | Name: {$driverName}");
                }

                // Step 4: Filter out excluded (rejected) drivers
                $eligibleDrivers = array_filter($allDrivers, function ($d) use ($excludedDriverIds) {
                    return !in_array($d['id'], $excludedDriverIds);
                });

                // 📝 Log filtering results
                $filteredOutCount = count($allDrivers) - count($eligibleDrivers);
                if ($filteredOutCount > 0) {
                    Log::info("🚫 Filtered out {$filteredOutCount} rejected driver(s). Rejected IDs: " . json_encode($excludedDriverIds));
                }

                $isCycling = false;
                if (empty($eligibleDrivers)) {
                    Log::info("🔄 All drivers rejected ride {$ride->id}, starting cycling mode");
                    $isCycling = true;
                    
                    // When cycling: sort all drivers by ETA and pick the NEXT one, not the first
                    // First, get all drivers with their ETAs
                    $allDriversWithETA = $rideEstimateController->getAllDriversSortedByETA(
                        $ride->pickup_lat,
                        $ride->pickup_lng,
                        $allDrivers,
                        $ride->id
                    );
                    
                    if (empty($allDriversWithETA)) {
                        Log::error("No drivers available with valid ETA for ride {$ride->id}");
                        continue;
                    }
                    
                    // Find the next driver to assign (round-robin through sorted list)
                    // Get the last assigned driver from rejected list to determine position
                    $lastDriverId = end($excludedDriverIds);
                    $lastDriverIndex = -1;
                    
                    foreach ($allDriversWithETA as $index => $driver) {
                        if ($driver['id'] === $lastDriverId) {
                            $lastDriverIndex = $index;
                            break;
                        }
                    }
                    
                    // Pick next driver in cycle (wrap around if at end)
                    $nextIndex = ($lastDriverIndex + 1) % count($allDriversWithETA);
                    $selectedDriver = $allDriversWithETA[$nextIndex];
                    
                    Log::info("🔄 Cycling: Last driver was at index {$lastDriverIndex}, selecting driver at index {$nextIndex} (ID: {$selectedDriver['id']})");
                    
                    // Format the driver data to match expected structure
                    $nearestDriver = [
                        'driver_id' => $selectedDriver['id'],
                        'eta_seconds' => $selectedDriver['eta_seconds'] ?? $selectedDriver['eta_time'],
                        'eta_minutes' => $selectedDriver['eta_minutes'] ?? round($selectedDriver['eta_time'] / 60, 1),
                        'driver' => $selectedDriver,
                    ];
                    
                    // Clear rejected list if we've completed a full cycle
                    if ($nextIndex === 0 && $lastDriverIndex >= 0) {
                        Log::info("🔄 Full cycle completed, clearing rejected drivers list");
                        $ride->update(['rejected_drivers' => []]);
                    }
                } else {
                    // ✅ CRITICAL: Re-index array to have sequential keys [0,1,2...] instead of [0,2,4...]
                    // This is necessary because Google API returns rows in sequential order
                    $eligibleDrivers = array_values($eligibleDrivers);

                    // 📋 Log eligible drivers being sent to ETA calculation
                    Log::info("✅ Sending " . count($eligibleDrivers) . " eligible driver(s) to ETA calculation:");
                    foreach ($eligibleDrivers as $d) {
                        Log::info("   → Driver ID: {$d['id']} | Name: {$d['name']}");
                    }

                    // Step 5: Find nearest driver by ETA (Google Distance Matrix API)
                    $nearestDriver = $rideEstimateController->findNearestDriverByETA(
                        $ride->pickup_lat,
                        $ride->pickup_lng,
                        $eligibleDrivers,
                        $ride->id
                    );
                }

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



                // Step 7: Send notification to new driver
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
                    Log::warning("No FCM token found for driver {$driver->id}");
                }

                Log::info("✅ Reassigned ride {$ride->id} to driver {$driver->id}");
            } catch (Exception $e) {
                Log::error("❌ Error processing ride {$ride->id}: " . $e->getMessage());
            }
        }

        Log::info('✅ Auto reject command finished at ' . now());
    }


}
