<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('driver.{userId}', function ($user, $userId) {
    if (!$user) {
        return false; // Deny if no authenticated user
    }

    return (int) $user->id === (int) $userId && strtolower($user->role) === 'driver';
});
