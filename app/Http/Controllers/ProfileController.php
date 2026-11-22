<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function showPhoto(User $user): StreamedResponse
    {
        if (! $user->profile_photo_path || ! Storage::disk('public')->exists($user->profile_photo_path)) {
            abort(404);
        }

        return Storage::disk('public')->response($user->profile_photo_path, null, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:8192'],
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

    public function showLicense(Request $request, User $user): StreamedResponse
    {
        $viewer = $request->user();
        abort_unless($viewer && ($viewer->id === $user->id || $viewer->isAdmin()), 403);

        if (! $user->license_photo_path || ! Storage::disk('public')->exists($user->license_photo_path)) {
            abort(404);
        }

        return Storage::disk('public')->response($user->license_photo_path, null, [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function updateLicense(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'rider', 403);

        $validated = $request->validate([
            'license_photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ]);

        $previous = $user->license_photo_path;
        $path = $validated['license_photo']->store('rider-licenses', 'public');

        if ($path === false) {
            throw new \RuntimeException('The driver license photo could not be saved.');
        }

        $user->update(['license_photo_path' => $path]);

        if ($previous && Storage::disk('public')->exists($previous)) {
            Storage::disk('public')->delete($previous);
        }

        return Redirect::route('profile.edit')->with('status', 'license-photo-updated');
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
