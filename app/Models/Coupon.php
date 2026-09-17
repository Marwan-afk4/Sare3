<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'minimum_ride_amount',
        'maximum_discount',
        'usage_limit',
        'usage_count',
        'user_usage_limit',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'value' => 'decimal:2',
        'minimum_ride_amount' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
    ];

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function rides()
    {
        return $this->hasMany(Ride::class);
    }

    /**
     * Check if coupon is valid for use
     */
    public function isValid(): bool
    {
        return $this->is_active 
            && $this->starts_at <= now() 
            && $this->expires_at >= now()
            && ($this->usage_limit === null || $this->usage_count < $this->usage_limit);
    }

    /**
     * Check if user can use this coupon
     */
    public function canBeUsedByUser(User $user): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
        return $userUsageCount < $this->user_usage_limit;
    }

    /**
     * Calculate discount amount for a ride.
     * Coupons are not applied when the fare is already at the minimum price,
     * and they cannot reduce the fare below that minimum.
     */
    public function calculateDiscount(float $rideAmount, float $minPrice = 1.0): float
    {
        if ($this->minimum_ride_amount && $rideAmount < $this->minimum_ride_amount) {
            return 0;
        }

        if ($minPrice > 0 && $rideAmount <= $minPrice) {
            return 0;
        }

        $discount = 0;

        if ($this->type === 'percentage') {
            $discount = ($rideAmount * $this->value) / 100;
        } else {
            $discount = $this->value;
        }

        // Apply maximum discount limit if set
        if ($this->maximum_discount && $discount > $this->maximum_discount) {
            $discount = $this->maximum_discount;
        }

        $discount = min($discount, $rideAmount);

        if ($minPrice > 0) {
            $discount = min($discount, max(0, $rideAmount - $minPrice));
        }

        return $discount;
    }

    /**
     * Apply coupon to a ride
     */
    public function applyToRide(Ride $ride): bool
    {
        if (!$this->canBeUsedByUser($ride->user)) {
            return false;
        }

        $discountAmount = $this->calculateDiscount(
            (float) $ride->calculated_initial_price,
            $ride->minimumFare()
        );

        if ($discountAmount <= 0) {
            return false;
        }

        // Update ride with coupon information
        $ride->update([
            'coupon_id' => $this->id,
            'coupon_discount' => $discountAmount,
            'calculated_final_price' => $ride->calculated_initial_price - $discountAmount
        ]);

        // Create usage record
        CouponUsage::create([
            'coupon_id' => $this->id,
            'user_id' => $ride->user_id,
            'ride_id' => $ride->id,
            'discount_amount' => $discountAmount
        ]);

        // Increment usage count
        $this->increment('usage_count');

        return true;
    }

    /**
     * Scope for active coupons
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('starts_at', '<=', now())
                    ->where('expires_at', '>=', now());
    }

    /**
     * Scope for available coupons (not reached usage limit)
     */
    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('usage_limit')
              ->orWhereRaw('usage_count < usage_limit');
        });
    }
}