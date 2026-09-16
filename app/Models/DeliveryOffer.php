<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOffer extends Model
{
    public const RESPONSE_PENDING = 'pending';
    public const RESPONSE_ACCEPTED = 'accepted';
    public const RESPONSE_REJECTED = 'rejected';
    public const RESPONSE_IGNORED = 'ignored';
    public const RESPONSE_CANCELLED_AFTER_ACCEPT = 'cancelled_after_accept';
    public const RESPONSE_CANCELLED_BY_USER = 'cancelled_by_user';

    protected $table = 'delivery_offers';

    protected $fillable = [
        'delivery_id',
        'rider_id',
        'offered_at',
        'responded_at',
        'response',
        'response_seconds',
        'attempt',
        'note',
        'rider_lat',
        'rider_lng',
    ];

    protected $casts = [
        'offered_at' => 'datetime',
        'responded_at' => 'datetime',
        'attempt' => 'integer',
        'response_seconds' => 'integer',
        'rider_lat' => 'float',
        'rider_lng' => 'float',
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

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
}
