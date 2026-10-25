<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Rider workspace</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Ready for your next trip, {{ Str::before(Auth::user()->name, ' ') }}?</h1>
                <p class="mt-1 text-sm text-slate-500">Manage your active booking and pick up a new request.</p>
            </div>
            <a href="{{ route('rider.bookings.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Browse requests</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Rider summary">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Available requests</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $availableCount }}</p>
                <p class="mt-1 text-xs text-slate-500">Waiting for a rider</p>
            </article>
            <article class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-5 shadow-sm">
                <p class="text-sm font-medium text-indigo-700">Assigned to you</p>
                <p class="mt-2 text-3xl font-extrabold text-indigo-950">{{ $assignedCount }}</p>
                <p class="mt-1 text-xs text-indigo-700/80">All-time bookings</p>
            </article>
            <article class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-5 shadow-sm">
                <p class="text-sm font-medium text-emerald-700">Completed</p>
                <p class="mt-2 text-3xl font-extrabold text-emerald-950">{{ $completedCount }}</p>
                <p class="mt-1 text-xs text-emerald-700/80">Successfully delivered</p>
            </article>
            <article class="rounded-2xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-amber-800">Rider rating</p>
                <p class="mt-2 text-3xl font-extrabold text-amber-950">{{ $ratingAverage ?? 'New' }}@if ($ratingAverage)<span class="text-lg">/5</span>@endif</p>
                <p class="mt-1 text-xs text-amber-800/80">{{ $ratingCount }} {{ \Illuminate\Support\Str::plural('review', $ratingCount) }} from customers</p>
            </article>
        </section>

        @if ($activeBooking)
            <section class="overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-100 bg-gradient-to-r from-indigo-50 to-white px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-bold uppercase tracking-wider text-indigo-700">Your active booking</p>
                            <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-bold capitalize text-indigo-700">{{ str_replace('_', ' ', $activeBooking->status) }}</span>
                        </div>
                        <h2 class="mt-2 text-xl font-extrabold text-slate-950">{{ ucfirst($activeBooking->type) }} · {{ $activeBooking->customer->name }}</h2>
                    </div>
                    <span class="text-xl font-extrabold text-slate-950">₱{{ number_format($activeBooking->fare, 2) }}</span>
                </div>

                <div class="grid gap-6 p-5 sm:p-6 lg:grid-cols-[1fr_auto]">
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <span class="mt-1 h-3 w-3 shrink-0 rounded-full border-2 border-indigo-600 bg-white"></span>
                            <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pickup</p><p class="mt-1 font-semibold text-slate-900">{{ $activeBooking->pickup_address }}</p></div>
                        </div>
                        <div class="ml-1.5 h-5 border-l-2 border-dashed border-slate-200"></div>
                        <div class="flex gap-3">
                            <span class="mt-1 h-3 w-3 shrink-0 rounded-sm bg-amber-400"></span>
                            <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Drop-off</p><p class="mt-1 font-semibold text-slate-900">{{ $activeBooking->dropoff_address }}</p></div>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            Customer contact: <span class="font-semibold text-slate-900">{{ $activeBooking->customer->phone ?: $activeBooking->customer->email }}</span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 lg:min-w-56">
                        @if ($activeBooking->status === 'accepted')
                            <form method="POST" action="{{ route('rider.bookings.status', $activeBooking) }}">
                                @csrf
                                <input type="hidden" name="status" value="picked_up">
                                <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Mark as picked up</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('rider.bookings.status', $activeBooking) }}">
                                @csrf
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">Complete booking</button>
                            </form>
                        @endif
                        <button type="button"
                                data-location-button
                                data-booking-id="{{ $activeBooking->id }}"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                            Start live location
                        </button>
                        <p data-location-status role="status" aria-live="polite" class="text-center text-xs leading-5 text-slate-500">Share your location while you’re on the trip.</p>
                    </div>
                </div>
            </section>
        @else
            <section class="rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-50 to-white p-6 sm:p-8">
                <p class="text-sm font-bold uppercase tracking-wider text-indigo-700">You’re available</p>
                <h2 class="mt-2 text-xl font-extrabold text-slate-950">Choose a booking request to get started</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Accept one pending request at a time. Once it’s accepted, update the trip status and share your live location with the customer.</p>
            </section>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5 sm:px-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Open booking requests</h2>
                    <p class="mt-1 text-sm text-slate-500">Review the route and fare before accepting.</p>
                </div>
                <a href="{{ route('rider.bookings.index') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-800">View all <span aria-hidden="true">→</span></a>
            </div>

            @include('rider.bookings.partials.list', ['bookings' => $bookings, 'activeBooking' => $activeBooking])
        </section>
    </div>

    @if ($activeBooking)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const button = document.querySelector('[data-location-button]');
                const status = document.querySelector('[data-location-status]');
                let watchId = null;
                let lastSentAt = 0;
                let updateInFlight = false;

                button.addEventListener('click', () => {
                    if (watchId !== null) {
                        navigator.geolocation.clearWatch(watchId);
                        watchId = null;
                        button.textContent = 'Start live location';
                        status.textContent = 'Location sharing stopped.';
                        return;
                    }

                    if (!navigator.geolocation) {
                        status.textContent = 'Location sharing is not supported by this browser.';
                        return;
                    }

                    button.textContent = 'Stop live location';
                    status.textContent = 'Waiting for location permission… Keep this page open during the trip.';
                    watchId = navigator.geolocation.watchPosition(async (position) => {
                        const now = Date.now();
                        if (document.hidden || updateInFlight || now - lastSentAt < 5000) return;
                        lastSentAt = now;
                        updateInFlight = true;

                        try {
                            const response = await fetch(@json(route('rider.locations.store')), {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({
                                    booking_id: button.dataset.bookingId,
                                    lat: position.coords.latitude,
                                    lng: position.coords.longitude,
                                }),
                            });

                            if (!response.ok) throw new Error('Location update was rejected.');
                            status.textContent = `Location shared · ${new Date().toLocaleTimeString()}`;
                        } catch (error) {
                            status.textContent = 'Could not share location. Check your connection and try again.';
                        } finally {
                            updateInFlight = false;
                        }
                    }, () => {
                        status.textContent = 'Unable to read your location. Allow location access and try again.';
                        navigator.geolocation.clearWatch(watchId);
                        watchId = null;
                        button.textContent = 'Start live location';
                    }, { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 });
                });
            });
        </script>
    @endif
</x-app-layout>
