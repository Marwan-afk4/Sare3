<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActiveDriverLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $driver_id;
    public float $latitude;
    public float $longitude;
    public ?float $bearing;
    public ?int $car_category_id;
    public string $updated_at;

    public function __construct(User $driver)
    {
        $this->driver_id = $driver->id;
        $this->latitude = (float) $driver->latitude;
        $this->longitude = (float) $driver->longitude;
        $this->bearing = $driver->bearing !== null ? (float) $driver->bearing : null;
        
        // Fetch driver's car category
        $driverCar = $driver->driverCars()->first();
        $this->car_category_id = $driverCar ? (int) $driverCar->car_categories_id : null;
        
        $this->updated_at = now()->toIso8601String();
    }

    public function broadcastOn(): array
    {
        // Public channel specifically for broadcasting active drivers' locations to users
        return [
            new Channel('active-drivers'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'active.driver.location.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'driver_id' => $this->driver_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'bearing' => $this->bearing,
            'car_category_id' => $this->car_category_id,
            'updated_at' => $this->updated_at,
        ];
    }
}
