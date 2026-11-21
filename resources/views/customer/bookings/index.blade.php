<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Customer workspace</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">My bookings</h1>
                <p class="mt-1 text-sm text-slate-500">Track all your rides and deliveries in one place.</p>
            </div>
            <a href="{{ route('customer.bookings.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" /></svg>
                New booking
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('customer.bookings.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_1fr_auto]">
            <label class="sr-only" for="type">Service type</label>
            <select id="type" name="type" class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="">All services</option>
                <option value="ride" @selected(request('type') === 'ride')>Rides</option>
                <option value="delivery" @selected(request('type') === 'delivery')>Deliveries</option>
            </select>
            <label class="sr-only" for="status">Booking status</label>
            <select id="status" name="status" class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="">All status</option>
                @foreach (['pending', 'accepted', 'picked_up', 'completed', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700">Apply filters</button>
                @if (request()->hasAny(['type', 'status']))
                    <a href="{{ route('customer.bookings.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">Clear</a>
                @endif
            </div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 class="font-bold text-slate-950">Booking history</h2>
                <p class="mt-1 text-xs text-slate-500">{{ $bookings->total() }} {{ \Illuminate\Support\Str::plural('booking', $bookings->total()) }}</p>
            </div>
            @if ($bookings->isEmpty())
                <div class="px-6 py-14 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 17h2m10 0h2M7 17l2-6h6l2 6m-8-6 1-3h4l1 3M5 17a2 2 0 1 0 4 0m6 0a2 2 0 1 0 4 0" /></svg>
                    </span>
                    <h3 class="mt-4 font-bold text-slate-900">No bookings found</h3>
                    <p class="mt-2 text-sm text-slate-500">Create a booking or adjust the filters to see your activity.</p>
                    <a href="{{ route('customer.bookings.create') }}" class="mt-5 inline-flex rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700">Book a ride or delivery</a>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($bookings as $booking)
                        <article class="flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div class="flex min-w-0 items-start gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $booking->type === 'delivery' ? 'bg-amber-50 text-amber-600' : 'bg-indigo-50 text-indigo-600' }}">
                                    @if ($booking->type === 'delivery')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm-7.5 4.5L12 12l7.5-4.5M12 12v9" /></svg>
                                    @else
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M5 17h2m10 0h2M7 17l2-6h6l2 6m-8-6 1-3h4l1 3M5 17a2 2 0 1 0 4 0m6 0a2 2 0 1 0 4 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    @endif
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-bold capitalize text-slate-900">{{ $booking->type }}</h3>
                                        <span class="rounded-full px-2.5 py-1 text-xs font-bold capitalize {{ match ($booking->status) { 'completed' => 'bg-emerald-50 text-emerald-700', 'cancelled' => 'bg-rose-50 text-rose-700', 'accepted', 'picked_up' => 'bg-indigo-50 text-indigo-700', default => 'bg-amber-50 text-amber-700' } }}">{{ str_replace('_', ' ', $booking->status) }}</span>
                                    </div>
                                    <p class="mt-1 truncate text-sm text-slate-600">{{ $booking->pickup_address }} <span class="text-slate-400">→</span> {{ $booking->dropoff_address }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ $booking->created_at->format('M j, Y · g:i A') }} · {{ ucfirst($booking->payment_method) }} payment</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-4 sm:justify-end">
                                <span class="font-bold text-slate-900">₱{{ number_format($booking->fare, 2) }}</span>
                                <a href="{{ route('customer.bookings.show', $booking) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">View details</a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $bookings->links() }}</div>
            @endif
        </section>
    </div>
</x-app-layout>
