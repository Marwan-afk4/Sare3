<?php

/*
| Prices are Google Maps Platform global list rates (USD per 1,000 billable
| events), after the monthly free cap. Free caps reset at midnight Pacific Time
| on the first of each month. Legacy Distance Matrix and Directions only publish
| two paid tiers. Roads "Route Traveled" is the Snap to Roads Pro SKU.
|
| https://developers.google.com/maps/billing-and-pricing/pricing
*/

return [
    'timezone' => 'America/Los_Angeles',

    'skus' => [
        'distance_matrix' => [
            'name' => 'Distance Matrix',
            'sku_id' => 'C1B6-FF9D-7700',
            'category' => 'Essentials',
            'unit' => 'elements',
            'free_cap' => 10000,
            'client' => false,
            'tiers' => [
                ['up_to' => 100000, 'per_thousand' => 5.00],
                ['up_to' => null, 'per_thousand' => 4.00],
            ],
            'used_for' => 'Fallback fare distance when the app did not send km and minutes, and one ETA rank of the 3 closest drivers per search. One element is one origin paired with one destination.',
        ],
        'roads_snap' => [
            'name' => 'Roads — Route Traveled (Snap to Roads)',
            'sku_id' => '9806-C277-BCCA',
            'category' => 'Pro',
            'unit' => 'requests',
            'free_cap' => 5000,
            'client' => false,
            'tiers' => [
                ['up_to' => 100000, 'per_thousand' => 10.00],
                ['up_to' => 500000, 'per_thousand' => 8.00],
                ['up_to' => 1000000, 'per_thousand' => 6.00],
                ['up_to' => 5000000, 'per_thousand' => 3.00],
                ['up_to' => null, 'per_thousand' => 0.76],
            ],
            'used_for' => 'Snapping a finished ride path onto roads. Each batch of up to 90 GPS points is one request.',
        ],
        'dynamic_maps' => [
            'name' => 'Dynamic Maps',
            'sku_id' => 'FAF4-3B2D-51B2',
            'category' => 'Essentials',
            'unit' => 'map loads',
            'free_cap' => 10000,
            'client' => true,
            'tiers' => [
                ['up_to' => 100000, 'per_thousand' => 7.00],
                ['up_to' => 500000, 'per_thousand' => 5.60],
                ['up_to' => 1000000, 'per_thousand' => 4.20],
                ['up_to' => 5000000, 'per_thousand' => 2.10],
                ['up_to' => null, 'per_thousand' => 0.53],
            ],
            'used_for' => 'Admin pages that load the Maps JavaScript API (rides, zones, deliveries).',
        ],
        'directions' => [
            'name' => 'Directions',
            'sku_id' => '28A8-3EB4-4595',
            'category' => 'Essentials',
            'unit' => 'requests',
            'free_cap' => 10000,
            'client' => true,
            'tiers' => [
                ['up_to' => 100000, 'per_thousand' => 5.00],
                ['up_to' => null, 'per_thousand' => 4.00],
            ],
            'used_for' => 'Drawing a driving route on the admin ride map.',
        ],
        'geocoding' => [
            'name' => 'Geocoding',
            'sku_id' => 'BAC8-4E68-E261',
            'category' => 'Essentials',
            'unit' => 'requests',
            'free_cap' => 10000,
            'client' => true,
            'tiers' => [
                ['up_to' => 100000, 'per_thousand' => 5.00],
                ['up_to' => 500000, 'per_thousand' => 4.00],
                ['up_to' => 1000000, 'per_thousand' => 3.00],
                ['up_to' => 5000000, 'per_thousand' => 1.50],
                ['up_to' => null, 'per_thousand' => 0.38],
            ],
            'used_for' => 'Turning a map click into an address on the admin location picker.',
        ],
        'places_autocomplete' => [
            'name' => 'Places Autocomplete',
            'sku_id' => '4EF4-B17C-B31A',
            'category' => 'Essentials',
            'unit' => 'selections',
            'free_cap' => 10000,
            'client' => true,
            'tiers' => [
                ['up_to' => 100000, 'per_thousand' => 2.83],
                ['up_to' => 500000, 'per_thousand' => 2.27],
                ['up_to' => 1000000, 'per_thousand' => 1.70],
                ['up_to' => 5000000, 'per_thousand' => 0.85],
                ['up_to' => null, 'per_thousand' => 0.21],
            ],
            'used_for' => 'Address search on admin maps. Counted once when a suggestion is chosen. Google may bill a typing session instead of a single selection.',
        ],
        'fcm' => [
            'name' => 'Firebase Cloud Messaging',
            'sku_id' => null,
            'category' => 'Firebase',
            'unit' => 'messages',
            'free_cap' => null,
            'unlimited' => true,
            'client' => false,
            'tiers' => [],
            'used_for' => 'Push notifications to riders and drivers. FCM has no per-message charge.',
        ],
    ],
];
