<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\RiderHubNotification;

class BookingNotificationService
{
    public function notifyAvailableRiders(Booking $booking): void
    {
        $booking->loadMissing('payment');

        if (
            $booking->status !== 'pending'
            || ($booking->payment_method === 'online' && $booking->payment?->status !== 'paid')
        ) {
            return;
        }

        User::query()
            ->whereIn('role', ['rider', 'admin'])
            ->get()
            ->each(fn (User $user) => $user->notify(new RiderHubNotification(
                'New booking request',
                'A customer has requested a new '.($booking->type === 'delivery' ? 'delivery' : 'ride').'.',
                $user->role === 'rider'
                    ? route('rider.bookings.index', absolute: false)
                    : route('admin.bookings.index', absolute: false),
                'booking',
            )));
    }

    public function notifyCustomerPaymentReceived(Booking $booking): void
    {
        $booking->customer->notify(new RiderHubNotification(
            'Payment received',
            'Your online payment is confirmed. RiderHub is now looking for an available rider.',
            route('customer.bookings.show', $booking, absolute: false),
            'payment',
        ));
    }
}
