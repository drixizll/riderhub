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
        $days = collect(range(6, 0))->map(fn (int $offset) => now()->subDays($offset)->startOfDay());
        $recentWeekBookings = Booking::query()
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['created_at', 'status', 'fare', 'type']);

        $dailyChart = [
            'labels' => $days->map(fn ($day) => $day->format('M j'))->values()->all(),
            'bookings' => $days->map(fn ($day) => $recentWeekBookings->filter(
                fn ($b) => $b->created_at->isSameDay($day)
            )->count())->values()->all(),
            'revenue' => $days->map(fn ($day) => round((float) $recentWeekBookings->filter(
                fn ($b) => $b->created_at->isSameDay($day) && $b->status === 'completed'
            )->sum('fare'), 2))->values()->all(),
        ];

        $statusCounts = Booking::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusChart = [
            'labels' => ['Pending', 'Accepted', 'Picked up', 'Completed', 'Cancelled'],
            'keys' => ['pending', 'accepted', 'picked_up', 'completed', 'cancelled'],
            'counts' => [
                (int) ($statusCounts['pending'] ?? 0),
                (int) ($statusCounts['accepted'] ?? 0),
                (int) ($statusCounts['picked_up'] ?? 0),
                (int) ($statusCounts['completed'] ?? 0),
                (int) ($statusCounts['cancelled'] ?? 0),
            ],
        ];

        $topRiders = User::where('role', 'rider')
            ->withCount(['riderBookings as completed_trips_count' => fn ($q) => $q->where('status', 'completed')])
            ->withSum(['riderBookings as completed_fare_sum' => fn ($q) => $q->where('status', 'completed')], 'fare')
            ->withAvg('ratingsReceived as rating_avg', 'score')
            ->orderByDesc('completed_trips_count')
            ->limit(5)
            ->get();

        $availableRiders = User::where('role', 'rider')
            ->whereDoesntHave('riderBookings', fn ($q) => $q->whereIn('status', ['accepted', 'picked_up']))
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

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
            'dailyChart' => $dailyChart,
            'statusChart' => $statusChart,
            'topRiders' => $topRiders,
            'availableRiders' => $availableRiders,
        ]);
    }

    public function assignRider(Request $request, Booking $booking): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'rider_id' => ['required', 'exists:users,id'],
        ]);

        $assigned = false;
        $riderName = '';

        \Illuminate\Support\Facades\DB::transaction(function () use ($booking, $validated, &$assigned, &$riderName): void {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            $lockedRider = User::query()->where('role', 'rider')->lockForUpdate()->findOrFail($validated['rider_id']);

            if ($lockedBooking->status !== 'pending' || $lockedBooking->rider_id !== null || $lockedRider->hasActiveBooking()) {
                return;
            }

            $lockedBooking->update([
                'rider_id' => $lockedRider->id,
                'status' => 'accepted',
            ]);
            $assigned = true;
            $riderName = $lockedRider->name;
        });

        if (! $assigned) {
            return back()->with('error', 'Could not assign rider — the booking was already taken or the rider is currently on an active trip.');
        }

        return back()->with('success', "Booking #{$booking->id} has been dispatched to {$riderName}.");
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

    public function updateUser(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot edit your own administrator account from the directory.');
        }

        if ($request->filled('email')) {
            $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique(User::class)->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:25'],
            'role' => ['required', 'in:customer,rider,admin'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "Account for {$user->name} has been updated.");
    }

    public function destroyUser(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own administrator account here.');
        }

        $hasActiveBooking = Booking::query()
            ->where(fn ($q) => $q->where('customer_id', $user->id)->orWhere('rider_id', $user->id))
            ->whereIn('status', ['accepted', 'picked_up'])
            ->exists();

        if ($hasActiveBooking) {
            return redirect()->route('admin.users.index')
                ->with('error', "Cannot delete {$user->name} while they have an active trip.");
        }

        $name = $user->name;
        if ($user->profile_photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile_photo_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo_path);
        }
        $user->notifications()->delete();
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Account for {$name} has been deleted.");
    }
}
