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
            // Lock only long enough to retire this captain. The driver search
            // hits Google and must not keep the row locked, or a passenger
            // cancel waits and the next captain is still offered the ride.
            $ride = Ride::where('id', $this->rideId)->lockForUpdate()->first();

            if (!$ride || !$ride->isAwaitingDriver() || (int) $ride->driver_id !== (int) $this->driverId) {
                DB::rollBack();
                Log::info("AutoRejectRideJob: Ride {$this->rideId} status changed, driver changed, or cancelled by user during execution.");
                return;
            }

            // The every-15-seconds command can wake up on the same offer.
            // One of them reassigns; the other leaves the ride alone.
            if (!\Illuminate\Support\Facades\Cache::add("ride_auto_reassign:{$ride->id}", 1, now()->addSeconds(8))) {
                DB::rollBack();
                Log::info("AutoRejectRideJob: Ride {$this->rideId} is already being reassigned");
                return;
            }

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

            DB::commit();

            Log::info("AutoRejectRideJob: Reset ride {$this->rideId}, driver {$this->driverId} added to rejected list: " . json_encode($rejectedDrivers));
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("❌ AutoRejectRideJob failed for ride {$this->rideId}: " . $e->getMessage());
            throw $e;
        }

        $ride->refresh();
        if (!$ride->isAwaitingDriver()) {
            Log::info("AutoRejectRideJob: Ride {$this->rideId} stopped searching before a new captain was chosen");
            return;
        }

        // Search for alternative driver outside the row lock.
        $rideEstimateController = new UserRideEstimateController();
        $alternativeDriver = $rideEstimateController->searchAlternativeDriver($ride);

        $ride->refresh();
        if (!$ride->isAwaitingDriver()) {
            Log::info("AutoRejectRideJob: Ride {$this->rideId} was cancelled or accepted, search stopped");
            return;
        }

        if (!$alternativeDriver) {
            Log::warning("❌ AutoRejectRideJob: No alternative drivers found for ride {$this->rideId}, marking as rejected");

            // Never overwrite a passenger cancel or an accept.
            $ride->updateWhileAwaiting([
                'status' => 'rejected',
            ], true, null);

            Log::info("AutoRejectRideJob: Ride {$this->rideId} rejected with no alternative driver");
            return;
        }

        $driverId = $alternativeDriver['driver_id'] ?? $alternativeDriver['id'] ?? null;
        if ($driverId && (int) $ride->driver_id === (int) $driverId) {
            AutoRejectRideJob::dispatch(
                $ride->id,
                $driverId,
                $ride->updated_at->format('Y-m-d H:i:s')
            )->delay(now()->addSeconds(15));

            Log::info("✅ AutoRejectRideJob: Ride {$this->rideId} reassigned to driver {$driverId} (previous driver: {$this->driverId})");
        }
    }


}
