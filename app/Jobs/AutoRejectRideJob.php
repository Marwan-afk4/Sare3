<?php

namespace App\Jobs;

use App\Http\Controllers\Api\User\RideEstimateController as UserRideEstimateController;
use App\Models\Ride;
use App\Http\Controllers\RideEstimateController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class AutoRejectRideJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rideId;
    protected $driverId;
    protected $assignedAt;

    public function __construct($rideId, $driverId, $assignedAt)
    {
        $this->rideId = $rideId;
        $this->driverId = $driverId;
        $this->assignedAt = $assignedAt;
    }

    public function handle()
    {
        $ride = Ride::find($this->rideId);

        if (!$ride) {
            Log::info("AutoRejectRideJob: Ride {$this->rideId} not found");
            return;
        }

        // Check if ride is still pending and assigned to the same driver
        if ($ride->status->value !== 'pending' || $ride->driver_id !== $this->driverId) {
            Log::info("AutoRejectRideJob: Ride {$this->rideId} status changed or driver changed");
            return;
        }

        // Check if the assignment time matches (prevent duplicate jobs)
        if ($ride->updated_at->format('Y-m-d H:i:s') !== $this->assignedAt) {
            Log::info("AutoRejectRideJob: Ride {$this->rideId} assignment time mismatch");
            return;
        }

        Log::info("AutoRejectRideJob: Auto-rejecting ride {$this->rideId} for driver {$this->driverId}");

        DB::beginTransaction();

        try {
            // Add current driver to rejected drivers list
            $rejectedDrivers = $ride->rejected_drivers ?? [];
            if (!in_array($this->driverId, $rejectedDrivers)) {
                $rejectedDrivers[] = $this->driverId;
            }

            // Reset ride and mark as auto-rejected
            $ride->update([
                'driver_id' => null,
                'rejected_drivers' => $rejectedDrivers,
                'status' => 'pending',
                'auto_rejected_at' => now(),
            ]);

            // Update Firebase
            $firebase = $this->getFirebaseDatabase();
            $firebaseRideId = 'ride_' . $ride->id;

            $firebase->getReference("rides/$firebaseRideId")->update([
                'driver_id' => null,
                'rejected_drivers' => $rejectedDrivers,
                'status' => 'pending',
                'auto_rejected_at' => now()->toIso8601String(),
                'rejection_reason' => 'auto_timeout'
            ]);

            // Search for alternative driver
            $rideEstimateController = new UserRideEstimateController();
            $alternativeDriver = $rideEstimateController->searchAlternativeDriver($ride);

            if ($alternativeDriver) {
                $ride->update([
                    'driver_id' => $alternativeDriver['id'],
                    'reassigned_at' => now(),
                ]);

                $firebase->getReference("rides/$firebaseRideId")->update([
                    'driver_id' => $alternativeDriver['id'],
                    'reassigned_at' => now()->toIso8601String(),
                    'previous_rejections' => count($rejectedDrivers),
                ]);

                // Schedule another auto-reject job for the new driver
                AutoRejectRideJob::dispatch(
                    $ride->id,
                    $alternativeDriver['id'],
                    $ride->updated_at->format('Y-m-d H:i:s')
                )->delay(now()->addSeconds(15));

                Log::info("AutoRejectRideJob: Ride {$this->rideId} reassigned to driver {$alternativeDriver['id']}");
            } else {
                Log::info("AutoRejectRideJob: No alternative drivers found for ride {$this->rideId}");
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("AutoRejectRideJob failed for ride {$this->rideId}: " . $e->getMessage());
            throw $e;
        }
    }

    private function getFirebaseDatabase(): Database
    {
        return (new Factory)
            ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
            ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
            ->createDatabase();
    }
}
