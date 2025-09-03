<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'participant_1_id',
        'participant_1_type',
        'participant_2_id', 
        'participant_2_type',
        'last_message',
        'last_message_at',
        'is_active'
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function participant1()
    {
        return $this->morphTo('participant_1', 'participant_1_type', 'participant_1_id');
    }

    public function participant2()
    {
        return $this->morphTo('participant_2', 'participant_2_type', 'participant_2_id');
    }

    public static function createRoomId($type1, $id1, $type2, $id2)
    {
        if ($type1 === 'admin' || $type2 === 'admin') {
            $nonAdminType = $type1 === 'admin' ? $type2 : $type1;
            $nonAdminId = $type1 === 'admin' ? $id2 : $id1;
            return "admin_{$nonAdminId}";
        }
        
        // For user-driver chats
        return "{$id1}_{$id2}";
    }

    public static function findOrCreateChat($participant1Type, $participant1Id, $participant2Type, $participant2Id)
    {
        $roomId = self::createRoomId($participant1Type, $participant1Id, $participant2Type, $participant2Id);
        
        return self::firstOrCreate(
            ['room_id' => $roomId],
            [
                'participant_1_id' => $participant1Id,
                'participant_1_type' => $participant1Type,
                'participant_2_id' => $participant2Id,
                'participant_2_type' => $participant2Type,
                'is_active' => true
            ]
        );
    }
}