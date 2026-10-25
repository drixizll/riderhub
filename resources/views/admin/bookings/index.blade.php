<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold text-indigo-600">Administration</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">All bookings</h1>
            <p class="mt-1 text-sm text-slate-500">Search and review customer bookings across the platform.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="grid gap-3 md:grid-cols-[1fr_180px_180px_auto]">
                <label class="sr-only" for="search">Search bookings</label>
                <input id="search" name="search" type="search" value="{{ request('search') }}" placeholder="Search customer or address"
                       class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <label class="sr-only" for="status">Filter by status</label>
                <select id="status" name="status" class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All statuses</option>
                    @foreach (['pending', 'accepted', 'picked_up', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <label class="sr-only" for="type">Filter by service type</label>
                <select id="type" name="type" class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All services</option>
                    <option value="ride" @selected(request('type') === 'ride')>Ride</option>
                    <option value="delivery" @selected(request('type') === 'delivery')>Delivery</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700">Filter</button>
                    @if (request()->hasAny(['search', 'status', 'type']))
                        <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Clear</a>
                    @endif
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                <div><h2 class="font-bold text-slate-950">Booking records</h2><p class="mt-1 text-xs text-slate-500">{{ $bookings->total() }} results</p></div>
            </div>
            @if ($bookings->isEmpty())
                <div class="px-6 py-14 text-center">
                    <h3 class="font-bold text-slate-900">No matching bookings</h3>
                    <p class="mt-2 text-sm text-slate-500">Try adjusting your search or filters.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                <th scope="col" class="px-5 py-3 sm:px-6">Booking</th>
                                <th scope="col" class="px-5 py-3">Customer / rider</th>
                                <th scope="col" class="px-5 py-3">Status</th>
                                <th scope="col" class="px-5 py-3">Payment</th>
                                <th scope="col" class="px-5 py-3 text-right sm:px-6">Fare</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($bookings as $booking)
                                <tr class="align-top">
                                    <td class="max-w-sm px-5 py-4 sm:px-6">
                                        <p class="text-xs font-semibold text-slate-400">#{{ $booking->id }} · {{ $booking->created_at->format('M j, Y g:i A') }}</p>
                                        <p class="mt-1 text-sm font-bold capitalize text-slate-900">{{ $booking->type }}</p>
                                        <p class="mt-1 truncate text-xs text-slate-500">{{ $booking->pickup_address }} → {{ $booking->dropoff_address }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm">
                                        <p class="font-semibold text-slate-800">{{ $booking->customer->name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Rider: {{ $booking->rider?->name ?? 'Unassigned' }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold capitalize {{ match ($booking->status) { 'completed' => 'bg-emerald-50 text-emerald-700', 'cancelled' => 'bg-rose-50 text-rose-700', 'accepted', 'picked_up' => 'bg-indigo-50 text-indigo-700', default => 'bg-amber-50 text-amber-700' } }}">{{ str_replace('_', ' ', $booking->status) }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-sm">
                                        <p class="font-semibold capitalize text-slate-700">{{ $booking->payment?->method ?? $booking->payment_method }}</p>
                                        <p class="mt-1 text-xs capitalize text-slate-500">{{ $booking->payment?->status ?? 'Not recorded' }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-bold text-slate-900 sm:px-6">₱{{ number_format($booking->fare, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $bookings->links() }}</div>
            @endif
        </section>
    </div>
</x-app-layout>
