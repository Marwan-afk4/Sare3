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
    public $verification_code;
    public $driver_data;

    /**
     * Create a new event instance.
     */
    public function __construct(Ride $ride)
    {
        $this->ride_id = $ride->id;
        $this->status = $ride->status->value;
        $this->driver_id = $ride->driver_id;
        $this->user_id = $ride->user_id;
        $this->verification_code = $ride->verification_code;

        $this->driver_data = null;
        if ($ride->driver_id) {
            // Eager-load driver and their cars if not loaded already
            if (!$ride->relationLoaded('driver')) {
                $ride->load(['driver' => function ($q) {
                    $q->with(['driverCars' => function ($q2) {
                        $q2->with(['carModel', 'carType']);
                    }]);
                }]);
            }
            
            $driver = $ride->driver;
            if ($driver) {
                $driverCar = $driver->driverCars->first();
                $this->driver_data = [
                    'id' => $driver->id,
                    'name' => $driver->name ?? '',
                    'email' => $driver->email,
                    'phone' => $driver->phone ?? '',
                    'image_link' => $driver->image_link ?? '',
                    'gender' => $driver->gender,
                    'average_rating' => $driver->average_rating,
                    'car' => $driverCar ? [
                        'id' => $driverCar->id,
                        'car_number' => $driverCar->car_number,
                        'car_color' => $driverCar->car_color,
                        'car_image_link' => $driverCar->car_image_link,
                        'car_model' => $driverCar->carModel->name ?? '',
                        'car_type' => $driverCar->carType->name ?? '',
                    ] : null
                ];
            }
        }
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
            'verification_code' => $this->verification_code,
            'driver' => $this->driver_data,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
