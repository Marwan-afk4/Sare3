<?php

namespace App\Events;

use App\Models\Delivery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $delivery_id;
    public $status;
    public $rider_id;
    public $user_id;
    public $vehicle_type;
    public $rider_data;
    public $completed_details;
    public $previous_rider_id;

    public function __construct(Delivery $delivery, ?int $previousRiderId = null)
    {
        $this->delivery_id = $delivery->id;
        $this->status = $delivery->status->value;
        $this->rider_id = $delivery->rider_id;
        $this->user_id = $delivery->user_id;
        $this->vehicle_type = $delivery->vehicle_type?->value;
        $this->previous_rider_id = $previousRiderId;

        $this->rider_data = null;
        if ($delivery->rider_id) {
            if (!$delivery->relationLoaded('rider')) {
                $delivery->load(['rider' => function ($q) {
                    $q->with('riderVehicle');
                }]);
            }

            $rider = $delivery->rider;
            if ($rider) {
                $vehicle = $rider->riderVehicle;
                $this->rider_data = [
                    'id' => $rider->id,
                    'name' => $rider->name ?? '',
                    'email' => $rider->email,
                    'phone' => $rider->phone ?? '',
                    'image_link' => $rider->image_link ?? '',
                    'gender' => $rider->gender,
                    'average_rating' => $rider->average_rating,
                    'vehicle' => $vehicle ? [
                        'type' => $vehicle->type?->value,
                        'vehicle_image_link' => $vehicle->vehicle_image_link,
                    ] : null,
                ];
            }
        }

        $this->completed_details = null;
        if (in_array($this->status, ['completed', 'finshed'])) {
            $this->completed_details = [
                'distance_km' => $delivery->total_distance_in_km ? (float) $delivery->total_distance_in_km : 0.0,
                'duration_minutes' => $delivery->time_taken ? (int) $delivery->time_taken : 0,
                'final_price' => $delivery->calculated_final_price ? (float) $delivery->calculated_final_price : 0.0,
                'original_price' => $delivery->original_price ? (float) $delivery->original_price : 0.0,
            ];
        }
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('delivery.' . $this->delivery_id),
            new PrivateChannel('user.' . $this->user_id),
            new Channel('delivery-updates'), // Admin dashboard
        ];

        if (!empty($this->rider_id)) {
            $channels[] = new PrivateChannel('rider.' . $this->rider_id);
        }

        if (!empty($this->previous_rider_id) && $this->previous_rider_id !== $this->rider_id) {
            $channels[] = new PrivateChannel('rider.' . $this->previous_rider_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'delivery.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'delivery_id' => $this->delivery_id,
            'status' => $this->status,
            'rider_id' => $this->rider_id,
            'user_id' => $this->user_id,
            'vehicle_type' => $this->vehicle_type,
            'rider' => $this->rider_data,
            'completed_details' => $this->completed_details,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
