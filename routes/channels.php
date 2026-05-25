<?php

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

// ─── Public Channel: driver-location ─────────────────────────────────────────
// Anyone (admin dashboard, passenger app) can subscribe to receive
// all driver location updates. No auth required — it is a public channel.
// Public channels do NOT need an authorization callback in Laravel.
