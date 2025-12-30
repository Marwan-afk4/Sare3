<?php

namespace App\Models;

use App\Enums\DriverStatus;
use App\Enums\WalletRequestType;
use App\Observers\WalletRequestObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// #[ObservedBy(WalletRequestObserver::class)]
class WalletRequest extends Model
{
    use HasFactory;

    protected $table = 'wallet_requests';

    protected $fillable = [
        'driver_id',
        'amount',
        'type',
        'status',
        'note'
    ];

    protected $casts = [
        'status' => DriverStatus::class,
        'type' => WalletRequestType::class,
    ];

    public $timestamps = true;


    public function driver()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(WalletRequestsMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * Append timezone-formatted timestamps to JSON
     */
    protected $appends = ['created_at_formatted', 'updated_at_formatted'];

    /**
     * Get formatted created_at in driver's timezone
     */
    public function getCreatedAtFormattedAttribute()
    {
        if (!$this->created_at) {
            return null;
        }

        if ($this->driver) {
            $timezone = $this->driver->getTimezone();
            return $this->created_at->timezone($timezone)->format('Y-m-d H:i:s');
        }

        return $this->created_at->format('Y-m-d H:i:s');
    }

    /**
     * Get formatted updated_at in driver's timezone
     */
    public function getUpdatedAtFormattedAttribute()
    {
        if (!$this->updated_at) {
            return null;
        }

        if ($this->driver) {
            $timezone = $this->driver->getTimezone();
            return $this->updated_at->timezone($timezone)->format('Y-m-d H:i:s');
        }

        return $this->updated_at->format('Y-m-d H:i:s');
    }

}
