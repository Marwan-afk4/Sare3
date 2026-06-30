<?php

return [
    'database_url' => env('FIREBASE_DATABASE_URL'),
    'secret' => env('FIREBASE_SECRET'),
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'service_account' => env('FIREBASE_SERVICE_ACCOUNT_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Phone Auth (optional SMS verification via Firebase)
    |--------------------------------------------------------------------------
    */
    'phone_auth' => [
        'enabled' => env('FIREBASE_PHONE_AUTH_ENABLED', true),
    ],
];