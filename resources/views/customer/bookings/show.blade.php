<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.bookings.index') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50" aria-label="Back to bookings">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6" /></svg>
            </a>
            <div>
                <p class="text-sm font-semibold text-indigo-600">Booking #{{ $booking->id }}</p>
                <h1 class="mt-0.5 text-2xl font-extrabold tracking-tight text-slate-950">Booking details</h1>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">{{ session('error') }}</div>
        @endif
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 bg-gradient-to-r from-indigo-50/70 to-white px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full {{ $booking->type === 'delivery' ? 'bg-amber-50 text-amber-700' : 'bg-indigo-50 text-indigo-700' }} px-2.5 py-1 text-xs font-bold uppercase">{{ $booking->type }}</span>
                        <span class="rounded-full px-2.5 py-1 text-xs font-bold capitalize {{ match ($booking->status) { 'completed' => 'bg-emerald-50 text-emerald-700', 'cancelled' => 'bg-rose-50 text-rose-700', 'accepted', 'picked_up' => 'bg-indigo-50 text-indigo-700', default => 'bg-amber-50 text-amber-700' } }}">{{ str_replace('_', ' ', $booking->status) }}</span>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">Created {{ $booking->created_at->format('M j, Y · g:i A') }}</p>
                </div>
                <div class="sm:text-right"><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total fare</p><p class="mt-1 text-3xl font-extrabold text-slate-950">₱{{ number_format($booking->fare, 2) }}</p></div>
            </div>

            <div class="grid gap-8 p-5 sm:p-7 lg:grid-cols-[1fr_0.8fr]">
                <div>
                    <h2 class="text-base font-bold text-slate-950">Your route</h2>
                    <div class="mt-5 space-y-1">
                        <div class="flex gap-4">
                            <span class="mt-1 h-3 w-3 shrink-0 rounded-full border-2 border-indigo-600 bg-white"></span>
                            <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pickup</p><p class="mt-1 font-semibold text-slate-900">{{ $booking->pickup_address }}</p></div>
                        </div>
                        <div class="ml-1.5 h-8 border-l-2 border-dashed border-slate-200"></div>
                        <div class="flex gap-4">
                            <span class="mt-1 h-3 w-3 shrink-0 rounded-sm bg-amber-400"></span>
                            <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Drop-off</p><p class="mt-1 font-semibold text-slate-900">{{ $booking->dropoff_address }}</p></div>
                        </div>
                    </div>
                    @if ($booking->item_description)
                        <div class="mt-6 rounded-xl bg-amber-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-amber-800">Package details</p>
                            <p class="mt-1 text-sm leading-6 text-amber-950">{{ $booking->item_description }}</p>
                        </div>
                    @endif
                </div>

                <div>
                    <h2 class="text-base font-bold text-slate-950">Trip information</h2>
                    <dl class="mt-4 divide-y divide-slate-100 rounded-xl border border-slate-100 px-4">
                        <div class="flex justify-between gap-4 py-3 text-sm"><dt class="text-slate-500">Payment method</dt><dd class="font-semibold capitalize text-slate-900">{{ $booking->payment_method }}</dd></div>
                        <div class="flex justify-between gap-4 py-3 text-sm"><dt class="text-slate-500">Payment status</dt><dd class="font-semibold capitalize text-slate-900">{{ str_replace('_', ' ', $booking->payment?->status ?? 'pending') }}</dd></div>
                        <div class="flex justify-between gap-4 py-3 text-sm"><dt class="text-slate-500">Rider</dt><dd class="text-right font-semibold text-slate-900">{{ $booking->rider?->name ?? 'Finding a rider' }}</dd></div>
                        @if ($booking->rider && $riderRatingCount > 0)
                            <div class="flex justify-between gap-4 py-3 text-sm"><dt class="text-slate-500">Rider rating</dt><dd class="font-bold text-amber-600">★ {{ $riderRatingAverage }} <span class="font-medium text-slate-400">({{ $riderRatingCount }})</span></dd></div>
                        @endif
                        @if ($booking->rider?->phone)
                            <div class="flex justify-between gap-4 py-3 text-sm"><dt class="text-slate-500">Rider contact</dt><dd><a href="tel:{{ $booking->rider->phone }}" class="font-semibold text-indigo-600 hover:text-indigo-800">{{ $booking->rider->phone }}</a></dd></div>
                        @endif
                    </dl>
                    @if ($booking->payment_method === 'online' && $booking->payment?->status === 'paid' && $booking->status === 'pending')
                        <p class="mt-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm leading-6 text-emerald-800">Payment confirmed. Your booking is waiting for a rider to accept it. The trip will be marked completed after the rider finishes the ride or delivery.</p>
                    @endif
                    @if ($booking->payment_method === 'online' && $booking->payment?->status !== 'paid' && $booking->status === 'pending')
                        <div class="mt-4 rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-4">
                            <p class="text-sm font-bold text-indigo-950">Complete your secure payment</p>
                            <p class="mt-1 text-sm leading-6 text-indigo-800">Choose GCash, Maya, or an eligible Visa/Mastercard on PayMongo. Your request will be sent to riders after PayMongo confirms payment.</p>
                            <form method="POST" action="{{ route('customer.bookings.checkout', $booking) }}" class="mt-4">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    {{ $booking->payment?->paymongo_checkout_url ? 'Continue to checkout' : 'Start secure checkout' }}
                                </button>
                            </form>
                        </div>
                    @elseif ($booking->status === 'pending')
                        <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-800">Your request is waiting for a rider. You can cancel while it is still pending.</p>
                    @endif
                </div>
            </div>

            @if ($booking->status === 'completed' && $booking->rider)
                <div class="border-t border-slate-100 bg-gradient-to-r from-amber-50/60 to-white p-5 sm:p-7">
                    @if ($booking->rating)
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-wider text-amber-700">Your rider review</p>
                                <h2 class="mt-1 text-lg font-extrabold text-slate-950">Thanks for riding with {{ $booking->rider->name }}</h2>
                                <p class="mt-2 text-2xl tracking-widest text-amber-500" aria-label="{{ $booking->rating->score }} out of 5 stars">{{ str_repeat('★', $booking->rating->score) }}<span class="text-slate-300">{{ str_repeat('★', 5 - $booking->rating->score) }}</span></p>
                                @if ($booking->rating->comment)
                                    <p class="mt-2 text-sm leading-6 text-slate-600">“{{ $booking->rating->comment }}”</p>
                                @endif
                            </div>
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">Review submitted</span>
                        </div>
                    @else
                        <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-wider text-amber-700">Trip complete</p>
                                <h2 class="mt-1 text-xl font-extrabold text-slate-950">How was your rider?</h2>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Your review helps other customers and celebrates great service from {{ $booking->rider->name }}.</p>
                            </div>
                            <form method="POST" action="{{ route('customer.bookings.rating', $booking) }}" class="space-y-4">
                                @csrf
                                <fieldset>
                                    <legend class="text-sm font-semibold text-slate-700">Your rating</legend>
                                    <div data-rating-choices class="mt-2 flex items-center gap-1" role="radiogroup" aria-label="Rate your rider from 1 to 5 stars">
                                        @php($selectedScore = (int) old('score', 5))
                                        @for ($score = 1; $score <= 5; $score++)
                                            <label class="rating-choice cursor-pointer rounded-lg p-1.5 text-3xl leading-none transition hover:scale-110 {{ $score <= $selectedScore ? 'is-selected' : '' }}">
                                                <input class="sr-only" type="radio" name="score" value="{{ $score }}" @checked($selectedScore === $score) required>
                                                <span aria-hidden="true">★</span>
                                                <span class="sr-only">{{ $score }} {{ \Illuminate\Support\Str::plural('star', $score) }}</span>
                                            </label>
                                        @endfor
                                    </div>
                                    <x-input-error :messages="$errors->get('score')" class="mt-1" />
                                </fieldset>
                                <div>
                                    <label for="rating-comment" class="block text-sm font-semibold text-slate-700">Share a quick note <span class="font-normal text-slate-400">(optional)</span></label>
                                    <textarea id="rating-comment" name="comment" rows="2" maxlength="500" placeholder="What went well?" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">{{ old('comment') }}</textarea>
                                    <x-input-error :messages="$errors->get('comment')" class="mt-1" />
                                </div>
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Submit review
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif

            @if (in_array($booking->status, ['accepted', 'picked_up'], true) && $booking->rider)
                <div class="border-t border-slate-100 p-5 sm:p-7">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                        <div><h2 class="font-bold text-slate-950">Live rider tracking</h2><p data-tracking-status role="status" aria-live="polite" class="mt-1 text-sm text-slate-500">Waiting for the rider to share their location…</p></div>
                        <span class="text-xs text-slate-400">Updates automatically while this page is open</span>
                    </div>
                    <div id="tracking-map" class="mt-4 h-72 overflow-hidden rounded-xl border border-slate-200 bg-slate-100" aria-label="Map showing rider location"></div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const map = L.map('tracking-map').setView([{{ $booking->pickup_lat }}, {{ $booking->pickup_lng }}], 13);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors',
                        }).addTo(map);
                        L.marker([{{ $booking->pickup_lat }}, {{ $booking->pickup_lng }}]).addTo(map).bindPopup('Pickup');
                        L.marker([{{ $booking->dropoff_lat }}, {{ $booking->dropoff_lng }}]).addTo(map).bindPopup('Drop-off');
                        let riderMarker = null;
                        let refreshInFlight = false;
                        const status = document.querySelector('[data-tracking-status]');

                        const refreshLocation = async () => {
                            if (refreshInFlight || document.hidden) return;
                            refreshInFlight = true;

                            try {
                                const response = await fetch(@json(route('customer.bookings.location', $booking)), {
                                    headers: { 'Accept': 'application/json' },
                                    cache: 'no-store',
                                });
                                if (response.status === 404) {
                                    status.textContent = 'Your rider has not shared a location yet. We’ll keep checking.';
                                    return;
                                }
                                if (!response.ok) throw new Error('Location could not be loaded.');
                                const location = await response.json();
                                const point = [location.lat, location.lng];
                                if (!riderMarker) riderMarker = L.marker(point).addTo(map).bindPopup('Your rider');
                                else riderMarker.setLatLng(point);
                                status.textContent = `Rider location updated ${new Date(location.updated_at).toLocaleTimeString()}`;
                            } catch (error) {
                                status.textContent = 'Could not refresh rider location. Check your connection.';
                            } finally {
                                refreshInFlight = false;
                            }
                        };

                        refreshLocation();
                        window.setInterval(refreshLocation, 5000);
                        document.addEventListener('visibilitychange', refreshLocation);
                    });
                </script>
            @endif

            @if ($booking->status === 'pending' && ! ($booking->payment_method === 'online' && ($booking->payment?->paymongo_reference || $booking->payment?->status === 'paid')))
                <div class="border-t border-slate-100 px-5 py-4 sm:px-7">
                    <form action="{{ route('customer.bookings.cancel', $booking) }}" method="POST" onsubmit="return confirm('Cancel this booking?');">
                        @csrf
                        <button type="submit" class="rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-bold text-rose-700 transition hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">Cancel booking</button>
                    </form>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
