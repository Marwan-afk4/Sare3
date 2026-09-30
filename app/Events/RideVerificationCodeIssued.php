<?php

namespace App\Events;

use App\Models\Ride;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Passenger-only. The driver channels must not receive the code.
 */
class RideVerificationCodeIssued implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Ride $ride)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->ride->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'verification.code.issued';
    }

    public function broadcastWith(): array
    {
        return [
            'ride_id' => $this->ride->id,
            'verification_code' => $this->ride->verification_code,
        ];
    }
}
