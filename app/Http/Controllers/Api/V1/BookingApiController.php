<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $query = Booking::with(['customer:id,name', 'rider:id,name', 'payment:id,booking_id,method,status'])
            ->latest();

        if ($user->role === 'customer') {
            $query->where('customer_id', $user->id);
        } elseif ($user->role === 'rider') {
            $query->where(fn ($q) => $q->where('rider_id', $user->id)->orWhere(fn ($open) => $open->whereNull('rider_id')->where('status', 'pending')));
        }

        $bookings = $query->limit(25)->get()->map(fn (Booking $b) => [
            'id' => $b->id,
            'type' => $b->type,
            'status' => $b->status,
            'pickup_address' => $b->pickup_address,
            'dropoff_address' => $b->dropoff_address,
            'distance_km' => (float) $b->distance_km,
            'fare' => (float) $b->fare,
            'formatted_fare' => '₱'.number_format((float) $b->fare, 2),
            'payment_method' => $b->payment_method,
            'payment_status' => $b->payment?->status ?? 'pending',
            'customer' => $b->customer?->name,
            'rider' => $b->rider?->name,
            'created_at' => $b->created_at?->toIso8601String(),
        ]);

        return response()->json(['bookings' => $bookings]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== 'customer') {
            return response()->json(['message' => 'Only customer accounts can create bookings.'], 403);
        }

        $hasActiveBooking = Booking::query()
            ->where('customer_id', $user->id)
            ->whereIn('status', ['pending', 'accepted', 'picked_up'])
            ->exists();

        if ($hasActiveBooking) {
            return response()->json([
                'message' => 'You already have an active booking. Please complete or cancel it first.',
            ], 422);
        }

        $bounds = config('riderhub.map.bounds', []);
        $minLat = (float) ($bounds['south'] ?? 8.20);
        $maxLat = (float) ($bounds['north'] ?? 9.15);
        $minLng = (float) ($bounds['west'] ?? 124.15);
        $maxLng = (float) ($bounds['east'] ?? 125.35);

        $validated = $request->validate([
            'type' => ['required', 'in:ride,delivery'],
            'pickup_address' => ['required', 'string', 'max:255'],
            'pickup_lat' => ['required', 'numeric', "between:{$minLat},{$maxLat}"],
            'pickup_lng' => ['required', 'numeric', "between:{$minLng},{$maxLng}"],
            'dropoff_address' => ['required', 'string', 'max:255'],
            'dropoff_lat' => ['required', 'numeric', "between:{$minLat},{$maxLat}"],
            'dropoff_lng' => ['required', 'numeric', "between:{$minLng},{$maxLng}"],
            'payment_method' => ['required', 'in:cash,online'],
            'item_description' => ['nullable', 'string', 'max:255'],
        ]);

        $lat1 = deg2rad((float) $validated['pickup_lat']);
        $lng1 = deg2rad((float) $validated['pickup_lng']);
        $lat2 = deg2rad((float) $validated['dropoff_lat']);
        $lng2 = deg2rad((float) $validated['dropoff_lng']);
        $dLat = $lat2 - $lat1;
        $dLng = $lng2 - $lng1;
        $a = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLng / 2) ** 2;
        $distanceKm = round(6371 * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);

        if ($distanceKm < 0.05) {
            return response()->json(['message' => 'Pickup and drop-off must be at least 50 meters apart.'], 422);
        }

        $fareConfig = config('riderhub.fare', ['base' => 55.00, 'per_km' => 18.50]);
        $fare = round((float) $fareConfig['base'] + ($distanceKm * (float) $fareConfig['per_km']), 2);

        $booking = Booking::create([
            'customer_id' => $user->id,
            'type' => $validated['type'],
            'pickup_address' => $validated['pickup_address'],
            'pickup_lat' => $validated['pickup_lat'],
            'pickup_lng' => $validated['pickup_lng'],
            'dropoff_address' => $validated['dropoff_address'],
            'dropoff_lat' => $validated['dropoff_lat'],
            'dropoff_lng' => $validated['dropoff_lng'],
            'distance_km' => $distanceKm,
            'fare' => $fare,
            'payment_method' => $validated['payment_method'],
            'item_description' => $validated['type'] === 'delivery' ? ($validated['item_description'] ?? null) : null,
            'status' => 'pending',
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'amount' => $fare,
            'method' => $validated['payment_method'],
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Booking created successfully.',
            'booking' => [
                'id' => $booking->id,
                'type' => $booking->type,
                'status' => $booking->status,
                'pickup_address' => $booking->pickup_address,
                'dropoff_address' => $booking->dropoff_address,
                'distance_km' => (float) $booking->distance_km,
                'fare' => (float) $booking->fare,
                'formatted_fare' => '₱'.number_format((float) $booking->fare, 2),
                'payment_method' => $booking->payment_method,
            ],
        ], 201);
    }

    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== 'customer' || $booking->customer_id !== $user->id) {
            return response()->json(['message' => 'You can only cancel your own booking.'], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'Only pending bookings can be cancelled.'], 422);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Booking cancelled successfully.',
            'booking' => ['id' => $booking->id, 'status' => $booking->status],
        ]);
    }

    public function accept(Request $request, Booking $booking): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== 'rider') {
            return response()->json(['message' => 'Only rider accounts can accept bookings.'], 403);
        }

        $statusCode = 200;
        $payload = [];

        DB::transaction(function () use ($user, $booking, &$statusCode, &$payload): void {
            $lockedRider = User::query()->lockForUpdate()->findOrFail($user->id);
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($lockedRider->hasActiveBooking()) {
                $statusCode = 403;
                $payload = ['message' => 'Complete your active booking before accepting another.'];

                return;
            }

            if ($lockedBooking->status !== 'pending' || $lockedBooking->rider_id !== null) {
                $statusCode = 409;
                $payload = ['message' => 'This booking was already accepted by another rider.'];

                return;
            }

            $lockedBooking->update([
                'rider_id' => $lockedRider->id,
                'status' => 'accepted',
            ]);

            $payload = [
                'message' => 'Booking accepted successfully.',
                'booking' => [
                    'id' => $lockedBooking->id,
                    'status' => $lockedBooking->status,
                    'rider_id' => $lockedRider->id,
                ],
            ];
        });

        return response()->json($payload, $statusCode);
    }
}
