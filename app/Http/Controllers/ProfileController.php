<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $ratingAverage = $user->role === 'rider' ? $user->ratingsReceived()->avg('score') : null;

        return view('profile.edit', [
            'user' => $user,
            'profileStats' => match ($user->role) {
                'customer' => [
                    ['label' => 'Bookings', 'value' => $user->bookings()->count()],
                    ['label' => 'Completed', 'value' => $user->bookings()->where('status', 'completed')->count()],
                ],
                'rider' => [
                    ['label' => 'Trips', 'value' => $user->riderBookings()->count()],
                    ['label' => 'Average rating', 'value' => $ratingAverage ? number_format($ratingAverage, 1).'/5' : 'New'],
                ],
                default => [
                    ['label' => 'Account type', 'value' => 'Administrator'],
                    ['label' => 'Member since', 'value' => $user->created_at->format('M Y')],
                ],
            },
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();
        $previousPhoto = $user->profile_photo_path;
        $path = $validated['profile_photo']->store('profile-photos', 'public');

        if ($path === false) {
            throw new \RuntimeException('The profile photo could not be saved.');
        }

        $user->update(['profile_photo_path' => $path]);

        if ($previousPhoto && Storage::disk('public')->exists($previousPhoto) && ! Storage::disk('public')->delete($previousPhoto)) {
            Log::warning('An old RiderHub profile photo could not be removed.', ['user_id' => $user->id]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-photo-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Deleting an account mid-trip would orphan the booking (or erase a paid one).
        $hasActiveBooking = \App\Models\Booking::query()
            ->where(fn ($query) => $query->where('customer_id', $user->id)->orWhere('rider_id', $user->id))
            ->where(fn ($query) => $query
                ->whereIn('status', ['accepted', 'picked_up'])
                ->orWhere(fn ($pending) => $pending
                    ->where('status', 'pending')
                    ->whereHas('payment', fn ($payment) => $payment->where('method', 'online')->where('status', 'paid'))))
            ->exists();

        if ($hasActiveBooking) {
            return Redirect::route('profile.edit')->withErrors([
                'password' => 'You have an active or paid booking. Finish it before deleting your account.',
            ], 'userDeletion');
        }

        $profilePhoto = $user->profile_photo_path;

        Auth::logout();

        if ($profilePhoto && Storage::disk('public')->exists($profilePhoto) && ! Storage::disk('public')->delete($profilePhoto)) {
            Log::warning('A RiderHub profile photo could not be removed during account deletion.', ['user_id' => $user->id]);
        }

        $user->notifications()->delete();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
