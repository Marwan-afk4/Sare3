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
    public $completed_details;
    public $previous_driver_id;

    /**
     * Create a new event instance.
     */
    public function __construct(Ride $ride, ?int $previousDriverId = null)
    {
        $this->ride_id = $ride->id;
        $this->status = $ride->status->value;
        $this->driver_id = $ride->driver_id;
        $this->user_id = $ride->user_id;
        $this->verification_code = $ride->verification_code;
        $this->previous_driver_id = $previousDriverId;

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

        $this->completed_details = null;
        if ($this->status === 'completed' || $this->status === 'finshed') {
            $this->completed_details = [
                'distance_km' => $ride->total_distance_in_km ? (float) $ride->total_distance_in_km : 0.0,
                'duration_minutes' => $ride->time_taken ? (int) $ride->time_taken : 0,
                'final_price' => $ride->calculated_final_price ? (float) $ride->calculated_final_price : 0.0,
                'original_price' => $ride->original_price ? (float) $ride->original_price : 0.0,
                'discount_amount' => $ride->discount_amount ? (float) $ride->discount_amount : 0.0,
                'coupon_discount' => $ride->coupon_discount ? (float) $ride->coupon_discount : 0.0,
                'wallet_paid_amount' => $ride->wallet_paid_amount ? (float) $ride->wallet_paid_amount : 0.0,
                'user_wallet_before' => $ride->user_wallet_before !== null ? (float) $ride->user_wallet_before : null,
                'user_wallet_after' => $ride->user_wallet_after !== null ? (float) $ride->user_wallet_after : null,
            ];
        }
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('ride.' . $this->ride_id),
            new PrivateChannel('user.' . $this->user_id),
            new Channel('ride-updates'), // For Admin Dashboard
        ];

        if (! empty($this->driver_id)) {
            $channels[] = new PrivateChannel('driver.' . $this->driver_id);
        }

        // Also notify the previous driver so their app can dismiss the ride request UI
        if (! empty($this->previous_driver_id) && $this->previous_driver_id !== $this->driver_id) {
            $channels[] = new PrivateChannel('driver.' . $this->previous_driver_id);
        }

        return $channels;
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
            'completed_details' => $this->completed_details,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
