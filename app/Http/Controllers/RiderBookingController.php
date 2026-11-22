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
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            ->sum('fare')
            + (float) $rider->ratingsReceived()->whereDate('created_at', today())->sum('tip_amount');
        $totalEarnings = (float) $rider->riderBookings()
            ->where('status', 'completed')
            ->sum('fare')
            + (float) $rider->ratingsReceived()->sum('tip_amount');

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

    public function toggleAvailability(Request $request): RedirectResponse
    {
        /** @var User $rider */
        $rider = $request->user();

        if ($rider->is_online && $rider->hasActiveBooking()) {
            return back()->with('error', 'Complete your active booking before going offline.');
        }

        $nextOnline = ! (bool) ($rider->is_online ?? true);
        $rider->update(['is_online' => $nextOnline]);

        return back()->with('success', $nextOnline
            ? 'You are now ONLINE and ready to receive trip requests.'
            : 'You are now OFFLINE. Toggle back online anytime to accept bookings.');
    }

    public function accept(Booking $booking): RedirectResponse
    {
        $errorMessage = null;

        DB::transaction(function () use ($booking, &$errorMessage): void {
            $lockedRider = User::query()->lockForUpdate()->findOrFail(Auth::id());
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if (($lockedRider->rider_status ?? 'verified') !== 'verified') {
                $errorMessage = 'Your rider account is not verified yet. Please wait for admin approval before accepting trips.';

                return;
            }

            if (! (bool) ($lockedRider->is_online ?? true)) {
                $errorMessage = 'You are currently Offline. Switch to Online mode first to accept bookings.';

                return;
            }

            if ($lockedRider->hasActiveBooking()) {
                abort(403, 'Complete your active booking before accepting another.');
            }

            // First-accept ("paunahan") atomic race guard: if another rider accepted a split-second earlier, do not overwrite
            if ($lockedBooking->status !== 'pending' || $lockedBooking->rider_id !== null) {
                $errorMessage = 'Naunahan ka na! This booking was just accepted by another rider.';

                return;
            }

            $this->authorize('accept', $lockedBooking);

            $lockedBooking->update([
                'rider_id' => $lockedRider->id,
                'status' => 'accepted',
            ]);
        });

        if ($errorMessage !== null) {
            return redirect()->route('rider.dashboard')->with('error', $errorMessage);
        }

        $booking->refresh()->customer->notify(new RiderHubNotification(
            'Rider matched',
            'A rider accepted your booking request and is heading to pickup. Your Trip Verification PIN is '.$booking->tripPin().'.',
            route('customer.bookings.show', $booking, absolute: false),
            'booking',
        ));

        return redirect()->route('rider.dashboard')->with('success', 'Booking accepted. Contact the customer and head to pickup.');
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['picked_up', 'completed'])],
            'proof_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ]);

        $proofPath = null;
        if ($validated['status'] === 'completed' && $request->hasFile('proof_photo')) {
            $stored = $request->file('proof_photo')->store('delivery-proofs', 'public');
            if ($stored !== false) {
                $proofPath = $stored;
            }
        }

        DB::transaction(function () use ($booking, $validated, $proofPath): void {
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

            $updates = ['status' => $validated['status']];
            if ($proofPath !== null) {
                $updates['proof_photo_path'] = $proofPath;
            }

            $lockedBooking->update($updates);

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

    public function showProof(Request $request, Booking $booking): StreamedResponse
    {
        $user = $request->user();
        abort_unless(
            $user && ($user->isAdmin() || $booking->customer_id === $user->id || $booking->rider_id === $user->id),
            403
        );

        if (! $booking->proof_photo_path || ! Storage::disk('public')->exists($booking->proof_photo_path)) {
            abort(404);
        }

        return Storage::disk('public')->response($booking->proof_photo_path, null, [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
