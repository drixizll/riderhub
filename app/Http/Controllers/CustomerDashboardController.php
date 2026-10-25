<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $bookings = $user->bookings();
        $bookingToRate = Booking::query()
            ->where('customer_id', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('rider_id')
            ->whereDoesntHave('rating')
            ->latest()
            ->first();

        return view('customer.dashboard', [
            'totalBookings' => (clone $bookings)->count(),
            'activeBookings' => (clone $bookings)->whereIn('status', ['accepted', 'picked_up'])->count(),
            'completedBookings' => (clone $bookings)->where('status', 'completed')->count(),
            'bookingToRate' => $bookingToRate,
            'bookings' => (clone $bookings)->with('payment')->latest()->limit(5)->get(),
        ]);
    }
}
