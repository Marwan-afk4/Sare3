<?php

namespace App\Observers;

use App\Models\Delivery;
use Illuminate\Support\Facades\Log;

class DeliveryObserver
{
    public function created(Delivery $delivery)
    {
        try {
            event(new \App\Events\DeliveryStatusUpdated($delivery));
        } catch (\Throwable $e) {
            Log::error("Failed to broadcast DeliveryStatusUpdated for new delivery {$delivery->id}: " . $e->getMessage());
        }
    }

    public function updated(Delivery $delivery)
    {
        if ($delivery->wasChanged('status') || $delivery->wasChanged('rider_id')) {
            try {
                $previousRiderId = $delivery->wasChanged('rider_id')
                    ? $delivery->getOriginal('rider_id')
                    : null;
                event(new \App\Events\DeliveryStatusUpdated($delivery, $previousRiderId ? (int) $previousRiderId : null));
            } catch (\Throwable $e) {
                Log::error("Failed to broadcast DeliveryStatusUpdated for delivery {$delivery->id}: " . $e->getMessage());
            }
        }
    }
}
