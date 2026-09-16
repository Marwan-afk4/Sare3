<?php

use App\Models\Delivery;
use App\Models\Ride;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// ─── Private Channel: driver.{driverId} ──────────────────────────────────────
// Only the driver themselves can subscribe (mobile app connection).
Broadcast::channel('driver.{userId}', function ($user, $userId) {
    if (! $user) {
        return false;
    }

    return (int) $user->id === (int) $userId
        && strtolower($user->role) === 'driver';
});

// ─── Private Channel: ride.{rideId} ──────────────────────────────────────
// Both the passenger and the assigned driver can subscribe to the ride updates.
Broadcast::channel('ride.{rideId}', function ($user, $rideId) {
    if (! $user) {
        return false;
    }

    $ride = Ride::find($rideId);
    if (! $ride) {
        return false;
    }

    return (int) $user->id === (int) $ride->user_id 
        || (int) $user->id === (int) $ride->driver_id;
});

// ─── Private Channel: user.{userId} ──────────────────────────────────────
// Only the specific user themselves can subscribe to their direct updates.
Broadcast::channel('user.{userId}', function ($user, $userId) {
    if (! $user) {
        return false;
    }

    return (int) $user->id === (int) $userId;
});

// ─── Private Channel: rider.{riderId} ─────────────────────────────────────
// Only the delivery captain themselves can subscribe (mobile app connection).
Broadcast::channel('rider.{userId}', function ($user, $userId) {
    if (! $user) {
        return false;
    }

    return (int) $user->id === (int) $userId
        && strtolower($user->role) === 'delivery';
});

// ─── Private Channel: delivery.{deliveryId} ───────────────────────────────
// Both the passenger and the assigned rider can subscribe to delivery updates.
Broadcast::channel('delivery.{deliveryId}', function ($user, $deliveryId) {
    if (! $user) {
        return false;
    }

    $delivery = Delivery::find($deliveryId);
    if (! $delivery) {
        return false;
    }

    return (int) $user->id === (int) $delivery->user_id
        || (int) $user->id === (int) $delivery->rider_id;
});

// ─── Presence Channel: chat.{roomId} ──────────────────────────────────────
// Both the passenger and the driver can subscribe to the chat channel.
// Room ID formats:
//   - user-driver (per ride): {userId}_{driverId}_{rideId}
//   - legacy user-driver:     {userId}_{driverId}
Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    if (! $user) {
        return false;
    }

    $parts = explode('_', $roomId);

    if (count($parts) === 3) {
        [$userId, $driverId, $rideId] = array_map('intval', $parts);

        $ride = Ride::find($rideId);
        if (! $ride) {
            return false;
        }

        if ((int) $user->id !== $userId && (int) $user->id !== $driverId) {
            return false;
        }

        if (! in_array((int) $user->id, [(int) $ride->user_id, (int) $ride->driver_id], true)) {
            return false;
        }

        return [
            'id' => $user->id,
            'name' => $user->getDisplayName(),
            'role' => $user->isDriver() ? 'driver' : 'user',
        ];
    }

    if (count($parts) === 2) {
        $userId = (int) $parts[0];
        $driverId = (int) $parts[1];

        if ((int) $user->id === $userId || (int) $user->id === $driverId) {
            return [
                'id' => $user->id,
                'name' => $user->getDisplayName(),
                'role' => $user->isDriver() ? 'driver' : 'user',
            ];
        }
    }

    return false;
});

// ─── Public Channel: driver-location ─────────────────────────────────────────
// Anyone (admin dashboard, passenger app) can subscribe to receive
// all driver location updates. No auth required — it is a public channel.
// Public channels do NOT need an authorization callback in Laravel.

