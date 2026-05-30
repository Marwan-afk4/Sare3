<?php

namespace App\Observers;

use App\Models\Ride;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Log;

class RideObserver
{
    public function created(Ride $ride)
    {
        Log::info("Ride {$ride->id} created. Status: {$ride->status->value}. Driver: {$ride->driver_id}");
        
        // Notify the driver and admin dashboard immediately
        try {
            event(new \App\Events\RideStatusUpdated($ride));
        } catch (\Throwable $e) {
            Log::error("Failed to broadcast RideStatusUpdated for new ride {$ride->id}: " . $e->getMessage());
        }
    }

    public function updated(Ride $ride)
    {
        // Check if status or driver_id was changed
        if ($ride->isDirty('status') || $ride->isDirty('driver_id')) {
            $newStatus = $ride->status->value;
            $oldStatus = $ride->getOriginal('status') ? $ride->getOriginal('status')->value : 'none';

            Log::info("Ride {$ride->id} updated. Status: {$oldStatus} -> {$newStatus}. Driver: " . $ride->getOriginal('driver_id') . " -> " . $ride->driver_id);

            // Broadcast status update via WebSockets (must not fail the DB update)
            try {
                event(new \App\Events\RideStatusUpdated($ride));
            } catch (\Throwable $e) {
                Log::error("Failed to broadcast RideStatusUpdated for ride {$ride->id}: " . $e->getMessage());
            }
        }
    }
}
