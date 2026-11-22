<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingRating;
use App\Notifications\RiderHubNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingRatingController extends Controller
{
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'score' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:500'],
            'tip_amount' => ['nullable', 'numeric', 'min:0', 'max:500'],
        ]);

        $tipAmount = round((float) ($validated['tip_amount'] ?? 0), 2);

        $rating = DB::transaction(function () use ($booking, $request, $validated, $tipAmount): BookingRating {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            abort_unless($lockedBooking->customer_id === $request->user()->id, 403);
            abort_unless($lockedBooking->status === 'completed' && $lockedBooking->rider_id, 422, 'Only completed bookings with an assigned rider can be rated.');
            abort_if($lockedBooking->rating()->exists(), 422, 'This booking has already been rated.');

            return $lockedBooking->rating()->create([
                'customer_id' => $request->user()->id,
                'rider_id' => $lockedBooking->rider_id,
                'score' => $validated['score'],
                'comment' => $validated['comment'] ?? null,
                'tip_amount' => $tipAmount,
            ]);
        });

        $tipText = $tipAmount > 0 ? ' with a ₱'.number_format($tipAmount, 2).' tip!' : '.';

        $rating->rider->notify(new RiderHubNotification(
            'New rider review',
            "{$request->user()->name} left you a {$rating->score}-star rating{$tipText}",
            route('rider.dashboard', absolute: false),
            'rating',
        ));

        return redirect()->route('customer.bookings.show', $booking)->with('success', 'Thanks for sharing your feedback.');
    }
}
