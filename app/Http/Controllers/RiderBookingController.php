<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\RiderHubNotification;
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
        ];
    }

    public function accept(Booking $booking): RedirectResponse
    {
        DB::transaction(function () use ($booking): void {
            $lockedRider = User::query()->lockForUpdate()->findOrFail(Auth::id());
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            $this->authorize('accept', $lockedBooking);

            if ($lockedRider->hasActiveBooking()) {
                abort(403, 'Complete your active booking before accepting another.');
            }

            $lockedBooking->update([
                'rider_id' => $lockedRider->id,
                'status' => 'accepted',
            ]);
        });

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
