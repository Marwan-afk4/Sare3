<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideOffer extends Model
{
    public const RESPONSE_PENDING = 'pending';
    public const RESPONSE_ACCEPTED = 'accepted';
    public const RESPONSE_REJECTED = 'rejected';
    public const RESPONSE_IGNORED = 'ignored';
    public const RESPONSE_CANCELLED_AFTER_ACCEPT = 'cancelled_after_accept';
    public const RESPONSE_CANCELLED_BY_USER = 'cancelled_by_user';

    protected $table = 'ride_offers';

    protected $fillable = [
        'ride_id',
        'driver_id',
        'offered_at',
        'responded_at',
        'response',
        'response_seconds',
        'attempt',
        'note',
        'driver_lat',
        'driver_lng',
    ];

    protected $casts = [
        'offered_at' => 'datetime',
        'responded_at' => 'datetime',
        'attempt' => 'integer',
        'response_seconds' => 'integer',
        'driver_lat' => 'float',
        'driver_lng' => 'float',
    ];

    public function hasDriverLocation(): bool
    {
        return $this->driver_lat !== null && $this->driver_lng !== null;
    }

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Human-readable Arabic/English label + bootstrap color for the response.
     * Used by the admin dashboard offer history table.
     */
    public static function responseLabels(): array
    {
        return [
            self::RESPONSE_PENDING => ['label' => __('Pending'), 'color' => 'warning'],
            self::RESPONSE_ACCEPTED => ['label' => __('Accepted'), 'color' => 'success'],
            self::RESPONSE_REJECTED => ['label' => __('Rejected'), 'color' => 'danger'],
            self::RESPONSE_IGNORED => ['label' => __('Ignored'), 'color' => 'secondary'],
            self::RESPONSE_CANCELLED_AFTER_ACCEPT => ['label' => __('Cancelled after accept'), 'color' => 'danger'],
            self::RESPONSE_CANCELLED_BY_USER => ['label' => __('Cancelled by user'), 'color' => 'dark'],
        ];
    }

    public function responseLabel(): string
    {
        return self::responseLabels()[$this->response]['label'] ?? $this->response;
    }

    public function responseColor(): string
    {
        return self::responseLabels()[$this->response]['color'] ?? 'secondary';
    }

    public function responseBadgeHtml(): string
    {
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            e($this->responseColor()),
            e($this->responseLabel())
        );
    }
}
