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
        return $this->delivery->toOfferArray();
    }
}
