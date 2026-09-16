<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RiderLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int    $riderId;
    public ?int   $deliveryId;
    public float  $latitude;
    public float  $longitude;
    public ?float $bearing;
    public string $updatedAt;

    public function __construct(User $rider, ?int $deliveryId = null)
    {
        $this->riderId    = $rider->id;
        $this->deliveryId = $deliveryId;
        $this->latitude   = (float) $rider->latitude;
        $this->longitude  = (float) $rider->longitude;
        $this->bearing    = $rider->bearing !== null ? (float) $rider->bearing : null;
        $this->updatedAt  = now()->toIso8601String();
    }

    /**
     * Mirror of DriverLocationUpdated:
     *  1. Private rider channel (mobile app).
     *  2. Public `rider-location` channel (admin / passenger apps).
     *  3. Private delivery channel when on an active delivery.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('rider.' . $this->riderId),
            new Channel('rider-location'),
        ];

        if ($this->deliveryId) {
            $channels[] = new PrivateChannel('delivery.' . $this->deliveryId);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        return [
            'rider_id'    => $this->riderId,
            'delivery_id' => $this->deliveryId,
            'latitude'    => $this->latitude,
            'longitude'   => $this->longitude,
            'bearing'     => $this->bearing,
            'updated_at'  => $this->updatedAt,
        ];
    }

    public function broadcastAs(): string
    {
        return 'rider.location.updated';
    }
}
