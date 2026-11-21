<?php

return [
    'fare' => [
        'base' => 55.00,
        'per_km' => 18.50,
    ],
    'map' => [
        'region_name' => 'Tagoloan, Misamis Oriental',
        'center' => ['lat' => 8.5378, 'lng' => 124.7536],
        'default_pickup' => ['lat' => 8.5378, 'lng' => 124.7536],
        'default_dropoff' => ['lat' => 8.5515, 'lng' => 124.7615],
        'bounds' => [
            'south' => 8.46,
            'west' => 124.68,
            'north' => 8.63,
            'east' => 124.85,
        ],
        'default_zoom' => 14,
        'min_zoom' => 12,
    ],
    'booking_statuses' => ['pending', 'accepted', 'picked_up', 'completed', 'cancelled'],
    'payment_statuses' => ['pending', 'paid', 'failed', 'cancelled'],
    'payment_methods' => ['cash', 'online'],
    'active_booking_statuses' => ['accepted', 'picked_up'],
];
