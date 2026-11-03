<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushNotificationLog extends Model
{
    protected $fillable = [
        'recipient_id',
        'recipient_role',
        'ride_id',
        'delivery_id',
        'msg_type',
        'title',
        'body',
        'status',
        'has_token',
        'token_prefix',
        'http_status',
        'fcm_message_id',
        'error_code',
        'error_message',
        'high_priority',
        'duration_ms',
    ];

    protected $casts = [
        'has_token' => 'boolean',
        'high_priority' => 'boolean',
    ];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function ride(): BelongsTo
    {
        return $this->belongsTo(Ride::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }
}
