<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Services\BookingNotificationService;
use App\Services\PayMongoCheckoutService;
use App\Services\PayMongoCheckoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerBookingController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,accepted,picked_up,completed,cancelled'],
            'type' => ['nullable', 'in:ride,delivery'],
        ]);

        // Auto-heal any legacy outlier fare (> ₱2,500) created before Misamis Oriental coordinate bounds were enforced
        Auth::user()->bookings()->where('fare', '>', 2500)->each(function (Booking $outlier): void {
            $recalculated = min(1450.00, $this->calculateFare(
                (float) $outlier->pickup_lat,
                (float) $outlier->pickup_lng,
                (float) $outlier->dropoff_lat,
                (float) $outlier->dropoff_lng,
            ));
            $outlier->update(['fare' => $recalculated]);
            $outlier->payment?->update(['amount' => $recalculated]);
        });

        $showingBin = $request->boolean('bin');
        $trashedCount = Auth::user()->bookings()->onlyTrashed()->count();

        $query = $showingBin
            ? Auth::user()->bookings()->onlyTrashed()
            : Auth::user()->bookings();

        $bookings = $query
            ->with(['rider', 'payment'])
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($validated['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->latest($showingBin ? 'deleted_at' : 'created_at')
            ->paginate(10)
            ->withQueryString();

        return view('customer.bookings.index', compact('bookings', 'showingBin', 'trashedCount'));
    }

    public function clearHistory(): RedirectResponse
    {
        $moved = Auth::user()->bookings()
            ->whereIn('status', ['completed', 'cancelled'])
            ->delete();

        if ($moved === 0) {
            return redirect()->route('customer.bookings.index')
                ->with('success', 'No completed or cancelled bookings to clear.');
        }

        return redirect()->route('customer.bookings.index')
            ->with('success', "{$moved} ".($moved === 1 ? 'booking' : 'bookings').' moved to Recycle Bin (auto-deletes after 30 days).');
    }

    public function trash(Booking $booking): RedirectResponse
    {
        abort_unless($booking->customer_id === Auth::id(), 403);

        if (! in_array($booking->status, ['completed', 'cancelled'], true)) {
            return redirect()->route('customer.bookings.index')
                ->with('error', 'Only completed or cancelled bookings can be moved to the Recycle Bin.');
        }

        $booking->delete();

        return redirect()->route('customer.bookings.index')
            ->with('success', "Booking #{$booking->id} moved to Recycle Bin (auto-deletes after 30 days).");
    }

    public function restore(int $bookingId): RedirectResponse
    {
        $booking = Auth::user()->bookings()->onlyTrashed()->findOrFail($bookingId);
        $booking->restore();

        return redirect()->route('customer.bookings.index', ['bin' => 1])
            ->with('success', "Booking #{$booking->id} has been restored to your history.");
    }

    public function restoreAll(): RedirectResponse
    {
        $restored = Auth::user()->bookings()->onlyTrashed()->restore();

        return redirect()->route('customer.bookings.index')
            ->with('success', $restored > 0 ? "Restored {$restored} booking(s) back to your history." : 'Recycle Bin is already empty.');
    }

    public function forceDelete(int $bookingId): RedirectResponse
    {
        $booking = Auth::user()->bookings()->onlyTrashed()->findOrFail($bookingId);
        $booking->forceDelete();

        return redirect()->route('customer.bookings.index', ['bin' => 1])
            ->with('success', "Booking #{$bookingId} permanently deleted.");
    }

    public function emptyBin(): RedirectResponse
    {
        $deleted = Auth::user()->bookings()->onlyTrashed()->forceDelete();

        return redirect()->route('customer.bookings.index', ['bin' => 1])
            ->with('success', $deleted > 0 ? "Permanently deleted {$deleted} item(s) from Recycle Bin." : 'Recycle Bin is already empty.');
    }

    public function create(): View
    {
        $fare = config('riderhub.fare', ['base' => 55.00, 'per_km' => 18.50]);
        $map = config('riderhub.map');

        return view('customer.bookings.create', [
            'fareBase' => $fare['base'],
            'farePerKm' => $fare['per_km'],
            'mapConfig' => $map,
        ]);
    }

    public function store(
        StoreBookingRequest $request,
        PayMongoCheckoutService $checkoutService,
        BookingNotificationService $notifications,
    ): RedirectResponse {
        $validated = $request->validated();
        $rawFare = $this->calculateFare(
            (float) $validated['pickup_lat'],
            (float) $validated['pickup_lng'],
            (float) $validated['dropoff_lat'],
            (float) $validated['dropoff_lng'],
        );
        [$appliedPromo, $discountAmount] = $this->resolvePromoDiscount($validated['promo_code'] ?? null, $rawFare);
        $finalFare = round(max(35.00, $rawFare - $discountAmount), 2);

        $booking = DB::transaction(function () use ($validated, $finalFare, $appliedPromo, $discountAmount): Booking {
            $booking = Booking::create([
                'customer_id' => Auth::id(),
                'rider_id' => null,
                'type' => $validated['type'],
                'pickup_address' => $validated['pickup_address'],
                'pickup_lat' => (float) $validated['pickup_lat'],
                'pickup_lng' => (float) $validated['pickup_lng'],
                'dropoff_address' => $validated['dropoff_address'],
                'dropoff_lat' => (float) $validated['dropoff_lat'],
                'dropoff_lng' => (float) $validated['dropoff_lng'],
                'item_description' => $validated['item_description'] ?? null,
                'fare' => $finalFare,
                'promo_code' => $appliedPromo,
                'discount_amount' => $discountAmount,
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
                'scheduled_at' => $validated['scheduled_at'] ?? null,
            ]);

            $booking->payment()->create([
                'amount' => $booking->fare,
                'method' => $booking->payment_method,
                'status' => 'pending',
            ]);

            return $booking;
        });

        if ($booking->payment_method === 'online') {
            return $this->startCheckout($booking, $checkoutService);
        }

        $notifications->notifyAvailableRiders($booking);

        return redirect()->route('customer.bookings.index')->with('success', 'Booking created successfully.');
    }

    public function checkout(Booking $booking, PayMongoCheckoutService $checkoutService): RedirectResponse
    {
        $this->authorize('view', $booking);
        abort_unless($booking->customer_id === Auth::id(), 403);

        return $this->startCheckout($booking, $checkoutService);
    }

    public function show(Booking $booking, PayMongoCheckoutService $checkoutService): View|RedirectResponse
    {
        $this->authorize('view', $booking);
        $booking->load(['rider', 'payment', 'rating']);

        if (request()->query('checkout') === 'return' && $booking->customer_id === Auth::id()) {
            if ($checkoutService->retrieveAndConfirmPaidSession($booking)) {
                return redirect()->route('customer.bookings.show', $booking)
                    ->with('success', 'Payment confirmed by PayMongo. Your request is now available to riders.');
            }

            if ($booking->payment?->status !== 'paid') {
                return redirect()->route('customer.bookings.show', $booking)
                    ->with('info', 'Payment has not been completed yet. You can continue to checkout whenever you are ready.');
            }

            return redirect()->route('customer.bookings.show', $booking);
        }

        $booking->load('payment');
        $riderRatingAverage = $booking->rider?->ratingsReceived()->avg('score');
        $riderRatingCount = $booking->rider?->ratingsReceived()->count() ?? 0;

        return view('customer.bookings.show', [
            'booking' => $booking,
            'riderRatingAverage' => $riderRatingAverage ? number_format($riderRatingAverage, 1) : null,
            'riderRatingCount' => $riderRatingCount,
            'mapConfig' => config('riderhub.map'),
        ]);
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('view', $booking);
        abort_unless($booking->customer_id === Auth::id(), 403);

        $validated = $request->validate([
            'pickup_address' => ['required', 'string', 'max:255'],
            'pickup_lat' => ['required', 'numeric', 'between:-90,90'],
            'pickup_lng' => ['required', 'numeric', 'between:-180,180'],
            'dropoff_address' => ['required', 'string', 'max:255'],
            'dropoff_lat' => ['required', 'numeric', 'between:-90,90'],
            'dropoff_lng' => ['required', 'numeric', 'between:-180,180'],
            'item_description' => ['nullable', 'string', 'max:255'],
        ]);

        $distanceKm = $this->haversineDistance(
            (float) $validated['pickup_lat'],
            (float) $validated['pickup_lng'],
            (float) $validated['dropoff_lat'],
            (float) $validated['dropoff_lng'],
        );

        if ($distanceKm < 0.05) {
            return redirect()->route('customer.bookings.show', $booking)
                ->with('error', 'Pickup and drop-off must be at least 50 meters apart.');
        }

        $newFare = $this->calculateFare(
            (float) $validated['pickup_lat'],
            (float) $validated['pickup_lng'],
            (float) $validated['dropoff_lat'],
            (float) $validated['dropoff_lng'],
        );

        $updated = DB::transaction(function () use ($booking, $validated, $newFare): bool {
            $locked = Booking::query()->lockForUpdate()->with('payment')->findOrFail($booking->id);

            if (
                $locked->status !== 'pending'
                || $locked->rider_id !== null
                || $locked->payment?->status === 'paid'
                || ($locked->payment_method === 'online' && $locked->payment?->paymongo_reference)
            ) {
                return false;
            }

            $locked->update([
                'pickup_address' => $validated['pickup_address'],
                'pickup_lat' => (float) $validated['pickup_lat'],
                'pickup_lng' => (float) $validated['pickup_lng'],
                'dropoff_address' => $validated['dropoff_address'],
                'dropoff_lat' => (float) $validated['dropoff_lat'],
                'dropoff_lng' => (float) $validated['dropoff_lng'],
                'item_description' => $locked->type === 'delivery'
                    ? ($validated['item_description'] ?: $locked->item_description)
                    : null,
                'fare' => $newFare,
            ]);

            if ($locked->payment) {
                $locked->payment->update(['amount' => $newFare]);
            }

            return true;
        });

        if (! $updated) {
            return redirect()->route('customer.bookings.show', $booking)
                ->with('error', 'This booking can no longer be edited because a rider has accepted it or payment has started.');
        }

        return redirect()->route('customer.bookings.show', $booking)
            ->with('success', 'Pickup & drop-off updated and fare recalculated.');
    }

    public function location(Booking $booking): JsonResponse
    {
        $this->authorize('viewLocation', $booking);

        $location = $booking->riderLocations()->latest()->first();

        if (! $location) {
            return response()->json([
                'message' => 'No rider location is available yet.',
                'status' => $booking->status,
            ], 404);
        }

        return response()->json([
            'lat' => $location->lat,
            'lng' => $location->lng,
            'updated_at' => $location->created_at->toIso8601String(),
            'status' => $booking->status,
        ]);
    }

    public function cancel(Request $request, Booking $booking, PayMongoCheckoutService $checkoutService): RedirectResponse
    {
        $this->authorize('cancel', $booking);
        $booking->loadMissing('payment');
        $reason = trim((string) $request->input('cancellation_reason', ''));
        $reason = $reason !== '' ? mb_substr($reason, 0, 120) : null;

        if (
            $booking->payment?->method === 'online'
            && $booking->payment->status === 'pending'
            && $booking->payment->paymongo_reference
        ) {
            // The customer may have paid moments ago: never cancel a paid booking.
            if ($checkoutService->retrieveAndConfirmPaidSession($booking)) {
                return redirect()->route('customer.bookings.show', $booking)
                    ->with('success', 'PayMongo already received your payment, so this booking was not cancelled. It is now available to riders.');
            }

            if (! $checkoutService->expireUnpaidSession($booking->payment)) {
                return redirect()->route('customer.bookings.show', $booking)
                    ->with('error', 'We could not confirm the payment status with PayMongo. Please try cancelling again in a moment.');
            }
        }

        // Lock the row so a rider accepting at the same moment cannot end up with a cancelled trip.
        $cancelled = DB::transaction(function () use ($booking, $reason): bool {
            $locked = Booking::query()->lockForUpdate()->with('payment')->findOrFail($booking->id);

            if (
                $locked->status !== 'pending'
                || $locked->rider_id !== null
                || ($locked->payment_method === 'online' && $locked->payment?->status === 'paid')
            ) {
                return false;
            }

            $locked->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
            ]);

            if ($locked->payment?->status === 'pending') {
                $locked->payment->update(['status' => 'cancelled']);
            }

            return true;
        });

        if (! $cancelled) {
            return redirect()->route('customer.bookings.show', $booking)
                ->with('error', 'This booking can no longer be cancelled because a rider has already accepted it or it has been paid.');
        }

        return redirect()->route('customer.bookings.index')->with('success', 'Booking cancelled.');
    }

    private function startCheckout(Booking $booking, PayMongoCheckoutService $checkoutService): RedirectResponse
    {
        $booking->loadMissing('payment');

        if ($booking->payment?->status === 'paid') {
            return redirect()->route('customer.bookings.show', $booking)
                ->with('success', 'This booking has already been paid.');
        }

        abort_unless(
            $booking->payment?->method === 'online'
            && $booking->payment->status === 'pending'
            && $booking->status === 'pending',
            422,
            'Online checkout is only available for pending online bookings.'
        );

        try {
            $checkoutUrl = $checkoutService->createOrReuseSession($booking);
        } catch (PayMongoCheckoutException $exception) {
            return redirect()->route('customer.bookings.show', $booking)->with('error', $exception->getMessage());
        }

        return redirect()->away($checkoutUrl);
    }

    protected function calculateFare(float $pickupLat, float $pickupLng, float $dropoffLat, float $dropoffLng): float
    {
        $distance = min(120.0, $this->haversineDistance($pickupLat, $pickupLng, $dropoffLat, $dropoffLng));
        $config = config('riderhub.fare', ['base' => 55.00, 'per_km' => 18.50]);

        return round($config['base'] + ($distance * $config['per_km']), 2);
    }

    protected function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2)
            + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLng / 2) * sin($deltaLng / 2);

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * @return array{0: ?string, 1: float}
     */
    protected function resolvePromoDiscount(?string $inputCode, float $rawFare): array
    {
        $code = strtoupper(trim((string) $inputCode));
        if ($code === '') {
            return [null, 0.0];
        }

        $promos = config('riderhub.promos', []);
        if (! isset($promos[$code])) {
            return [null, 0.0];
        }

        $promo = $promos[$code];
        $discount = $promo['type'] === 'percent'
            ? min((float) ($promo['max'] ?? 50.0), round($rawFare * ((float) $promo['value'] / 100), 2))
            : (float) $promo['value'];

        $discount = round(min(max(0.0, $rawFare - 35.00), $discount), 2);

        return [$code, $discount];
    }
}
