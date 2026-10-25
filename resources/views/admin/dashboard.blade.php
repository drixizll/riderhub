<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Administration</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Operations overview</h1>
                <p class="mt-1 text-sm text-slate-500">Monitor bookings, customers, riders, and payment records.</p>
            </div>
            <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700">Manage bookings</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Platform metrics">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-medium text-slate-500">Total bookings</p><p class="mt-2 text-3xl font-extrabold text-slate-950">{{ number_format($totalBookings) }}</p><p class="mt-1 text-xs text-slate-500">All ride and delivery requests</p></article>
            <article class="rounded-2xl border border-amber-100 bg-amber-50/70 p-5 shadow-sm"><p class="text-sm font-medium text-amber-700">Awaiting a rider</p><p class="mt-2 text-3xl font-extrabold text-amber-950">{{ number_format($pendingBookings) }}</p><p class="mt-1 text-xs text-amber-700/80">Pending assignments</p></article>
            <article class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-5 shadow-sm"><p class="text-sm font-medium text-indigo-700">Active trips</p><p class="mt-2 text-3xl font-extrabold text-indigo-950">{{ number_format($activeBookings) }}</p><p class="mt-1 text-xs text-indigo-700/80">Accepted or picked up</p></article>
            <article class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-5 shadow-sm"><p class="text-sm font-medium text-emerald-700">Completed fare volume</p><p class="mt-2 text-3xl font-extrabold text-emerald-950">₱{{ number_format($completedRevenue, 2) }}</p><p class="mt-1 text-xs text-emerald-700/80">{{ number_format($completedBookings) }} completed bookings</p></article>
        </section>

        <section class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Platform users</h2>
                <p class="mt-1 text-sm text-slate-500">Registered accounts by service role.</p>
                <div class="mt-6 space-y-5">
                    <div>
                        <div class="flex justify-between text-sm"><span class="font-semibold text-slate-700">Customers</span><span class="font-bold text-slate-950">{{ number_format($totalCustomers) }}</span></div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-indigo-600" style="width: {{ ($totalCustomers + $totalRiders) > 0 ? max(6, round($totalCustomers / ($totalCustomers + $totalRiders) * 100)) : 0 }}%"></div></div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm"><span class="font-semibold text-slate-700">Riders</span><span class="font-bold text-slate-950">{{ number_format($totalRiders) }}</span></div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-amber-400" style="width: {{ ($totalCustomers + $totalRiders) > 0 ? max(6, round($totalRiders / ($totalCustomers + $totalRiders) * 100)) : 0 }}%"></div></div>
                    </div>
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
                            <div class="flex items-center justify-between gap-4 px-5 py-4 sm:px-6">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ $booking->customer->name }} <span class="font-normal text-slate-400">· {{ ucfirst($booking->type) }}</span></p>
                                    <p class="mt-1 truncate text-xs text-slate-500">{{ $booking->pickup_address }} → {{ $booking->dropoff_address }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-bold text-slate-900">₱{{ number_format($booking->fare, 2) }}</p>
                                    <span class="text-xs font-semibold capitalize text-slate-500">{{ str_replace('_', ' ', $booking->status) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>
        </section>

        <section class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div>
                <h2 class="font-bold text-slate-950">People directory</h2>
                <p class="mt-1 text-sm text-slate-500">Search registered customers, riders, and administrator accounts.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Browse people</a>
        </section>
    </div>
</x-app-layout>
