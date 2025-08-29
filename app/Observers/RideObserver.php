<?php

namespace App\Observers;

use App\Models\Ride;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Log;

class RideObserver
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Handle the Ride "updated" event.
     */
    public function updated(Ride $ride)
    {
        // Check if status was changed
        if ($ride->isDirty('status')) {
            $newStatus = $ride->status->value;
            $oldStatus = $ride->getOriginal('status')->value;

            Log::info("Ride {$ride->id} status changed from {$oldStatus} to {$newStatus}");

            // Update Firebase with new status
            $this->firebaseService->updateRideStatus(
                $ride->id,
                $newStatus,
                $ride->firebase_ride_id
            );

            // If ride is completed, cleanup Firebase tracking data
            if (in_array($newStatus, ['completed', 'finished', 'finshed'])) {
                Log::info("Ride {$ride->id} completed, cleaning up Firebase tracking data");

                // Wait a moment before cleanup to ensure status update is received
                dispatch(function () use ($ride) {
                    sleep(2); // Wait 2 seconds
                    $this->firebaseService->cleanupRideData($ride->id, $ride->firebase_ride_id);
                })->delay(now()->addSeconds(5));
            }
        }
    }
}
