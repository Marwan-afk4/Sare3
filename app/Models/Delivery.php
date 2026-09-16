<?php

namespace App\Models;

use App\Enums\RideStatus;
use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $table = 'deliveries';

    protected $fillable = [
        'user_id',
        'rider_id',
        'zone_id',
        'payment_method_id',
        'vehicle_type',
        'pickup_lat',
        'pickup_lng',
        'pickup_address',
        'dropoff_lat',
        'dropoff_lng',
        'dropoff_address',
        'status',
        'estimated_km',
        'estimated_time',
        'calculated_initial_price',
        'calculated_final_price',
        'original_price',
        'total_distance_in_km',
        'time_taken',
        'route_points',
        'to_pickup_route_points',
        'rejected_riders',
        'reassigned_at',
        'rider_assigned_at',
        'auto_rejected_at',
        'accepted_at',
        'arrived_at',
        'started_at',
        'ended_at',
        'completed_at',
        'rider_accept_lat',
        'rider_accept_lng',
        'rider_arrived_lat',
        'rider_arrived_lng',
        'rider_cancel_lat',
        'rider_cancel_lng',
        'rider_cancelled_at',
        'rider_cancelled_by',
        'cancelled_before_accept',
        'cancellation_reason_id',
    ];

    protected $casts = [
        'status' => RideStatus::class,
        'vehicle_type' => VehicleType::class,
        'route_points' => 'array',
        'to_pickup_route_points' => 'array',
        'rejected_riders' => 'array',
        'reassigned_at' => 'datetime',
        'rider_assigned_at' => 'datetime',
        'auto_rejected_at' => 'datetime',
        'accepted_at' => 'datetime',
        'arrived_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'completed_at' => 'datetime',
        'rider_cancelled_at' => 'datetime',
        'cancelled_before_accept' => 'boolean',
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'dropoff_lat' => 'float',
        'dropoff_lng' => 'float',
        'rider_accept_lat' => 'float',
        'rider_accept_lng' => 'float',
        'rider_arrived_lat' => 'float',
        'rider_arrived_lng' => 'float',
        'rider_cancel_lat' => 'float',
        'rider_cancel_lng' => 'float',
    ];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymenentMethod::class, 'payment_method_id');
    }

    public function offers()
    {
        return $this->hasMany(DeliveryOffer::class)->orderBy('offered_at');
    }

    public function riderCancelledBy()
    {
        return $this->belongsTo(User::class, 'rider_cancelled_by');
    }

    /**
     * Persist the rider's GPS at cancel time. Prefer coordinates from the
     * request; otherwise use the rider's last known location.
     */
    public function recordRiderCancelLocation(?User $rider, mixed $lat = null, mixed $lng = null): void
    {
        if ($lat === null || $lat === '' || $lng === null || $lng === '') {
            $lat = $rider?->latitude;
            $lng = $rider?->longitude;
        }

        if ($rider === null && ($lat === null || $lng === null)) {
            return;
        }

        $update = [
            'rider_cancelled_at' => now(),
            'rider_cancelled_by' => $rider?->id,
        ];

        if ($lat !== null && $lng !== null) {
            $update['rider_cancel_lat'] = (float) $lat;
            $update['rider_cancel_lng'] = (float) $lng;
        }

        $this->update($update);
    }
}
