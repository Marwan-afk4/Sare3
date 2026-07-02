<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserDriverChatMessageSeen implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $chatId;
    public string $roomId;
    public int $readerId;
    public string $readerType;

    /**
     * Create a new event instance.
     */
    public function __construct(int $chatId, string $roomId, int $readerId, string $readerType)
    {
        $this->chatId = $chatId;
        $this->roomId = $roomId;
        $this->readerId = $readerId;
        $this->readerType = $readerType;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('chat.' . $this->roomId),
        ];
    }

    /**
     * Payload sent over the wire.
     */
    public function broadcastWith(): array
    {
        return [
            'chat_id' => $this->chatId,
            'room_id' => $this->roomId,
            'reader_id' => $this->readerId,
            'reader_type' => $this->readerType,
        ];
    }

    /**
     * Event name used on the client side.
     */
    public function broadcastAs(): string
    {
        return 'messages.seen';
    }
}
