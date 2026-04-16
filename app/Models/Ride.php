<?php

namespace App\Models;

use App\Enums\RideStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    use HasFactory;

    protected $table = 'rides';

    protected $fillable = [
        'user_id',
        'driver_id',
        'car_category_id',
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
        'route_points',
        'calculated_final_price',
        'original_price',
        'discount_amount',
        'total_distance_in_km',
        'started_at',
        'ended_at',
        'time_taken',
        'firebase_ride_id',
        'payment_method_id',
        'rejected_drivers',
        'verification_code',
        'verification_code_generated_at',
        'verification_code_verified',
        'coupon_id',
        'coupon_discount',
        'wallet_paid_amount',
        'cancellation_reason_id',
        'zone_id',
        'reassigned_at',
        'driver_assigned_at',
        'accepted_at',
        'arrived_at',
        'trip_started_at',
        'completed_at',
        'driver_accept_lat',
        'driver_accept_lng',
        'driver_arrived_lat',
        'driver_arrived_lng',
    ];

    public $timestamps = true;

    protected $casts = [
        'route_points' => 'array',
        'rejected_drivers' => 'array',
        'status' => RideStatus::class,
        'verification_code_generated_at' => 'datetime',
        'verification_code_verified' => 'boolean',
        'reassigned_at' => 'datetime',
        'auto_rejected_at' => 'datetime',
        'accepted_at' => 'datetime',
        'arrived_at' => 'datetime',
        'trip_started_at' => 'datetime',
        'completed_at' => 'datetime',
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'dropoff_lat' => 'float',
        'dropoff_lng' => 'float',
        'driver_accept_lat' => 'float',
        'driver_accept_lng' => 'float',
        'driver_arrived_lat' => 'float',
        'driver_arrived_lng' => 'float',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function carCategory()
    {
        return $this->belongsTo(CarCategory::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(paymenentMethod::class);
    }

    public function profit()
    {
        return $this->hasOne(RideProfit::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function couponUsage()
    {
        return $this->hasOne(CouponUsage::class);
    }

    public function cancellationReason()
    {
        return $this->belongsTo(CancellationReason::class);
    }

    /**
     * Generate a 6-digit verification code
     */
    public function generateVerificationCode(): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'verification_code' => $code,
            'verification_code_generated_at' => now(),
            'verification_code_verified' => false
        ]);

        return $code;
    }

    /**
     * Verify the provided code
     */
    public function verifyCode(string $code): bool
    {
        if ($this->verification_code === $code && !$this->verification_code_verified) {
            $this->update(['verification_code_verified' => true]);
            return true;
        }

        return false;
    }

    /**
     * Check if verification code is required for this ride
     */
    public function requiresVerificationCode(): bool
    {
        try {
            return \App\Models\AppSetting::isRideVerificationEnabled() &&
                $this->status->value === 'accepted' &&
                !empty($this->verification_code);
        } catch (\Exception $e) {
            // If database is not available, return false (feature disabled)
            return false;
        }
    }

    /**
     * Check if ride can be started (verification passed or not required)
     */
    public function canStart(): bool
    {
        try {
            if (!\App\Models\AppSetting::isRideVerificationEnabled()) {
                return true;
            }

            return $this->verification_code_verified || empty($this->verification_code);
        } catch (\Exception $e) {
            // If database is not available, allow ride to start (feature disabled)
            return true;
        }
    }
}
