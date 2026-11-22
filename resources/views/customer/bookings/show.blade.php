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
@if (session('info'))
<div role="status" class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-medium text-sky-800">{{ session('info') }}</div>
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
                <div class="flex flex-wrap items-center justify-between gap-3 sm:flex-col sm:items-end">
                    <div class="sm:text-right"><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total fare</p><p class="mt-1 text-3xl font-extrabold text-slate-950">₱{{ number_format($booking->fare, 2) }}</p></div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('customer.bookings.create', ['type' => $booking->type, 'pickup_address' => $booking->pickup_address, 'pickup_lat' => $booking->pickup_lat, 'pickup_lng' => $booking->pickup_lng, 'dropoff_address' => $booking->dropoff_address, 'dropoff_lat' => $booking->dropoff_lat, 'dropoff_lng' => $booking->dropoff_lng]) }}"
                           class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm shadow-indigo-600/25 transition hover:-translate-y-0.5 hover:shadow-md">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M5.64 18.36A9 9 0 0 0 20 12M18.36 5.64A9 9 0 0 0 4 12" /></svg>
                            Book Again
                        </a>
                        <button type="button"
                                x-data="{ copied: false }"
                                @click="
                                    const text = `RiderHub Trip #{{ $booking->id }} ({{ ucfirst($booking->status) }}): {{ addslashes($booking->pickup_address) }} → {{ addslashes($booking->dropoff_address) }} (₱{{ number_format($booking->fare, 2) }})`;
                                    if (navigator.share) { navigator.share({ title: 'RiderHub Trip Status', text, url: window.location.href }).catch(() => {}); }
                                    else if (navigator.clipboard) { navigator.clipboard.writeText(text + ' ' + window.location.href); copied = true; setTimeout(() => copied = false, 2500); }
                                "
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" /></svg>
                            <span x-text="copied ? 'Copied ✓' : 'Share Trip'">Share Trip</span>
                        </button>
                        <a href="{{ route('customer.bookings.receipt', $booking) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-white px-3.5 py-2 text-xs font-bold text-indigo-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-50">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8Z" /></svg>
                            PDF Receipt
                        </a>
                    </div>
                </div>
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
                        @if ($booking->promo_code && (float) $booking->discount_amount > 0)
                            <div class="flex justify-between gap-4 py-3 text-sm"><dt class="text-slate-500">Promo applied</dt><dd class="font-bold text-emerald-700">{{ $booking->promo_code }} (-₱{{ number_format((float) $booking->discount_amount, 2) }})</dd></div>
                        @endif
                        @if ($booking->status === 'cancelled' && $booking->cancellation_reason)
                            <div class="flex justify-between gap-4 py-3 text-sm"><dt class="text-slate-500">Cancel reason</dt><dd class="text-right font-semibold text-rose-700">{{ $booking->cancellation_reason }}</dd></div>
                        @endif
                        <div class="flex justify-between gap-4 py-3 text-sm">
                            <dt class="text-slate-500">Rider</dt>
                            <dd class="text-right font-semibold text-slate-900">
                                {{ $booking->rider?->name ?? 'Finding a rider' }}
                                @if ($booking->rider && ($booking->rider->rider_status ?? 'verified') === 'verified')
                                    <span class="ml-1 inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">✓ Verified</span>
                                @endif
                            </dd>
                        </div>
                        @if ($booking->rider && ($booking->rider->vehicle_model || $booking->rider->plate_number))
                            <div class="flex justify-between gap-4 py-3 text-sm">
                                <dt class="text-slate-500">Vehicle / Plate</dt>
                                <dd class="text-right font-semibold text-slate-900">
                                    {{ $booking->rider->vehicle_model ?: 'Motorcycle' }}
                                    @if ($booking->rider->plate_number)
                                        <span class="ml-1 rounded-md bg-slate-100 px-2 py-0.5 font-mono text-xs font-bold text-slate-800">{{ $booking->rider->plate_number }}</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
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
                                        @php
                                            $selectedScore = (int) old('score', 5);
                                        @endphp
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
                    @php
                        $steps = [
                            ['key' => 'pending', 'label' => 'Booked'],
                            ['key' => 'accepted', 'label' => 'Rider matched'],
                            ['key' => 'picked_up', 'label' => 'On the way'],
                            ['key' => 'completed', 'label' => 'Completed'],
                        ];
                        $currentIndex = match ($booking->status) {
                            'accepted' => 1,
                            'picked_up' => 2,
                            'completed' => 3,
                            default => 0,
                        };
                    @endphp
                    <ol class="mb-6 grid grid-cols-4 gap-2" aria-label="Trip progress">
                        @foreach ($steps as $index => $step)
                            <li class="flex flex-col gap-1.5">
                                <div class="h-1.5 rounded-full transition-all duration-500 {{ $index <= $currentIndex ? 'bg-indigo-600' : 'bg-slate-200' }}"></div>
                                <span class="text-[11px] font-bold {{ $index <= $currentIndex ? 'text-indigo-700' : 'text-slate-400' }}">{{ $step['label'] }}</span>
                            </li>
                        @endforeach
                    </ol>

                    <div class="grid gap-5 lg:grid-cols-[1.45fr_0.55fr]">
                        <div>
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="relative flex h-2.5 w-2.5">
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                        </span>
                                        <h2 class="font-bold text-slate-950">Live rider tracking</h2>
                                    </div>
                                    <p data-tracking-status role="status" aria-live="polite" class="mt-1 text-sm text-slate-500">Waiting for the rider to share their location…</p>
                                </div>
                                <div data-tracking-eta class="hidden items-center gap-2 rounded-xl bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700">
                                    <span data-eta-distance>—</span>
                                    <span class="text-indigo-300">·</span>
                                    <span data-eta-minutes>—</span>
                                </div>
                            </div>
                            <div id="tracking-map" class="mt-4 h-80 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-inner" aria-label="Map showing rider location"></div>
                        </div>

                        <div class="flex flex-col justify-between gap-4">
                            <x-weather-widget title="Route Weather" :lat="$booking->pickup_lat" :lng="$booking->pickup_lng" />

                            <div class="rounded-2xl border border-rose-200/80 bg-rose-50/70 p-4 text-xs leading-5 text-rose-900">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="font-extrabold uppercase tracking-wider text-rose-700">Safety &amp; SOS</p>
                                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700">Active Trip</span>
                                </div>
                                <p class="mt-1.5 text-rose-800/90">Need urgent assistance? Call emergency services or copy your live trip details for family.</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a href="tel:911" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-rose-600 px-3 py-2 text-xs font-extrabold text-white shadow-sm transition hover:bg-rose-700">
                                        Emergency 911
                                    </a>
                                    <button type="button"
                                            x-data="{ copied: false }"
                                            @click="
                                                const info = `SOS RiderHub Trip #{{ $booking->id }} | Rider: {{ addslashes($booking->rider->name) }} ({{ addslashes($booking->rider->phone ?? 'N/A') }}) | From: {{ addslashes($booking->pickup_address) }} → To: {{ addslashes($booking->dropoff_address) }}`;
                                                if (navigator.clipboard) { navigator.clipboard.writeText(info); copied = true; setTimeout(() => copied = false, 2500); }
                                            "
                                            class="inline-flex flex-1 items-center justify-center gap-1 rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                        <span x-text="copied ? 'Copied ✓' : 'Copy SOS Info'">Copy SOS Info</span>
                                    </button>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs leading-5 text-slate-600">
                                <p class="font-bold text-slate-900">Real-time updates</p>
                                <p class="mt-1">Map coordinates, distance, ETA, and trip status refresh automatically every 5 seconds while this page stays open.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-booking-chat :booking="$booking" />
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const pickupPoint = [{{ $booking->pickup_lat }}, {{ $booking->pickup_lng }}];
                        const dropoffPoint = [{{ $booking->dropoff_lat }}, {{ $booking->dropoff_lng }}];
                        const map = L.map('tracking-map').setView(pickupPoint, 13);
                        const trackLayers = {
                            'Street Map': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
                                attribution: 'Tiles &copy; Esri',
                                maxZoom: 19,
                            }),
                            'Satellite': L.layerGroup([
                                L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                                    attribution: 'Tiles &copy; Esri',
                                    maxZoom: 19,
                                }),
                                L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}', {
                                    maxZoom: 19,
                                }),
                                L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
                                    maxZoom: 19,
                                }),
                            ]),
                            'OpenStreetMap': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; OpenStreetMap contributors',
                                maxZoom: 19,
                            }),
                        };
                        trackLayers['Street Map'].addTo(map);
                        L.control.layers(trackLayers, null, { position: 'topright' }).addTo(map);

                        L.marker(pickupPoint).addTo(map).bindPopup('Pickup');
                        L.marker(dropoffPoint).addTo(map).bindPopup('Drop-off');
                        map.fitBounds([pickupPoint, dropoffPoint], { padding: [36, 36] });

                        let routeLine = L.polyline([pickupPoint, dropoffPoint], {
                            color: '#7c3aed',
                            weight: 5,
                            opacity: 0.85,
                        }).addTo(map);
                        const routeEndpoint = @json(route('route.directions'));
                        let lastRoadFetchAt = 0;

                        const updateRoadLine = async (fromPt, toPt) => {
                            const now = Date.now();
                            if (now - lastRoadFetchAt < 12000) return;
                            lastRoadFetchAt = now;
                            try {
                                const url = new URL(routeEndpoint, window.location.origin);
                                url.searchParams.set('from_lat', fromPt[0]);
                                url.searchParams.set('from_lng', fromPt[1]);
                                url.searchParams.set('to_lat', toPt[0]);
                                url.searchParams.set('to_lng', toPt[1]);
                                const res = await fetch(url, { headers: { Accept: 'application/json' } });
                                if (!res.ok) return;
                                const data = await res.json();
                                const primary = data.routes?.[0];
                                if (primary?.coordinates?.length) {
                                    routeLine.setLatLngs(primary.coordinates);
                                    etaDistance.textContent = `${primary.distance_km} km road`;
                                    etaMinutes.textContent = `~${primary.duration_min} min ETA`;
                                    etaBox.classList.remove('hidden');
                                    etaBox.classList.add('inline-flex');
                                }
                            } catch {}
                        };
                        updateRoadLine(pickupPoint, dropoffPoint);

                        const riderIcon = L.divIcon({
                            className: '',
                            iconSize: [34, 34],
                            iconAnchor: [17, 17],
                            html: '<div class="rider-pulse-pin"><div class="rider-pulse-pin-core"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17h2m10 0h2M7 17l2-6h6l2 6m-8-6 1-3h4l1 3"/></svg></div></div>',
                        });

                        let riderMarker = null;
                        let refreshInFlight = false;
                        const initialStatus = @json($booking->status);
                        const status = document.querySelector('[data-tracking-status]');
                        const etaBox = document.querySelector('[data-tracking-eta]');
                        const etaDistance = document.querySelector('[data-eta-distance]');
                        const etaMinutes = document.querySelector('[data-eta-minutes]');

                        const distanceKm = (a, b) => {
                            const toRad = (deg) => deg * Math.PI / 180;
                            const dLat = toRad(b[0] - a[0]);
                            const dLng = toRad(b[1] - a[1]);
                            const x = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(a[0])) * Math.cos(toRad(b[0])) * Math.sin(dLng / 2) ** 2;
                            return 6371 * 2 * Math.atan2(Math.sqrt(x), Math.sqrt(1 - x));
                        };

                        const refreshLocation = async () => {
                            if (refreshInFlight || document.hidden) return;
                            refreshInFlight = true;

                            try {
                                const response = await fetch(@json(route('customer.bookings.location', $booking)), {
                                    headers: { 'Accept': 'application/json' },
                                    cache: 'no-store',
                                });
                                const data = await response.json().catch(() => ({}));
                                // Reload when the rider picks up or completes the trip so the page reflects it.
                                if (data.status && data.status !== initialStatus) {
                                    window.location.reload();
                                    return;
                                }
                                if (response.status === 404) {
                                    status.textContent = 'Your rider has not shared a location yet. We’ll keep checking.';
                                    return;
                                }
                                if (!response.ok) throw new Error('Location could not be loaded.');
                                const point = [data.lat, data.lng];
                                if (!riderMarker) {
                                    riderMarker = L.marker(point, { icon: riderIcon }).addTo(map).bindPopup('Your rider');
                                    map.fitBounds([pickupPoint, dropoffPoint, point], { padding: [36, 36] });
                                } else {
                                    riderMarker.setLatLng(point);
                                }
                                const target = initialStatus === 'picked_up' ? dropoffPoint : pickupPoint;
                                updateRoadLine(point, target);
                                const km = distanceKm(point, target);
                                const mins = Math.max(1, Math.round((km / 22) * 60));
                                if (etaBox.classList.contains('hidden')) {
                                    etaDistance.textContent = `${km.toFixed(1)} km away`;
                                    etaMinutes.textContent = `~${mins} min ETA`;
                                    etaBox.classList.remove('hidden');
                                    etaBox.classList.add('inline-flex');
                                }
                                status.textContent = `Rider location updated ${new Date(data.updated_at).toLocaleTimeString()}`;
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

            @php
                $canEditLocation = $booking->status === 'pending'
                    && $booking->rider_id === null
                    && $booking->payment?->status !== 'paid'
                    && ! ($booking->payment_method === 'online' && $booking->payment?->paymongo_reference);
            @endphp

            @if ($booking->status === 'pending' && ! ($booking->payment_method === 'online' && $booking->payment?->status === 'paid'))
                <div x-data="{ editOpen: false }" class="border-t border-slate-100 px-5 py-4 sm:px-7">
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($canEditLocation)
                            <button
                                type="button"
                                @click="editOpen = !editOpen; if (editOpen) $nextTick(() => window.dispatchEvent(new CustomEvent('riderhub-init-edit-map')))"
                                class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50/80 px-4 py-2.5 text-sm font-bold text-indigo-700 transition hover:-translate-y-0.5 hover:bg-indigo-100"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                                <span x-text="editOpen ? 'Close editor' : 'Edit Pickup / Drop-off'"></span>
                            </button>
                        @endif
                        <form action="{{ route('customer.bookings.cancel', $booking) }}"
                              method="POST"
                              class="flex flex-wrap items-center gap-2"
                              data-confirm-title="Cancel this booking?"
                              data-confirm-message="Are you sure you want to cancel booking #{{ $booking->id }}? If you started an unpaid online checkout, it will be safely expired."
                              data-confirm-button="Yes, cancel"
                              data-confirm-tone="danger">
                            @csrf
                            <select name="cancellation_reason" aria-label="Cancellation reason" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-semibold text-slate-700 focus:border-rose-400 focus:outline-none">
                                <option value="">Reason (optional)</option>
                                @foreach (config('riderhub.cancellation_reasons', []) as $reason)
                                    <option value="{{ $reason }}">{{ $reason }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-rose-600 to-pink-600 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-rose-600/25 transition duration-200 hover:-translate-y-0.5 hover:shadow-lg active:scale-95">Cancel booking</button>
                        </form>
                    </div>

                    @if ($canEditLocation)
                        <form
                            x-show="editOpen"
                            x-cloak
                            x-transition:enter="transition ease-out duration-250"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            method="POST"
                            action="{{ route('customer.bookings.update', $booking) }}"
                            class="mt-5 space-y-4 rounded-2xl border border-indigo-100 bg-indigo-50/40 p-5"
                        >
                            @csrf
                            @method('PATCH')
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <h3 class="text-sm font-extrabold text-slate-950">Update Pickup &amp; Drop-off</h3>
                                    <p class="text-xs text-slate-500">Drag pins or click the map to adjust. Fare updates automatically.</p>
                                </div>
                                <div class="flex gap-1.5">
                                    <button type="button" data-edit-select="pickup" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white">Pin Pickup</button>
                                    <button type="button" data-edit-select="dropoff" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600">Pin Drop-off</button>
                                </div>
                            </div>

                            <div id="edit-booking-map" class="h-60 w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-100"></div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="edit_pickup_address" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Pickup address</label>
                                    <input id="edit_pickup_address" name="pickup_address" type="text" required value="{{ old('pickup_address', $booking->pickup_address) }}" class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900">
                                    <input type="hidden" id="edit_pickup_lat" name="pickup_lat" value="{{ old('pickup_lat', $booking->pickup_lat) }}">
                                    <input type="hidden" id="edit_pickup_lng" name="pickup_lng" value="{{ old('pickup_lng', $booking->pickup_lng) }}">
                                </div>
                                <div>
                                    <label for="edit_dropoff_address" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Drop-off address</label>
                                    <input id="edit_dropoff_address" name="dropoff_address" type="text" required value="{{ old('dropoff_address', $booking->dropoff_address) }}" class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900">
                                    <input type="hidden" id="edit_dropoff_lat" name="dropoff_lat" value="{{ old('dropoff_lat', $booking->dropoff_lat) }}">
                                    <input type="hidden" id="edit_dropoff_lng" name="dropoff_lng" value="{{ old('dropoff_lng', $booking->dropoff_lng) }}">
                                </div>
                            </div>

                            @if ($booking->type === 'delivery')
                                <div>
                                    <label for="edit_item_description" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Package description</label>
                                    <input id="edit_item_description" name="item_description" type="text" value="{{ old('item_description', $booking->item_description) }}" class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900">
                                </div>
                            @endif

                            <div class="flex justify-end gap-2">
                                <x-secondary-button type="button" @click="editOpen = false">Cancel</x-secondary-button>
                                <x-primary-button>Save updated location</x-primary-button>
                            </div>
                        </form>

                        <script>
                            window.addEventListener('riderhub-init-edit-map', () => {
                                const el = document.getElementById('edit-booking-map');
                                if (!el || el.dataset.initialized === 'true' || !window.L) return;
                                el.dataset.initialized = 'true';
                                const pLat = document.getElementById('edit_pickup_lat');
                                const pLng = document.getElementById('edit_pickup_lng');
                                const dLat = document.getElementById('edit_dropoff_lat');
                                const dLng = document.getElementById('edit_dropoff_lng');

                                const editMap = L.map('edit-booking-map').setView([Number(pLat.value), Number(pLng.value)], 13);
                                L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
                                    attribution: 'Tiles &copy; Esri',
                                    maxZoom: 19,
                                }).addTo(editMap);

                                const pMarker = L.marker([Number(pLat.value), Number(pLng.value)], { draggable: true }).addTo(editMap).bindPopup('Pickup');
                                const dMarker = L.marker([Number(dLat.value), Number(dLng.value)], { draggable: true }).addTo(editMap).bindPopup('Drop-off');
                                editMap.fitBounds([[Number(pLat.value), Number(pLng.value)], [Number(dLat.value), Number(dLng.value)]], { padding: [30, 30] });

                                let mode = 'pickup';
                                const sync = (which, lat, lng) => {
                                    if (which === 'pickup') { pLat.value = lat.toFixed(7); pLng.value = lng.toFixed(7); pMarker.setLatLng([lat, lng]); }
                                    else { dLat.value = lat.toFixed(7); dLng.value = lng.toFixed(7); dMarker.setLatLng([lat, lng]); }
                                };
                                pMarker.on('dragend', (e) => { const pt = e.target.getLatLng(); sync('pickup', pt.lat, pt.lng); });
                                dMarker.on('dragend', (e) => { const pt = e.target.getLatLng(); sync('dropoff', pt.lat, pt.lng); });
                                editMap.on('click', (e) => sync(mode, e.latlng.lat, e.latlng.lng));

                                document.querySelectorAll('[data-edit-select]').forEach((btn) => {
                                    btn.addEventListener('click', () => {
                                        mode = btn.dataset.editSelect;
                                        document.querySelectorAll('[data-edit-select]').forEach((b) => {
                                            b.className = b.dataset.editSelect === mode
                                                ? 'rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white'
                                                : 'rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600';
                                        });
                                    });
                                });
                            });
                        </script>
                    @endif
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
