<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int    $driverId;
    public ?int   $rideId;
    public float  $latitude;
    public float  $longitude;
    public ?float $bearing;
    public string $updatedAt;

    /**
     * Create a new event instance.
     */
    public function __construct(User $driver, ?int $rideId = null)
    {
        $this->driverId  = $driver->id;
        $this->rideId    = $rideId;
        $this->latitude  = (float) $driver->latitude;
        $this->longitude = (float) $driver->longitude;
        $this->bearing   = $driver->bearing !== null ? (float) $driver->bearing : null;
        $this->updatedAt = now()->toIso8601String();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * We broadcast on two channels:
     *  1. A private channel so only the driver themselves can subscribe.
     *  2. A public channel `driver-location` so admins / passengers can
     *     listen to ALL drivers without needing authentication.
     */
    public function broadcastOn(): array
    {
        return [
            // Private: only the driver can subscribe (used by the mobile app)
            new PrivateChannel('driver.' . $this->driverId),

            // Public: dashboard / passenger apps can listen without auth
            new Channel('driver-location'),
        ];
    }

    /**
     * Payload sent over the wire.
     */
    public function broadcastWith(): array
    {
        return [
            'driver_id'  => $this->driverId,
            'ride_id'    => $this->rideId,
            'latitude'   => $this->latitude,
            'longitude'  => $this->longitude,
            'bearing'    => $this->bearing,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * Event name used on the client side.
     */
    public function broadcastAs(): string
    {
        return 'driver.location.updated';
    }
}
