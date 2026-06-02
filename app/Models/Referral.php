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
        'expires_at',
        'referrer_discount_percentage',
        'referrer_discount_rides_count',
        'referrer_used_rides_count',
        'referrer_rewards_active',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'referrer_discount_percentage' => 'decimal:2',
        'referrer_rewards_active' => 'boolean',
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
     * Mark referral as accepted.
     *
     * Snapshots BOTH sides of the reward from the dashboard settings so that the
     * invited user (referred) and the inviter (referrer) each get their configured
     * discount on their upcoming rides.
     */
    public function markAsAccepted(User $referredUser): void
    {
        $this->update([
            'referred_user_id' => $referredUser->id,
            'accepted_at' => now(),
            // Invited user reward
            'discount_percentage' => AppSetting::getReferralDiscountPercentage(),
            'discount_rides_count' => AppSetting::getReferralDiscountRides(),
            'is_active' => true,
            // Inviter (referrer) reward
            'referrer_discount_percentage' => AppSetting::getReferrerRewardPercentage(),
            'referrer_discount_rides_count' => AppSetting::getReferrerRewardRides(),
            'referrer_rewards_active' => true,
        ]);
    }

    /**
     * Use one discount ride (invited user side)
     */
    public function useDiscountRide(): void
    {
        $this->increment('used_rides_count');
        
        if ($this->used_rides_count >= $this->discount_rides_count) {
            $this->update(['is_active' => false]);
        }
    }

    /**
     * Effective referrer reward percentage (falls back to live dashboard value
     * for legacy referrals accepted before this column was populated).
     */
    public function getEffectiveReferrerPercentage(): float
    {
        return $this->referrer_discount_percentage > 0
            ? (float) $this->referrer_discount_percentage
            : AppSetting::getReferrerRewardPercentage();
    }

    /**
     * Effective number of rides the referrer is rewarded on.
     */
    public function getEffectiveReferrerRidesCount(): int
    {
        return $this->referrer_discount_rides_count > 0
            ? (int) $this->referrer_discount_rides_count
            : AppSetting::getReferrerRewardRides();
    }

    /**
     * Check if the referrer (inviter) is still entitled to a reward discount.
     */
    public function isValidForReferrerReward(): bool
    {
        if (!$this->referrer_rewards_active) {
            return false;
        }

        if (!$this->referred_user_id) {
            return false;
        }

        return $this->referrer_used_rides_count < $this->getEffectiveReferrerRidesCount();
    }

    /**
     * Remaining reward rides for the referrer (inviter).
     */
    public function getReferrerRemainingRides(): int
    {
        return max(0, $this->getEffectiveReferrerRidesCount() - $this->referrer_used_rides_count);
    }

    /**
     * Consume one referrer (inviter) reward ride.
     */
    public function useReferrerRewardRide(): void
    {
        $this->increment('referrer_used_rides_count');

        if ($this->referrer_used_rides_count >= $this->getEffectiveReferrerRidesCount()) {
            $this->update(['referrer_rewards_active' => false]);
        }
    }

    /**
     * Human-friendly referral code for admin display (the referrer's real code).
     */
    public function getReferralCodeAttribute(): string
    {
        if ($this->referrer && !empty($this->referrer->referral_code)) {
            return $this->referrer->referral_code;
        }
        return 'REF' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }
}
