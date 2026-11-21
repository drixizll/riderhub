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

        $days = collect(range(6, 0))->map(fn (int $offset) => now()->subDays($offset)->startOfDay());
        $weekBookings = $user->bookings()
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['created_at', 'type', 'fare']);

        $activityChart = [
            'labels' => $days->map(fn ($day) => $day->format('M j'))->values()->all(),
            'rides' => $days->map(fn ($day) => $weekBookings->filter(fn ($b) => $b->created_at->isSameDay($day) && $b->type === 'ride')->count())->values()->all(),
            'deliveries' => $days->map(fn ($day) => $weekBookings->filter(fn ($b) => $b->created_at->isSameDay($day) && $b->type === 'delivery')->count())->values()->all(),
        ];

        return view('customer.dashboard', [
            'totalBookings' => (clone $bookings)->count(),
            'activeBookings' => (clone $bookings)->whereIn('status', ['accepted', 'picked_up'])->count(),
            'completedBookings' => (clone $bookings)->where('status', 'completed')->count(),
            'bookingToRate' => $bookingToRate,
            'bookings' => (clone $bookings)->with('payment')->latest()->limit(5)->get(),
            'activityChart' => $activityChart,
        ]);
    }
}
