<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActiveRiderLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $rider_id;
    public float $latitude;
    public float $longitude;
    public ?float $bearing;
    public ?string $vehicle_type;
    public string $updated_at;

    public function __construct(User $rider)
    {
        $this->rider_id = $rider->id;
        $this->latitude = (float) $rider->latitude;
        $this->longitude = (float) $rider->longitude;
        $this->bearing = $rider->bearing !== null ? (float) $rider->bearing : null;

        $vehicle = $rider->riderVehicle()->first();
        $this->vehicle_type = $vehicle?->type?->value;

        $this->updated_at = now()->toIso8601String();
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('active-riders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'active.rider.location.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'rider_id' => $this->rider_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'bearing' => $this->bearing,
            'vehicle_type' => $this->vehicle_type,
            'updated_at' => $this->updated_at,
        ];
    }
}
