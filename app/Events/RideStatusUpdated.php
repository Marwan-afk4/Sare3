<?php

namespace App\Events;

use App\Models\Ride;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RideStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ride_id;
    public $status;
    public $driver_id;
    public $user_id;

    /**
     * Create a new event instance.
     */
    public function __construct(Ride $ride)
    {
        $this->ride_id = $ride->id;
        $this->status = $ride->status->value;
        $this->driver_id = $ride->driver_id;
        $this->user_id = $ride->user_id;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('ride.' . $this->ride_id),
            new PrivateChannel('user.' . $this->user_id),
            $this->driver_id ? new PrivateChannel('driver.' . $this->driver_id) : null,
            new Channel('ride-updates'), // For Admin Dashboard
        ];
    }

    public function broadcastAs(): string
    {
        return 'ride.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'ride_id' => $this->ride_id,
            'status' => $this->status,
            'driver_id' => $this->driver_id,
            'user_id' => $this->user_id,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
