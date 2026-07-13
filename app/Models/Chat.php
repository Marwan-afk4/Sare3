<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    /** Ride statuses that allow sending new messages. */
    public const ACTIVE_RIDE_STATUSES = [
        'accepted',
        'waiting_user',
        'arrived',
        'in_progress',
    ];

    protected $fillable = [
        'room_id',
        'ride_id',
        'participant_1_id',
        'participant_1_type',
        'participant_2_id',
        'participant_2_type',
        'last_message',
        'last_message_at',
        'is_active',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function participant1()
    {
        return $this->morphTo('participant_1', 'participant_1_type', 'participant_1_id');
    }

    public function participant2()
    {
        return $this->morphTo('participant_2', 'participant_2_type', 'participant_2_id');
    }

    public static function createRoomId(string $type1, int $id1, string $type2, int $id2, ?int $rideId = null): string
    {
        if ($type1 === 'admin' || $type2 === 'admin') {
            $nonAdminId = $type1 === 'admin' ? $id2 : $id1;
            return "admin_{$nonAdminId}";
        }

        // User-driver chats are scoped to a specific ride.
        if ($rideId !== null) {
            $userId = $type1 === 'user' ? $id1 : $id2;
            $driverId = $type1 === 'driver' ? $id1 : $id2;

            return "{$userId}_{$driverId}_{$rideId}";
        }

        return "{$id1}_{$id2}";
    }

    public static function findOrCreateForRide(Ride $ride): self
    {
        if (!$ride->user_id || !$ride->driver_id) {
            throw new \InvalidArgumentException('Ride must have both a user and a driver assigned.');
        }

        $roomId = self::createRoomId('user', $ride->user_id, 'driver', $ride->driver_id, $ride->id);

        return self::firstOrCreate(
            ['room_id' => $roomId],
            [
                'ride_id' => $ride->id,
                'participant_1_id' => $ride->user_id,
                'participant_1_type' => 'user',
                'participant_2_id' => $ride->driver_id,
                'participant_2_type' => 'driver',
                'is_active' => true,
            ]
        );
    }

    /**
     * @deprecated Use findOrCreateForRide() for user-driver chats.
     */
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
                'is_active' => true,
            ]
        );
    }

    public static function findForRide(int $rideId, int $userId, int $driverId): ?self
    {
        $roomId = self::createRoomId('user', $userId, 'driver', $driverId, $rideId);

        return self::where('room_id', $roomId)->first();
    }

    public static function closeForRide(int $rideId): void
    {
        self::where('ride_id', $rideId)->update(['is_active' => false]);
    }

    public function allowsSending(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->ride_id) {
            return true;
        }

        $ride = $this->relationLoaded('ride') ? $this->ride : Ride::find($this->ride_id);

        if (!$ride) {
            return false;
        }

        return in_array($ride->status->value, self::ACTIVE_RIDE_STATUSES, true);
    }
}
