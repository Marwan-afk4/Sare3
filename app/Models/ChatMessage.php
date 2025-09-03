<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'sender_id',
        'sender_type',
        'receiver_id',
        'receiver_type',
        'message',
        'status',
        'firebase_message_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        return $this->morphTo('sender', 'sender_type', 'sender_id');
    }

    public function receiver()
    {
        return $this->morphTo('receiver', 'receiver_type', 'receiver_id');
    }
}