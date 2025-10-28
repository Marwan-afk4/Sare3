<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ride;
use Carbon\Carbon;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\User\RideEstimateController;

class AutoRejectRides extends Command
{
    protected $signature = 'rides:auto-reject';
    protected $description = 'Automatically reject rides if driver takes no action within 15 seconds';

    public function handle()
    {
        Log::info('🚀 Auto reject command started at ' . now());
        $expiredTime = Carbon::now()->subSeconds(15);

        // Find all rides that have been pending too long
        $rides = Ride::where('status', 'pending')
            ->whereNotNull('driver_id') // ✅ Must have a driver assigned
            ->whereNotNull('driver_assigned_at')
            ->where('driver_assigned_at', '<=', $expiredTime)
            ->get();

        foreach ($rides as $ride) {
            $currentDriverId = $ride->driver_id;
            
            // ⚠️ Safety check: Skip if no driver_id (shouldn't happen with query above)
            if (!$currentDriverId) {
                Log::warning("⚠️ Skipping ride {$ride->id} - no driver_id found");
                continue;
            }
            
            // ✅ Add current driver to rejected list
            $rejectedDrivers = $ride->rejected_drivers ?? [];
            
            // ⚠️ Ensure all driver IDs are integers for consistent comparison
            $rejectedDrivers = array_map(function($id) {
                return is_numeric($id) ? (int)$id : $id;
            }, $rejectedDrivers);
            
            if ($currentDriverId && !in_array((int)$currentDriverId, $rejectedDrivers)) {
                $rejectedDrivers[] = (int)$currentDriverId;
            }
            
            // ⚠️ IMPORTANT: Keep status as 'pending' (not 'rejected') so we can search for alternative driver
            // Set driver_id to null temporarily
            $ride->update([
                'driver_id' => null,
                'status' => 'pending',
                'rejected_drivers' => $rejectedDrivers,
                'auto_rejected_at' => now(),
            ]);

            Log::info("Auto-rejected ride ID {$ride->id} (driver ID: {$currentDriverId}). Rejected drivers list: " . json_encode($rejectedDrivers));

            try {
                // ✅ Update Firebase for real-time UI sync
                $firebase = (new Factory)
                    ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                    ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                    ->createDatabase();

                $firebaseRideId = 'ride_' . $ride->id;

                $firebase->getReference("rides/$firebaseRideId")->update([
                    'driver_id' => null,
                    'status' => 'pending',
                    'rejected_drivers' => $rejectedDrivers,
                    'auto_rejected_at' => now()->toIso8601String(),
                    'rejection_reason' => 'auto_timeout'
                ]);

                Log::info("Firebase updated for auto-rejected ride {$ride->id}");
            } catch (\Exception $e) {
                Log::error("Failed to update Firebase for auto-rejected ride {$ride->id}: " . $e->getMessage());
            }
            
            // ✅ Reload the ride to get fresh data after update
            $ride->refresh();

            // ✅ Search for alternative driver using existing controller method
            $rideEstimateController = new RideEstimateController();
            $newDriver = $rideEstimateController->searchAlternativeDriver($ride);
            
            if ($newDriver) {
                Log::info("✅ Found alternative driver {$newDriver['id']} for ride {$ride->id} (previous driver: {$currentDriverId})");
                // Note: searchAlternativeDriver already updated the ride with new driver_id and sent notification
            } else {
                Log::warning("❌ No alternative driver found for ride {$ride->id} (previous driver: {$currentDriverId})");
                // If no alternative driver found, mark ride as truly rejected
                $ride->update([
                    'status' => 'rejected',
                ]);
                
                // Update Firebase to reflect rejection
                try {
                    $firebase = (new Factory)
                        ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                        ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                        ->createDatabase();

                    $firebaseRideId = 'ride_' . $ride->id;
                    $firebase->getReference("rides/$firebaseRideId")->update([
                        'status' => 'rejected',
                        'rejection_reason' => 'no_drivers_available',
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to update Firebase for fully rejected ride {$ride->id}: " . $e->getMessage());
                }
            }
        }

        $this->info('Auto reject process complete. Total rejected: ' . $rides->count());
        Log::info('✅ Auto reject command finished at ' . now());
    }
}
