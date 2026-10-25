<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalBookings' => Booking::count(),
            'activeBookings' => Booking::whereIn('status', ['accepted', 'picked_up'])->count(),
            'pendingBookings' => Booking::where('status', 'pending')
                ->where(function ($query): void {
                    $query->where('payment_method', 'cash')
                        ->orWhereHas('payment', fn ($paymentQuery) => $paymentQuery->where('status', 'paid'));
                })
                ->count(),
            'completedBookings' => Booking::where('status', 'completed')->count(),
            'totalCustomers' => User::where('role', 'customer')->count(),
            'totalRiders' => User::where('role', 'rider')->count(),
            'completedRevenue' => Booking::where('status', 'completed')->sum('fare'),
            'recentBookings' => Booking::with(['customer', 'rider', 'payment'])->latest()->limit(8)->get(),
        ]);
    }

    public function bookings(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,accepted,picked_up,completed,cancelled'],
            'type' => ['nullable', 'in:ride,delivery'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $bookings = Booking::with(['customer', 'rider', 'payment'])
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($validated['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($validated['search'] ?? null, function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('pickup_address', 'like', "%{$search}%")
                        ->orWhere('dropoff_address', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function users(Request $request): View
    {
        $validated = $request->validate([
            'role' => ['nullable', 'in:customer,rider,admin'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $users = User::query()
            ->when($validated['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->when($validated['search'] ?? null, function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }
}
