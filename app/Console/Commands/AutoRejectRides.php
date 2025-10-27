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
            ->whereNotNull('driver_assigned_at')
            ->where('driver_assigned_at', '<=', $expiredTime)
            ->get();

        foreach ($rides as $ride) {
            $ride->update([
                'status' => 'rejected',
            ]);

            Log::info("Auto-rejected ride ID {$ride->id} (driver ID: {$ride->driver_id})");

            try {
                // ✅ Update Firebase for real-time UI sync
                $firebase = (new Factory)
                    ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                    ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                    ->createDatabase();

                $firebaseRideId = 'ride_' . $ride->id;

                $firebase->getReference("rides/$firebaseRideId")->update([
                    'status' => 'rejected',
                    'auto_rejected_at' => now()->toIso8601String(),
                ]);

                Log::info("Firebase updated for auto-rejected ride {$ride->id}");
            } catch (\Exception $e) {
                Log::error("Failed to update Firebase for auto-rejected ride {$ride->id}: " . $e->getMessage());
            }

            $rejected = $ride->rejected_drivers ?? [];

            if ($ride->driver_id) { // ✅ تأكد إن فيه driver_id فعلاً
                $rejected[] = (int) $ride->driver_id;
            }

            $ride->update([
                'status' => 'pending',
                'auto_rejected_at' => now(),
                'rejected_drivers' => array_unique(array_filter($rejected)), // ✅ إزالة null وتكرار
            ]);



            // ✅ Search for alternative driver using existing controller method
            $rideEstimateController = new RideEstimateController();
            $rideEstimateController->searchAlternativeDriver($ride);
        }

        $this->info('Auto reject process complete. Total rejected: ' . $rides->count());
        Log::info('✅ Auto reject command finished at ' . now());
    }
}
