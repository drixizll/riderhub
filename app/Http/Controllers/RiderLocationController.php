<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\RiderLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiderLocationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);
        $this->authorize('updateStatus', $booking);

        abort_unless(in_array($booking->status, ['accepted', 'picked_up'], true), 422, 'Location updates are only available for active bookings.');

        $location = RiderLocation::create([
            'rider_id' => Auth::id(),
            'booking_id' => $booking->id,
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
        ]);

        return response()->json([
            'lat' => $location->lat,
            'lng' => $location->lng,
            'updated_at' => $location->created_at->toIso8601String(),
        ]);
    }
}
