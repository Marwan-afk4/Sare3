<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Referral extends Model
{
    use HasFactory;

    protected $table = 'referrals';

    protected $fillable = [
        'referrer_id',
        'referred_user_id',
        'token',
        'accepted_at',
        'discount_percentage',
        'discount_rides_count',
        'used_rides_count',
        'is_active',
        'expires_at'
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public $timestamps = true;

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function discounts()
    {
        return $this->hasMany(ReferralDiscount::class);
    }

    public function referrerDiscounts()
    {
        return $this->hasMany(ReferrerDiscount::class);
    }

    /**
     * Check if referral is still valid for discounts
     */
    public function isValidForDiscount(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return $this->used_rides_count < $this->discount_rides_count;
    }

    /**
     * Get remaining discount rides
     */
    public function getRemainingRides(): int
    {
        return max(0, $this->discount_rides_count - $this->used_rides_count);
    }

    /**
     * Mark referral as accepted
     */
    public function markAsAccepted(User $referredUser): void
    {
        $this->update([
            'referred_user_id' => $referredUser->id,
            'accepted_at' => now(),
            'discount_percentage' => AppSetting::getReferralDiscountPercentage(),
            'discount_rides_count' => AppSetting::getReferralDiscountRides()
        ]);
    }

    /**
     * Use one discount ride
     */
    public function useDiscountRide(): void
    {
        $this->increment('used_rides_count');
        
        if ($this->used_rides_count >= $this->discount_rides_count) {
            $this->update(['is_active' => false]);
        }
    }

    /**
     * Get referral code for this referral
     */
    public function getReferralCodeAttribute(): string
    {
        // Generate a referral code based on referrer info
        if ($this->referrer) {
            $name = strtoupper(substr($this->referrer->name, 0, 3));
            return $name . str_pad($this->referrer->id, 4, '0', STR_PAD_LEFT);
        }
        return 'REF' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }
}
