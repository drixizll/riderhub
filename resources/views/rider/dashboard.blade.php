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
        @if (session('error'))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-800">{{ session('error') }}</div>
        @endif

        <section class="reveal-stagger grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Rider summary">
            <a href="{{ route('rider.bookings.index') }}" class="card-interactive group block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm active:scale-[0.98]">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Available requests</p>
                    <span class="text-xs font-bold text-indigo-600 transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
                </div>
                <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $availableCount }}</p>
                <p class="mt-1 text-xs text-slate-500">Waiting for a rider</p>
            </a>
            <a href="{{ $activeBooking ? '#active-booking' : route('rider.bookings.index') }}" class="card-interactive group block rounded-2xl border border-indigo-100 bg-indigo-50/70 p-5 shadow-sm active:scale-[0.98]">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-indigo-700">Assigned to you</p>
                    <span class="text-xs font-bold text-indigo-700 transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
                </div>
                <p class="mt-2 text-3xl font-extrabold text-indigo-950">{{ $assignedCount }}</p>
                <p class="mt-1 text-xs text-indigo-700/80">All-time bookings</p>
            </a>
            <a href="#rider-earnings" class="card-interactive group block rounded-2xl border border-emerald-100 bg-emerald-50/70 p-5 shadow-sm active:scale-[0.98]">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-emerald-700">Completed</p>
                    <span class="text-xs font-bold text-emerald-700 transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
                </div>
                <p class="mt-2 text-3xl font-extrabold text-emerald-950">{{ $completedCount }}</p>
                <p class="mt-1 text-xs text-emerald-700/80">Successfully delivered</p>
            </a>
            <a href="{{ route('profile.edit') }}" class="card-interactive group block rounded-2xl border border-amber-100 bg-amber-50 p-5 shadow-sm active:scale-[0.98]">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-amber-800">Rider rating</p>
                    <span class="text-xs font-bold text-amber-800 transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
                </div>
                <p class="mt-2 text-3xl font-extrabold text-amber-950">{{ $ratingAverage ?? 'New' }}@if ($ratingAverage)<span class="text-lg">/5</span>@endif</p>
                <p class="mt-1 text-xs text-amber-800/80">{{ $ratingCount }} {{ \Illuminate\Support\Str::plural('review', $ratingCount) }} from customers</p>
            </a>
        </section>

        @if ($activeBooking)
            <section id="active-booking" class="scroll-mt-24 overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm">
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

                <!-- Live Road Navigation & AI Voice Turn-by-Turn Panel -->
                <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/70">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/80 bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 px-4 py-3 text-white">
                        <div class="flex min-w-0 items-center gap-2.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white/15 backdrop-blur-sm">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0-6 6m6-6 6 6" /></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-100">AI Turn-by-Turn Navigator</p>
                                <p data-nav-instruction class="truncate text-sm font-extrabold">Calculating fastest road route…</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" data-voice-toggle class="inline-flex items-center gap-1.5 rounded-xl bg-white/15 px-3 py-1.5 text-xs font-bold text-white backdrop-blur-sm transition hover:bg-white/25" aria-pressed="true">
                                <span data-voice-label>🔊 AI Voice: ON</span>
                            </button>
                            <button type="button" data-voice-replay class="rounded-xl bg-white px-3 py-1.5 text-xs font-extrabold text-indigo-700 shadow-sm transition hover:bg-indigo-50">
                                Speak
                            </button>
                        </div>
                    </div>

                    <div id="rider-nav-map" class="h-72 w-full bg-slate-200 sm:h-80" aria-label="Live road navigation map"></div>

                    <div class="flex flex-col gap-3 border-t border-slate-200/80 bg-white px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-baseline gap-2">
                                <span data-nav-eta class="text-lg font-extrabold text-emerald-600">— min</span>
                                <span data-nav-distance class="text-sm font-semibold text-slate-500">(— km)</span>
                            </div>
                            <p class="text-xs text-slate-500">Fastest road route now · {{ $activeBooking->status === 'accepted' ? 'Heading to pickup' : 'Heading to drop-off' }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" data-toggle-steps class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" /></svg>
                                Steps
                            </button>
                        </div>
                    </div>

                    <ol data-nav-steps class="hidden max-h-52 divide-y divide-slate-100 overflow-y-auto border-t border-slate-100 bg-white px-4 py-2 text-xs text-slate-700"></ol>
                </div>
            </section>
        @else
            <section class="rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-50 to-white p-6 sm:p-8">
                <p class="text-sm font-bold uppercase tracking-wider text-indigo-700">You’re available</p>
                <h2 class="mt-2 text-xl font-extrabold text-slate-950">Choose a booking request to get started</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Accept one pending request at a time. Once it’s accepted, update the trip status and share your live location with the customer.</p>
            </section>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1.4fr_0.6fr]">
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

            <div class="space-y-4">
                <x-weather-widget title="Tagoloan Road Weather" :lat="$activeBooking?->pickup_lat ?? 8.5378" :lng="$activeBooking?->pickup_lng ?? 124.7536" />

                <section id="rider-earnings" class="scroll-mt-24 card-interactive rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <h2 class="text-sm font-bold text-slate-950">7-Day Completed Earnings</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Your daily completed fare totals (₱).</p>
                        </div>
                        <a href="{{ route('rider.earnings.export.pdf') }}" target="_blank" class="rounded-lg bg-indigo-50 px-2.5 py-1.5 text-[11px] font-bold text-indigo-700 transition hover:bg-indigo-100">PDF Report</a>
                    </div>
                    <div class="relative mt-4 h-48">
                        <canvas id="rider-earnings-chart"></canvas>
                    </div>
                </section>

                <!-- Interactive Daily Earnings Goal Tracker Card -->
                <section x-data="{ goal: 1000, today: {{ (float) ($todayEarnings ?? 0) }}, total: {{ (float) ($totalEarnings ?? 0) }} }"
                         class="card-interactive rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50/80 via-white to-violet-50/60 p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[11px] font-extrabold uppercase tracking-wider text-indigo-600">Daily Goal Tracker</p>
                            <p class="mt-1 text-xl font-extrabold text-slate-950">₱<span x-text="today.toFixed(2)"></span> <span class="text-xs font-semibold text-slate-500">/ ₱<span x-text="goal"></span> today</span></p>
                        </div>
                        <select x-model.number="goal" aria-label="Daily earnings goal" class="rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700">
                            <option value="500">Goal: ₱500</option>
                            <option value="1000">Goal: ₱1,000</option>
                            <option value="1500">Goal: ₱1,500</option>
                            <option value="2500">Goal: ₱2,500</option>
                        </select>
                    </div>
                    <div class="mt-3 h-2.5 w-full overflow-hidden rounded-full bg-slate-200/80">
                        <div class="h-full rounded-full bg-gradient-to-r from-indigo-600 via-violet-500 to-emerald-500 transition-all duration-500"
                             :style="`width: ${Math.min(100, Math.round((today / Math.max(1, goal)) * 100))}%`"></div>
                    </div>
                    <div class="mt-2.5 flex items-center justify-between text-xs text-slate-500">
                        <span x-text="`${Math.min(100, Math.round((today / Math.max(1, goal)) * 100))}% of daily target`"></span>
                        <span class="font-bold text-indigo-700">All-time: ₱{{ number_format((float) ($totalEarnings ?? 0), 2) }}</span>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Chart) return;
            const isDark = document.documentElement.dataset.theme === 'dark';
            const tickColor = isDark ? '#94a3b8' : '#64748b';
            const gridColor = isDark ? 'rgba(51,65,85,0.45)' : 'rgba(226,232,240,0.8)';
            const data = @json($earningsChart);
            const canvas = document.getElementById('rider-earnings-chart');
            if (!canvas) return;
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Earnings (₱)',
                        data: data.earnings,
                        backgroundColor: 'rgba(16, 185, 129, 0.85)',
                        borderRadius: 8,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: tickColor }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { color: tickColor, callback: (v) => `₱${v}` }, grid: { color: gridColor } },
                    },
                },
            });
        });
    </script>

    @if ($activeBooking)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const button = document.querySelector('[data-location-button]');
                const status = document.querySelector('[data-location-status]');
                const navInstructionEl = document.querySelector('[data-nav-instruction]');
                const navEtaEl = document.querySelector('[data-nav-eta]');
                const navDistanceEl = document.querySelector('[data-nav-distance]');
                const navStepsEl = document.querySelector('[data-nav-steps]');
                const voiceToggleBtn = document.querySelector('[data-voice-toggle]');
                const voiceLabelEl = document.querySelector('[data-voice-label]');
                const voiceReplayBtn = document.querySelector('[data-voice-replay]');
                const stepsToggleBtn = document.querySelector('[data-toggle-steps]');

                const pickupPoint = [{{ $activeBooking->pickup_lat }}, {{ $activeBooking->pickup_lng }}];
                const dropoffPoint = [{{ $activeBooking->dropoff_lat }}, {{ $activeBooking->dropoff_lng }}];
                const bookingStatus = @json($activeBooking->status);
                const routeEndpoint = @json(route('route.directions'));

                let voiceEnabled = true;
                let lastSpoken = '';
                let latestInstruction = 'Follow the highlighted route.';
                let lastRouteFetchAt = 0;
                let watchId = null;
                let lastSentAt = 0;
                let updateInFlight = false;

                const speakDirection = (text, force = false) => {
                    if (!('speechSynthesis' in window) || (!voiceEnabled && !force) || !text) return;
                    if (!force && text === lastSpoken) return;
                    lastSpoken = text;
                    try {
                        window.speechSynthesis.cancel();
                        const utterance = new SpeechSynthesisUtterance(text);
                        utterance.lang = 'en-PH';
                        utterance.rate = 1.02;
                        window.speechSynthesis.speak(utterance);
                    } catch {}
                };

                voiceToggleBtn?.addEventListener('click', () => {
                    voiceEnabled = !voiceEnabled;
                    voiceToggleBtn.setAttribute('aria-pressed', voiceEnabled.toString());
                    voiceLabelEl.textContent = voiceEnabled ? '🔊 AI Voice: ON' : '🔇 AI Voice: OFF';
                    if (!voiceEnabled && 'speechSynthesis' in window) window.speechSynthesis.cancel();
                });

                voiceReplayBtn?.addEventListener('click', () => speakDirection(latestInstruction, true));
                stepsToggleBtn?.addEventListener('click', () => navStepsEl?.classList.toggle('hidden'));

                // Initialize Rider Navigation Map
                const navMap = L.map('rider-nav-map').setView(pickupPoint, 13);
                const navLayers = {
                    'Street Map': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
                        attribution: 'Tiles &copy; Esri',
                        maxZoom: 19,
                    }),
                    'Satellite': L.layerGroup([
                        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                            attribution: 'Tiles &copy; Esri',
                            maxZoom: 19,
                        }),
                        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19 }),
                        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19 }),
                    ]),
                    'OpenStreetMap': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 19,
                    }),
                };
                navLayers['Street Map'].addTo(navMap);
                L.control.layers(navLayers, null, { position: 'topright' }).addTo(navMap);

                L.marker(pickupPoint).addTo(navMap).bindPopup('Pickup');
                L.marker(dropoffPoint).addTo(navMap).bindPopup('Drop-off');

                const riderIcon = L.divIcon({
                    className: '',
                    iconSize: [34, 34],
                    iconAnchor: [17, 17],
                    html: '<div class="rider-pulse-pin"><div class="rider-pulse-pin-core"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 17h2m10 0h2M7 17l2-6h6l2 6m-8-6 1-3h4l1 3"/></svg></div></div>',
                });

                let riderMarker = null;
                let routeGroup = L.layerGroup().addTo(navMap);

                const drawRoadRoute = async (fromPt, toPt, announce = false) => {
                    const now = Date.now();
                    if (!announce && now - lastRouteFetchAt < 12000) return;
                    lastRouteFetchAt = now;

                    try {
                        const url = new URL(routeEndpoint, window.location.origin);
                        url.searchParams.set('from_lat', fromPt[0]);
                        url.searchParams.set('from_lng', fromPt[1]);
                        url.searchParams.set('to_lat', toPt[0]);
                        url.searchParams.set('to_lng', toPt[1]);

                        const res = await fetch(url, { headers: { Accept: 'application/json' } });
                        if (!res.ok) return;
                        const data = await res.json();
                        const routes = data.routes || [];
                        if (!routes.length) return;

                        routeGroup.clearLayers();

                        // Draw alternate route in muted gray first (like Google Maps)
                        if (routes[1] && routes[1].coordinates?.length) {
                            const alt = routes[1];
                            L.polyline(alt.coordinates, { color: '#94a3b8', weight: 5, opacity: 0.75 }).addTo(routeGroup);
                            const midAlt = alt.coordinates[Math.floor(alt.coordinates.length / 2)];
                            if (midAlt) {
                                L.marker(midAlt, {
                                    interactive: false,
                                    icon: L.divIcon({
                                        className: '',
                                        html: `<div style="background:#fff;color:#334155;font-size:11px;font-weight:700;padding:3px 8px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.18);white-space:nowrap;">${alt.duration_min} min</div>`,
                                    }),
                                }).addTo(routeGroup);
                            }
                        }

                        // Draw fastest primary route in bold Royal Violet/Blue
                        const primary = routes[0];
                        if (primary.coordinates?.length) {
                            const primaryLine = L.polyline(primary.coordinates, { color: '#7c3aed', weight: 6, opacity: 0.92 }).addTo(routeGroup);
                            const mid = primary.coordinates[Math.floor(primary.coordinates.length / 2)];
                            if (mid) {
                                L.marker(mid, {
                                    interactive: false,
                                    icon: L.divIcon({
                                        className: '',
                                        html: `<div style="background:#6d28d9;color:#fff;font-size:11px;font-weight:800;padding:4px 9px;border-radius:8px;box-shadow:0 6px 16px rgba(109,40,217,0.45);white-space:nowrap;">${primary.duration_min} min</div>`,
                                    }),
                                }).addTo(routeGroup);
                            }
                            if (!riderMarker) {
                                navMap.fitBounds(primaryLine.getBounds(), { padding: [36, 36] });
                            }
                        }

                        navEtaEl.textContent = `${primary.duration_min} min`;
                        navDistanceEl.textContent = `(${primary.distance_km} km)`;

                        const steps = primary.steps || [];
                        if (steps.length) {
                            latestInstruction = `${steps[0].instruction}. Estimated arrival in ${primary.duration_min} minutes.`;
                            navInstructionEl.textContent = steps[0].instruction;
                            navStepsEl.innerHTML = steps.map((s, idx) =>
                                `<li class="flex items-start gap-2.5 py-2"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-[10px] font-extrabold text-indigo-700">${idx + 1}</span><span>${s.instruction}</span></li>`
                            ).join('');
                            if (announce) speakDirection(latestInstruction);
                        }
                    } catch {}
                };

                // Initial preview route from Pickup -> Drop-off immediately on load
                drawRoadRoute(pickupPoint, dropoffPoint, false);

                button.addEventListener('click', () => {
                    if (watchId !== null) {
                        navigator.geolocation.clearWatch(watchId);
                        watchId = null;
                        button.textContent = 'Start live location';
                        status.textContent = 'Location sharing stopped.';
                        if ('speechSynthesis' in window) window.speechSynthesis.cancel();
                        return;
                    }

                    if (!navigator.geolocation) {
                        status.textContent = 'Location sharing is not supported by this browser.';
                        return;
                    }

                    button.textContent = 'Stop live location';
                    status.textContent = 'Waiting for location permission… Keep this page open during the trip.';
                    speakDirection('Starting RiderHub live navigation.', true);

                    watchId = navigator.geolocation.watchPosition(async (position) => {
                        const livePt = [position.coords.latitude, position.coords.longitude];
                        if (!riderMarker) {
                            riderMarker = L.marker(livePt, { icon: riderIcon }).addTo(navMap).bindPopup('Your live location');
                            navMap.setView(livePt, 15);
                        } else {
                            riderMarker.setLatLng(livePt);
                        }

                        const destinationPt = bookingStatus === 'picked_up' ? dropoffPoint : pickupPoint;
                        drawRoadRoute(livePt, destinationPt, true);

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
