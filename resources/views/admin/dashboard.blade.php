<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <x-realtime-greeting :name="Auth::user()->name" role="admin" />
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.bookings.export.pdf') }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8Z" /></svg>
                    Export PDF
                </a>
                <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-indigo-600/25 transition hover:-translate-y-0.5">Manage bookings</a>
                <form method="POST"
                      action="{{ route('admin.reset-test-data') }}"
                      data-confirm-title="Reset all test bookings?"
                      data-confirm-message="This clears all bookings, payments, chats, ratings, and notifications so the next booking starts fresh at #1. All user accounts (Admin, Rider, Customer) stay safe."
                      data-confirm-button="Yes, reset to #1"
                      data-confirm-tone="danger">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-sm font-bold text-rose-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-rose-100">
                        Reset Test Data
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-800">{{ session('error') }}</div>
        @endif

        <section class="reveal-stagger grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Platform metrics">
            <a href="{{ route('admin.bookings.index') }}" class="card-interactive group block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between"><p class="text-sm font-medium text-slate-500">Total bookings</p><span class="text-xs font-bold text-indigo-600 transition group-hover:translate-x-0.5">→</span></div>
                <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ number_format($totalBookings) }}</p>
                <p class="mt-1 text-xs text-slate-500">All ride and delivery requests</p>
            </a>
            <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="card-interactive group block rounded-2xl border border-amber-100 bg-amber-50/70 p-5 shadow-sm">
                <div class="flex items-center justify-between"><p class="text-sm font-medium text-amber-700">Awaiting a rider</p><span class="text-xs font-bold text-amber-700 transition group-hover:translate-x-0.5">→</span></div>
                <p class="mt-2 text-3xl font-extrabold text-amber-950">{{ number_format($pendingBookings) }}</p>
                <p class="mt-1 text-xs text-amber-700/80">Pending assignments</p>
            </a>
            <a href="{{ route('admin.bookings.index', ['status' => 'accepted']) }}" class="card-interactive group block rounded-2xl border border-indigo-100 bg-indigo-50/70 p-5 shadow-sm">
                <div class="flex items-center justify-between"><p class="text-sm font-medium text-indigo-700">Active trips</p><span class="text-xs font-bold text-indigo-700 transition group-hover:translate-x-0.5">→</span></div>
                <p class="mt-2 text-3xl font-extrabold text-indigo-950">{{ number_format($activeBookings) }}</p>
                <p class="mt-1 text-xs text-indigo-700/80">Accepted or picked up</p>
            </a>
            <a href="{{ route('admin.bookings.index', ['status' => 'completed']) }}" class="card-interactive group block rounded-2xl border border-emerald-100 bg-emerald-50/70 p-5 shadow-sm">
                <div class="flex items-center justify-between"><p class="text-sm font-medium text-emerald-700">Completed fare volume</p><span class="text-xs font-bold text-emerald-700 transition group-hover:translate-x-0.5">→</span></div>
                <p class="mt-2 text-3xl font-extrabold text-emerald-950">₱{{ number_format($completedRevenue, 2) }}</p>
                <p class="mt-1 text-xs text-emerald-700/80">{{ number_format($completedBookings) }} completed bookings</p>
            </a>
        </section>

        <!-- Live Fleet Command Center Map -->
        <section class="overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm" aria-label="Live Fleet Command Center">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 px-5 py-4 text-white sm:px-6">
                <div class="flex items-center gap-3">
                    <span class="relative flex h-3 w-3 shrink-0">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-300 opacity-75"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-400"></span>
                    </span>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-widest text-indigo-100">Misamis Oriental Operations</p>
                        <h2 class="text-lg font-extrabold text-white">Live Fleet Command Center</h2>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                    <span id="fleet-count-pill" class="rounded-full bg-white/15 px-3 py-1 text-indigo-50 backdrop-blur-sm">Syncing live fleet…</span>
                    <span id="fleet-updated-pill" class="rounded-full bg-white/10 px-2.5 py-1 text-indigo-100">10s auto-refresh</span>
                </div>
            </div>
            <div id="admin-fleet-map" class="h-80 w-full bg-slate-100 sm:h-96" aria-label="Live map of active RiderHub trips and riders"></div>
            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 bg-slate-50 px-5 py-2.5 text-xs text-slate-600 sm:px-6">
                <div class="flex flex-wrap items-center gap-4">
                    <span class="inline-flex items-center gap-1.5 font-semibold"><span class="h-2.5 w-2.5 rounded-full bg-indigo-600"></span>Active Rider GPS</span>
                    <span class="inline-flex items-center gap-1.5 font-semibold"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Active Trip Pickup</span>
                    <span class="inline-flex items-center gap-1.5 font-semibold"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>Pending Request</span>
                </div>
                <span class="text-[11px] text-slate-400">Click any pin for booking &amp; rider details</span>
            </div>
        </section>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const mapEl = document.getElementById('admin-fleet-map');
                if (!mapEl || !window.L) return;

                const map = L.map('admin-fleet-map').setView([8.5378, 124.7536], 12);
                L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri',
                    maxZoom: 19,
                }).addTo(map);

                const layerGroup = L.layerGroup().addTo(map);
                const countPill = document.getElementById('fleet-count-pill');
                const updatedPill = document.getElementById('fleet-updated-pill');

                const dotIcon = (color) => L.divIcon({
                    className: 'riderhub-fleet-dot',
                    html: `<div style="width:14px;height:14px;border-radius:9999px;background:${color};border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.35);"></div>`,
                    iconSize: [14, 14],
                    iconAnchor: [7, 7],
                });

                const refreshFleet = async () => {
                    try {
                        const res = await fetch(@js(route('admin.fleet.live')), { headers: { 'Accept': 'application/json' } });
                        if (!res.ok) return;
                        const data = await res.json();
                        layerGroup.clearLayers();

                        const trips = data.trips || [];
                        trips.forEach((t) => {
                            const isPending = t.status === 'pending';
                            const popup = `<div style="font-size:12px;line-height:1.45;"><strong>#${t.id} · ${t.type}</strong> (${t.status.replace('_', ' ')})<br>Customer: ${t.customer}<br>${t.rider ? `Rider: ${t.rider}${t.plate ? ` (${t.plate})` : ''}<br>` : ''}Route: ${t.pickup_address} → ${t.dropoff_address}<br><strong>${t.fare}</strong></div>`;

                            L.marker([t.pickup_lat, t.pickup_lng], { icon: dotIcon(isPending ? '#f59e0b' : '#10b981') })
                                .bindPopup(popup)
                                .addTo(layerGroup);

                            if (!isPending && t.rider_lat && t.rider_lng) {
                                L.marker([t.rider_lat, t.rider_lng], { icon: dotIcon('#4f46e5') })
                                    .bindPopup(`<strong>🏍 Live Rider: ${t.rider || 'Rider'}</strong><br>Trip #${t.id} · ${t.fare}`)
                                    .addTo(layerGroup);
                                L.polyline([[t.rider_lat, t.rider_lng], [t.dropoff_lat, t.dropoff_lng]], { color: '#6366f1', weight: 3, dashArray: '6,6', opacity: 0.7 }).addTo(layerGroup);
                            }
                        });

                        if (countPill) countPill.textContent = `${trips.length} Live Trip(s) · ${data.online_riders ?? 0} Online Rider(s)`;
                        if (updatedPill && data.timestamp_pht) updatedPill.textContent = `Updated ${data.timestamp_pht}`;
                    } catch {}
                };

                refreshFleet();
                setInterval(refreshFleet, 10000);
            });
        </script>

        <section class="grid gap-6 lg:grid-cols-[1.35fr_0.65fr]" aria-label="Platform analytics">
            <article class="card-interactive rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">7-Day Bookings &amp; Fare Volume</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Daily trip requests and completed fare totals across Tagoloan.</p>
                    </div>
                    <a href="{{ route('admin.bookings.index') }}" class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700 transition hover:bg-indigo-100">View report →</a>
                </div>
                <div class="relative mt-5 h-64">
                    <canvas id="admin-daily-chart"></canvas>
                </div>
            </article>

            <article class="card-interactive rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Booking Status Breakdown</h2>
                <p class="mt-0.5 text-xs text-slate-500">Click any slice to filter bookings by status.</p>
                <div class="relative mt-5 h-64">
                    <canvas id="admin-status-chart"></canvas>
                </div>
            </article>
        </section>

        <section class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Platform users</h2>
                <p class="mt-1 text-sm text-slate-500">Registered accounts by service role.</p>
                <div class="mt-6 space-y-5">
                    <a href="{{ route('admin.users.index', ['role' => 'customer']) }}" class="block rounded-xl p-2 -mx-2 transition hover:bg-slate-50">
                        <div class="flex justify-between text-sm"><span class="font-semibold text-slate-700">Customers →</span><span class="font-bold text-slate-950">{{ number_format($totalCustomers) }}</span></div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-indigo-600" style="width: {{ ($totalCustomers + $totalRiders) > 0 ? max(6, round($totalCustomers / ($totalCustomers + $totalRiders) * 100)) : 0 }}%"></div></div>
                    </a>
                    <a href="{{ route('admin.users.index', ['role' => 'rider']) }}" class="block rounded-xl p-2 -mx-2 transition hover:bg-slate-50">
                        <div class="flex justify-between text-sm"><span class="font-semibold text-slate-700">Riders →</span><span class="font-bold text-slate-950">{{ number_format($totalRiders) }}</span></div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-amber-400" style="width: {{ ($totalCustomers + $totalRiders) > 0 ? max(6, round($totalRiders / ($totalCustomers + $totalRiders) * 100)) : 0 }}%"></div></div>
                    </a>
                </div>
                <div class="mt-6 rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                    Completed fare volume is calculated from completed bookings; it is not a payout or payment-provider reconciliation.
                </div>
            </article>

            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5 sm:px-6">
                    <div><h2 class="text-lg font-bold text-slate-950">Latest bookings</h2><p class="mt-1 text-sm text-slate-500">A real-time snapshot of recent platform activity.</p></div>
                    <a href="{{ route('admin.bookings.index') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-800">View all</a>
                </div>
                @if ($recentBookings->isEmpty())
                    <div class="px-6 py-10 text-center text-sm text-slate-500">No bookings have been created yet.</div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($recentBookings as $booking)
                            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ $booking->customer->name }} <span class="font-normal text-slate-400">· {{ ucfirst($booking->type) }}</span></p>
                                    <p class="mt-1 truncate text-xs text-slate-500">{{ $booking->pickup_address }} → {{ $booking->dropoff_address }}</p>
                                </div>
                                <div class="flex shrink-0 flex-wrap items-center gap-3 sm:justify-end">
                                    @if ($booking->status === 'pending' && ! $booking->rider_id && isset($availableRiders) && $availableRiders->isNotEmpty())
                                        <form method="POST" action="{{ route('admin.bookings.assign', $booking) }}" class="flex items-center gap-1.5">
                                            @csrf
                                            <select name="rider_id" required aria-label="Assign rider" class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                                <option value="">Dispatch rider…</option>
                                                @foreach ($availableRiders as $availRider)
                                                    <option value="{{ $availRider->id }}">{{ $availRider->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="rounded-lg bg-indigo-600 px-2.5 py-1 text-xs font-bold text-white transition hover:bg-indigo-700">Assign</button>
                                        </form>
                                    @endif
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-slate-900">₱{{ number_format($booking->fare, 2) }}</p>
                                        <span class="text-xs font-semibold capitalize text-slate-500">{{ str_replace('_', ' ', $booking->status) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>
        </section>

        @if (isset($topRiders) && $topRiders->isNotEmpty())
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">Top Performing Riders Leaderboard</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Ranked by completed trips, fare volume, and customer rating across Misamis Oriental.</p>
                    </div>
                    <a href="{{ route('admin.users.index', ['role' => 'rider']) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">All riders →</a>
                </div>
                <div class="grid divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0 lg:grid-cols-3">
                    @foreach ($topRiders->take(3) as $idx => $topRider)
                        <div class="flex items-center justify-between gap-3 p-5">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-xs font-extrabold text-indigo-700">#{{ $idx + 1 }}</span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ $topRider->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $topRider->completed_trips_count }} completed · ★ {{ $topRider->rating_avg ? number_format((float) $topRider->rating_avg, 1) : 'New' }}</p>
                                </div>
                            </div>
                            <span class="shrink-0 rounded-xl bg-emerald-50 px-3 py-1.5 text-xs font-extrabold text-emerald-700">₱{{ number_format((float) ($topRider->completed_fare_sum ?? 0), 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Admin Activity & Audit Log -->
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-label="Admin audit log">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-base font-bold text-slate-950">🛡️ Admin Activity &amp; Audit Log</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Accountability trail of recent dispatches, KYC updates, and admin actions (PHT).</p>
                </div>
                <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-bold text-indigo-700">Live Trail</span>
            </div>
            @if (($recentActivityLogs ?? collect())->isEmpty())
                <div class="px-6 py-8 text-center text-xs text-slate-400">No admin actions recorded yet. Dispatches, account edits, and resets will appear here.</div>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($recentActivityLogs as $log)
                        <li class="flex flex-col gap-1 px-5 py-3.5 text-xs sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-md bg-slate-100 px-2 py-0.5 font-mono text-[10px] font-extrabold text-indigo-700">{{ $log->action }}</span>
                                <span class="font-semibold text-slate-800">{{ $log->description }}</span>
                                <span class="text-slate-400">by {{ $log->admin?->name ?? 'Admin' }}</span>
                            </div>
                            <span class="shrink-0 text-[11px] font-medium text-slate-400">{{ $log->created_at?->timezone('Asia/Manila')->format('M j, g:i A') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div>
                <h2 class="font-bold text-slate-950">People directory</h2>
                <p class="mt-1 text-sm text-slate-500">Search registered customers, riders, and administrator accounts.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Browse people</a>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Chart) return;
            const isDark = document.documentElement.dataset.theme === 'dark';
            const tickColor = isDark ? '#94a3b8' : '#64748b';
            const gridColor = isDark ? 'rgba(51,65,85,0.45)' : 'rgba(226,232,240,0.8)';
            const dailyData = @json($dailyChart);
            const statusData = @json($statusChart);
            const bookingsBaseUrl = @json(route('admin.bookings.index'));

            const dailyCanvas = document.getElementById('admin-daily-chart');
            if (dailyCanvas) {
                new Chart(dailyCanvas, {
                    type: 'bar',
                    data: {
                        labels: dailyData.labels,
                        datasets: [
                            {
                                label: 'Bookings',
                                data: dailyData.bookings,
                                backgroundColor: 'rgba(79, 70, 229, 0.85)',
                                borderRadius: 8,
                                yAxisID: 'y',
                            },
                            {
                                type: 'line',
                                label: 'Completed Fare (₱)',
                                data: dailyData.revenue,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.15)',
                                tension: 0.35,
                                fill: true,
                                yAxisID: 'y1',
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { labels: { color: tickColor, usePointStyle: true } } },
                        scales: {
                            x: { ticks: { color: tickColor }, grid: { display: false } },
                            y: { beginAtZero: true, ticks: { precision: 0, color: tickColor }, grid: { color: gridColor } },
                            y1: { position: 'right', beginAtZero: true, ticks: { color: '#10b981', callback: (v) => `₱${v}` }, grid: { display: false } },
                        },
                    },
                });
            }

            const statusCanvas = document.getElementById('admin-status-chart');
            if (statusCanvas) {
                new Chart(statusCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: statusData.labels,
                        datasets: [{
                            data: statusData.counts,
                            backgroundColor: ['#f59e0b', '#6366f1', '#0ea5e9', '#10b981', '#f43f5e'],
                            borderWidth: 0,
                            hoverOffset: 8,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '64%',
                        plugins: { legend: { position: 'bottom', labels: { color: tickColor, usePointStyle: true, padding: 14 } } },
                        onClick: (_, elements) => {
                            if (!elements.length) return;
                            const key = statusData.keys[elements[0].index];
                            if (key) window.location.href = `${bookingsBaseUrl}?status=${encodeURIComponent(key)}`;
                        },
                    },
                });
            }
        });
    </script>
</x-app-layout>
