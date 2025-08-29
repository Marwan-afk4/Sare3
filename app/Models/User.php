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


class User extends Authenticatable
{
    use HasFactory;
    use HasApiTokens, Notifiable;

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
        'zone_id'
    ];

    protected $casts = [
        'activity' => ActivtyType::class,
        'status' => DriverStatus::class,
    ];

    public $timestamps = true;

    protected $appends =['image_link'];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referrer_id');
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
        return $this->hasMany(DriverCar::class,'driver_id');
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

}
