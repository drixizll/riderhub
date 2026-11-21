<?php

return [
    'fare' => [
        'base' => 55.00,
        'per_km' => 18.50,
    ],
    'booking_statuses' => ['pending', 'accepted', 'picked_up', 'completed', 'cancelled'],
    'payment_statuses' => ['pending', 'paid', 'failed', 'cancelled'],
    'payment_methods' => ['cash', 'online'],
    'active_booking_statuses' => ['accepted', 'picked_up'],
];
