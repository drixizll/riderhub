<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->customer_id
            || $user->id === $booking->rider_id
            || $user->isAdmin();
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $user->id === $booking->customer_id
            && $booking->status === 'pending'
            && ! ($booking->payment_method === 'online' && $booking->payment?->status === 'paid');
    }

    public function accept(User $user, Booking $booking): bool
    {
        return $user->role === 'rider'
            && $booking->status === 'pending'
            && $booking->rider_id === null
            && ($booking->payment_method === 'cash' || $booking->payment?->status === 'paid')
            && ! $user->hasActiveBooking();
    }

    public function updateStatus(User $user, Booking $booking): bool
    {
        return $booking->rider_id === $user->id && $user->role === 'rider';
    }

    public function viewLocation(User $user, Booking $booking): bool
    {
        return $user->id === $booking->customer_id
            || $user->id === $booking->rider_id
            || $user->isAdmin();
    }
}
