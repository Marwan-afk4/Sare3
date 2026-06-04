<?php

namespace App\Jobs;

use App\Http\Controllers\Api\User\RideEstimateController as UserRideEstimateController;
use App\Models\Ride;
use App\Http\Controllers\RideEstimateController;
use App\Services\RideOfferService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\RideStatusUpdated;

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

        // Track auto-reject count for this driver on this ride
        if ($this->driverId) {
            $autoRejectCount = (int) \Illuminate\Support\Facades\Cache::get("ride_auto_reject_count:{$ride->id}:{$this->driverId}", 0) + 1;
            \Illuminate\Support\Facades\Cache::put("ride_auto_reject_count:{$ride->id}:{$this->driverId}", $autoRejectCount, now()->addMinutes(5));
            
            if ($autoRejectCount >= 2) {
                \Illuminate\Support\Facades\Cache::put("ride_cooldown:{$ride->id}:{$this->driverId}", 'auto', now()->addMinutes(2));
                Log::info("AutoRejectRideJob: Driver {$this->driverId} auto-rejected ride {$ride->id} {$autoRejectCount} times consecutively. Placing on 2-minute cooldown.");
            }
        }

        DB::beginTransaction();

        try {
            // Add current driver to rejected drivers list
            $rejectedDrivers = $ride->rejected_drivers ?? [];
            
            // ⚠️ Ensure all driver IDs are integers for consistent comparison
            $rejectedDrivers = array_map(function($id) {
                return is_numeric($id) ? (int)$id : $id;
            }, $rejectedDrivers);
            
            if (!in_array((int)$this->driverId, $rejectedDrivers)) {
                $rejectedDrivers[] = (int)$this->driverId;
            }

            // Reset ride and mark as auto-rejected
            $ride->update([
                'driver_id' => null,
                'rejected_drivers' => $rejectedDrivers,
                'status' => 'pending',
                'auto_rejected_at' => now(),
            ]);

            // Mark this captain's offer as "ignored" in the audit trail
            // (they got the request but didn't respond in time).
            try {
                app(RideOfferService::class)->markIgnored($ride, (int) $this->driverId, 'auto_timeout');
            } catch (\Throwable $offerEx) {
                Log::warning("AutoRejectRideJob: offer bookkeeping failed: " . $offerEx->getMessage());
            }

            Log::info("AutoRejectRideJob: Reset ride {$this->rideId}, driver {$this->driverId} added to rejected list: " . json_encode($rejectedDrivers));



            // Reload the ride to get fresh data
            $ride->refresh();

            // Search for alternative driver
            $rideEstimateController = new UserRideEstimateController();
            $alternativeDriver = $rideEstimateController->searchAlternativeDriver($ride);

            $driverId = null;
            if ($alternativeDriver) {
                // Note: searchAlternativeDriver already updates the ride with the new driver_id
                // Just need to reload
                $ride->refresh();
                
                $driverId = $alternativeDriver['driver_id'] ?? $alternativeDriver['id'] ?? null;
            } else {
                Log::warning("❌ AutoRejectRideJob: No alternative drivers found for ride {$this->rideId}, marking as rejected");
                
                // Mark ride as truly rejected if no alternative driver found
                $ride->update([
                    'status' => 'rejected',
                ]);
            }

            DB::commit();

            // Broadcast and dispatch AFTER commit
            if ($alternativeDriver) {
                // ✅ Broadcast new driver assignment to passenger AND dismiss ride from old driver
                try {
                    RideStatusUpdated::dispatch($ride, (int) $this->driverId);
                    Log::info("📡 Broadcasted RideStatusUpdated event for reassigned ride {$ride->id} (old driver: {$this->driverId})");
                } catch (\Exception $e) {
                    Log::error("⚠️ Failed to broadcast RideStatusUpdated event for ride {$ride->id}: " . $e->getMessage());
                }

                // Schedule another auto-reject job for the new driver
                if ($driverId) {
                    AutoRejectRideJob::dispatch(
                        $ride->id,
                        $driverId,
                        $ride->updated_at->format('Y-m-d H:i:s')
                    )->delay(now()->addSeconds(15));
                }

                Log::info("✅ AutoRejectRideJob: Ride {$this->rideId} reassigned to driver {$driverId} (previous driver: {$this->driverId})");
            } else {
                // ✅ Broadcast rejection to passenger AND dismiss ride from old driver
                try {
                    RideStatusUpdated::dispatch($ride, (int) $this->driverId);
                    Log::info("📡 Broadcasted RideStatusUpdated event for rejected ride {$ride->id} (old driver: {$this->driverId})");
                } catch (\Exception $e) {
                    Log::error("⚠️ Failed to broadcast RideStatusUpdated event for ride {$ride->id}: " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("❌ AutoRejectRideJob failed for ride {$this->rideId}: " . $e->getMessage());
            throw $e;
        }
    }


}
