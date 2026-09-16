<?php

namespace App\Events;

use App\Models\Delivery;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewDeliveryRequest implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $delivery;

    public function __construct(Delivery $delivery)
    {
        $this->delivery = $delivery->load('user');
    }

    public function broadcastOn(): array
    {
        // Broadcast specifically to the assigned rider.
        return [
            new PrivateChannel('rider.' . $this->delivery->rider_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'delivery.request.new';
    }

    public function broadcastWith(): array
    {
        $data = [
            'delivery_id' => $this->delivery->id,
            'vehicle_type' => $this->delivery->vehicle_type?->value,
            'user' => [
                'id' => $this->delivery->user->id,
                'name' => $this->delivery->user->name,
                'phone' => $this->delivery->user->phone,
                'avatar' => $this->delivery->user->image_link,
                'rating' => $this->delivery->user->average_rating,
            ],
            'pickup_lat' => (float) $this->delivery->pickup_lat,
            'pickup_lng' => (float) $this->delivery->pickup_lng,
            'pickup_address' => $this->delivery->pickup_address,
            'dropoff_address' => $this->delivery->dropoff_address,
            'estimated_price' => (float) $this->delivery->calculated_initial_price,
            'estimated_time' => $this->delivery->estimated_time,
            'estimated_km' => (float) $this->delivery->estimated_km,
        ];

        if ($this->delivery->dropoff_lat !== null && $this->delivery->dropoff_lng !== null) {
            $data['dropoff_lat'] = (float) $this->delivery->dropoff_lat;
            $data['dropoff_lng'] = (float) $this->delivery->dropoff_lng;
        }

        return $data;
    }
}
