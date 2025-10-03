<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Auto Reject Timeout
    |--------------------------------------------------------------------------
    |
    | The number of seconds to wait before automatically rejecting a ride
    | if the driver doesn't respond (accept or reject).
    |
    */
    'auto_reject_timeout_seconds' => env('RIDE_AUTO_REJECT_TIMEOUT', 15),

    /*
    |--------------------------------------------------------------------------
    | Max Driver Rejections
    |--------------------------------------------------------------------------
    |
    | Maximum number of drivers that can reject a ride before it's marked
    | as failed to find a driver.
    |
    */
    'max_driver_rejections' => env('RIDE_MAX_DRIVER_REJECTIONS', 10),
];
