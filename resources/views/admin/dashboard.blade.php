<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Administration</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Operations overview</h1>
                <p class="mt-1 text-sm text-slate-500">Monitor bookings, customers, riders, and payment records.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.bookings.export.pdf') }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8Z" /></svg>
                    Export PDF
                </a>
                <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-indigo-600/25 transition hover:-translate-y-0.5">Manage bookings</a>
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
