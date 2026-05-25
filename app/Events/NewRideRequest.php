<?php

namespace App\Events;

use App\Models\Ride;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewRideRequest implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ride;

    public function __construct(Ride $ride)
    {
        $this->ride = $ride->load('user');
    }

    public function broadcastOn(): array
    {
        // Broadcast specifically to the assigned driver
        return [
            new PrivateChannel('driver.' . $this->ride->driver_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ride.request.new';
    }

    public function broadcastWith(): array
    {
        return [
            'ride_id' => $this->ride->id,
            'user' => [
                'id' => $this->ride->user->id,
                'name' => $this->ride->user->name,
                'phone' => $this->ride->user->phone,
                'avatar' => $this->ride->user->image_link,
                'rating' => $this->ride->user->average_rating,
            ],
            'pickup_address' => $this->ride->pickup_address,
            'dropoff_address' => $this->ride->dropoff_address,
            'estimated_price' => (float) $this->ride->calculated_initial_price,
            'estimated_time' => $this->ride->estimated_time,
            'estimated_km' => (float) $this->ride->estimated_km
        ];
    }
}
