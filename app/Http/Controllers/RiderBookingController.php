<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\RiderHubNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RiderBookingController extends Controller
{
    public function index(): View
    {
        return view('rider.bookings.index', $this->viewData(true));
    }

    public function dashboard(): View
    {
        return view('rider.dashboard', $this->viewData(false));
    }

    public function pollRequests(): JsonResponse
    {
        $query = Booking::query()
            ->with('customer:id,name')
            ->whereNull('rider_id')
            ->where('status', 'pending')
            ->where(function ($q): void {
                $q->where('payment_method', 'cash')
                    ->orWhereHas('payment', fn ($p) => $p->where('status', 'paid'));
            });

        $count = (clone $query)->count();
        $latest = (clone $query)->latest('id')->first();

        return response()->json([
            'count' => $count,
            'latest_id' => $latest?->id ?? 0,
            'latest' => $latest ? [
                'id' => $latest->id,
                'type' => ucfirst($latest->type),
                'pickup_address' => $latest->pickup_address,
                'dropoff_address' => $latest->dropoff_address,
                'fare' => '₱'.number_format((float) $latest->fare, 2),
                'customer' => $latest->customer?->name ?? 'Customer',
            ] : null,
        ]);
    }

    private function viewData(bool $paginate): array
    {
        $rider = Auth::user();
        $availableBookings = Booking::with('customer')
            ->whereNull('rider_id')
            ->where('status', 'pending')
            ->where(function ($query): void {
                $query->where('payment_method', 'cash')
                    ->orWhereHas('payment', fn ($paymentQuery) => $paymentQuery->where('status', 'paid'));
            })
            ->latest();

        $ratingAverage = $rider->ratingsReceived()->avg('score');
        $days = collect(range(6, 0))->map(fn (int $offset) => now()->subDays($offset)->startOfDay());
        $weekCompleted = $rider->riderBookings()
            ->where('status', 'completed')
            ->where('updated_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['updated_at', 'fare']);

        $earningsChart = [
            'labels' => $days->map(fn ($day) => $day->format('M j'))->values()->all(),
            'trips' => $days->map(fn ($day) => $weekCompleted->filter(fn ($b) => $b->updated_at->isSameDay($day))->count())->values()->all(),
            'earnings' => $days->map(fn ($day) => round((float) $weekCompleted->filter(fn ($b) => $b->updated_at->isSameDay($day))->sum('fare'), 2))->values()->all(),
        ];

        $todayEarnings = (float) $rider->riderBookings()
            ->where('status', 'completed')
            ->whereDate('updated_at', today())
            ->sum('fare');
        $totalEarnings = (float) $rider->riderBookings()
            ->where('status', 'completed')
            ->sum('fare');

        return [
            'activeBooking' => $rider->riderBookings()
                ->with(['customer', 'payment'])
                ->whereIn('status', ['accepted', 'picked_up'])
                ->latest()
                ->first(),
            'completedCount' => $rider->riderBookings()->where('status', 'completed')->count(),
            'assignedCount' => $rider->riderBookings()->count(),
            'ratingAverage' => $ratingAverage ? number_format($ratingAverage, 1) : null,
            'ratingCount' => $rider->ratingsReceived()->count(),
            'availableCount' => (clone $availableBookings)->count(),
            'bookings' => $paginate
                ? $availableBookings->paginate(10)
                : $availableBookings->limit(5)->get(),
            'earningsChart' => $earningsChart,
            'todayEarnings' => $todayEarnings,
            'totalEarnings' => $totalEarnings,
        ];
    }

    public function accept(Booking $booking): RedirectResponse
    {
        $alreadyTaken = false;

        DB::transaction(function () use ($booking, &$alreadyTaken): void {
            $lockedRider = User::query()->lockForUpdate()->findOrFail(Auth::id());
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($lockedRider->hasActiveBooking()) {
                abort(403, 'Complete your active booking before accepting another.');
            }

            // First-accept ("paunahan") atomic race guard: if another rider accepted a split-second earlier, do not overwrite
            if ($lockedBooking->status !== 'pending' || $lockedBooking->rider_id !== null) {
                $alreadyTaken = true;

                return;
            }

            $this->authorize('accept', $lockedBooking);

            $lockedBooking->update([
                'rider_id' => $lockedRider->id,
                'status' => 'accepted',
            ]);
        });

        if ($alreadyTaken) {
            return redirect()->route('rider.dashboard')
                ->with('error', 'Naunahan ka na! This booking was just accepted by another rider.');
        }

        $booking->refresh()->customer->notify(new RiderHubNotification(
            'Rider matched',
            'A rider accepted your booking request and is preparing for pickup.',
            route('customer.bookings.show', $booking, absolute: false),
            'booking',
        ));

        return redirect()->route('rider.dashboard')->with('success', 'Booking accepted. Contact the customer and head to pickup.');
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['picked_up', 'completed'])],
        ]);

        DB::transaction(function () use ($booking, $validated): void {
            $lockedBooking = Booking::query()->lockForUpdate()->with('payment')->findOrFail($booking->id);

            $this->authorize('updateStatus', $lockedBooking);

            $allowedNextStatus = match ($lockedBooking->status) {
                'accepted' => 'picked_up',
                'picked_up' => 'completed',
                default => null,
            };

            if ($validated['status'] !== $allowedNextStatus) {
                abort(422, 'This booking cannot move to the selected status.');
            }

            $lockedBooking->update(['status' => $validated['status']]);

            if ($validated['status'] === 'completed' && $lockedBooking->payment?->method === 'cash') {
                $lockedBooking->payment->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }
        });

        if ($validated['status'] === 'completed') {
            $booking->refresh()->customer->notify(new RiderHubNotification(
                'Trip completed',
                'Your '.($booking->type === 'delivery' ? 'delivery' : 'ride').' is complete. You can now rate your rider.',
                route('customer.bookings.show', $booking, absolute: false),
                'booking',
            ));
        }

        return redirect()->route('rider.dashboard')->with('success', 'Booking status updated.');
    }
}
