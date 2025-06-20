<?php

namespace App\Events;

use App\Models\Point;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $point;

    /**
     * Create a new event instance.
     */
    public function __construct(Point $point)
    {
        $this->point = $point;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('driver.'. $this->point->user_id),
        ];
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->point->user_id,
            'longitude' => $this->point->longitude,
            'latitude' => $this->point->latitude,
            'updated_at' => $this->point->updated_at,
            'point_type' => $this->point->point_type,
        ];
    }
}
