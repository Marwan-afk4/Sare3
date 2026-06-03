<?php

namespace App\Models;

use App\Enums\ActivtyType;
use App\Enums\DriverStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasFactory;
    use HasApiTokens, Notifiable, HasRoles;

    protected $table = 'users';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'otp_limit',
        'otp_used',
        'password',
        'remember_token',
        'image',
        'activity',
        'wallet',
        'wallet_limit',
        'role',
        'email_code',
        'email_verified',
        'id_token',
        'status',
        'rejected_reason',
        'gender',
        'fcm_token',
        'referrer_id',
        'is_referrer',
        'referral_code',
        'zone_id',
        'pending_coupon_id',
        'otp_code',
        'otp_expires_at',
        'is_available',
        'latitude',
        'longitude',
        'bearing',
        'phone_verified',
    ];

    protected $casts = [
        'activity' => ActivtyType::class,
        'status' => DriverStatus::class,
        'is_available' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'bearing' => 'float',
    ];

    public $timestamps = true;

    protected $appends = ['image_link'];

    /**
     * Ensure every account gets a unique, random referral code as soon as it is created.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->referral_code)) {
                $user->referral_code = static::generateUniqueReferralCode();
            }
        });
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    public function referralTokens()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function activeReferral()
    {
        return $this->hasOne(Referral::class, 'referred_user_id')
            ->where('is_active', true)
            ->whereColumn('used_rides_count', '<', 'discount_rides_count');
    }

    public function pendingCoupon()
    {
        return $this->belongsTo(Coupon::class, 'pending_coupon_id');
    }

    /**
     * Build a unique, random referral code that does not depend on the user's name.
     * Uses an unambiguous uppercase alphanumeric alphabet (no 0/O/1/I/L) so codes are
     * easy to read and share, and are always different per account.
     */
    public static function generateUniqueReferralCode(int $length = 8): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $max = strlen($alphabet) - 1;

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $alphabet[random_int(0, $max)];
            }
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Generate and persist a unique referral code for this user.
     */
    public function generateReferralCode(): string
    {
        $code = static::generateUniqueReferralCode();
        $this->update(['referral_code' => $code]);
        return $code;
    }

    /**
     * Get the user's referral code, generating one on demand for legacy accounts
     * that were created before codes were assigned at signup.
     */
    public function getReferralCode(): string
    {
        if (empty($this->referral_code)) {
            return $this->generateReferralCode();
        }

        return $this->referral_code;
    }

    public function getImageLinkAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function documents()
    {
        return $this->hasMany(DriverDocument::class, 'driver_id');
    }

    public function points()
    {
        return $this->hasMany(Point::class, 'user_id');
    }

    public function rideEstimate()
    {
        return $this->hasMany(RideEstimate::class);
    }

    public function driverCars()
    {
        return $this->hasMany(DriverCar::class, 'driver_id');
    }

    public function userRides()
    {
        return $this->hasMany(Ride::class, 'user_id');
    }

    public function driverRides()
    {
        return $this->hasMany(Ride::class, 'driver_id');
    }

    public function walletRequests()
    {
        return $this->hasMany(WalletRequest::class, 'driver_id');
    }

    public function userTransactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function driverTransactions()
    {
        return $this->hasMany(Transaction::class, 'driver_id');
    }

    public function driverRideSetting()
    {
        return $this->hasOne(DriverRideSetting::class, 'driver_id');
    }

    /**
     * Get the chat conversation ID for this user with admin
     * Format: admin_{user_id}
     */
    public function getChatConversationId(): string
    {
        return 'admin_' . $this->id;
    }

    /**
     * Get display name for chat interface
     * Returns the user's name if available, otherwise a formatted fallback
     */
    public function getDisplayName(): string
    {
        if (!empty($this->name)) {
            return $this->name;
        }

        // Fallback to formatted name based on role
        $roleLabel = $this->isDriver() ? 'Driver' : 'User';
        return $roleLabel . ' #' . $this->id;
    }

    /**
     * Check if this user is a driver
     */
    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }

    /**
     * Check if this user is a regular user (not driver or admin)
     */
    public function isUser(): bool
    {
        return $this->role === 'user' || $this->role === null;
    }

    /**
     * Check if this user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get the user type for chat purposes
     */
    public function getChatUserType(): string
    {
        return $this->isDriver() ? 'driver' : 'user';
    }

    /**
     * Get formatted display name with role indicator
     */
    public function getDisplayNameWithRole(): string
    {
        $name = $this->getDisplayName();
        $role = $this->isDriver() ? ' (Driver)' : ' (User)';

        return $name . $role;
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function bonusGrants()
    {
        return $this->hasMany(DriverBonusGrant::class, 'driver_id');
    }

    /**
     * Check if driver can go online based on wallet balance
     */
    public function canGoOnline(): bool
    {
        if (!$this->isDriver()) {
            return true; // Non-drivers are not affected by this rule
        }

        $minimumBalance = AppSetting::getMinimumDriverWalletBalance();
        return $this->wallet >= $minimumBalance;
    }

    /**
     * Get wallet status for driver
     */
    public function getWalletStatus(): array
    {
        $minimumBalance = AppSetting::getMinimumDriverWalletBalance();
        $currentBalance = $this->wallet ?? 0;
        $canGoOnline = $this->canGoOnline();

        return [
            'current_balance' => $currentBalance,
            'minimum_required_balance' => $minimumBalance,
            'can_go_online' => $canGoOnline,
            'balance_deficit' => $canGoOnline ? 0 : ($minimumBalance - $currentBalance)
        ];
    }
    public function ratingsReceived()
    {
        return $this->hasMany(Rating::class, 'ratee_id');
    }

    public function getAverageRatingAttribute()
    {
        return round($this->ratingsReceived()->avg('rate') ?: 5.0, 1);
    }
}
