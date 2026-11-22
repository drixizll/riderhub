<?php

return [
    'fare' => [
        'base' => 55.00,
        'per_km' => 18.50,
    ],
    'map' => [
        'region_name' => 'Misamis Oriental',
        'center' => ['lat' => 8.5150, 'lng' => 124.7150],
        'default_pickup' => ['lat' => 8.5378, 'lng' => 124.7536],
        'default_dropoff' => ['lat' => 8.4810, 'lng' => 124.6498],
        'bounds' => [
            'south' => 8.20,
            'west' => 124.15,
            'north' => 9.15,
            'east' => 125.35,
        ],
        'default_zoom' => 12,
        'min_zoom' => 9,
    ],
    'booking_statuses' => ['pending', 'accepted', 'picked_up', 'completed', 'cancelled'],
    'payment_statuses' => ['pending', 'paid', 'failed', 'cancelled'],
    'payment_methods' => ['cash', 'online'],
    'active_booking_statuses' => ['accepted', 'picked_up'],
    'promos' => [
        'TAGOLOAN20' => ['type' => 'fixed', 'value' => 20.00, 'label' => '₱20 off Tagoloan ride'],
        'FIRSTTRIP' => ['type' => 'fixed', 'value' => 25.00, 'label' => '₱25 off welcome promo'],
        'MISOR15' => ['type' => 'percent', 'value' => 15, 'max' => 40.00, 'label' => '15% off (up to ₱40)'],
    ],
    'cancellation_reasons' => [
        'Wrong pickup or drop-off location',
        'Changed my mind',
        'Booked by mistake',
        'Waiting time too long',
        'Found another ride',
        'Other',
    ],
];
