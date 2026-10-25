<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Customer workspace</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Good to see you, {{ Str::before(Auth::user()->name, ' ') }}</h1>
                <p class="mt-1 text-sm text-slate-500">Plan a ride or send a package anywhere you need to go.</p>
            </div>
            <a href="{{ route('customer.bookings.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" /></svg>
                New booking
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5" aria-label="Booking summary">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">All bookings</p>
                <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">{{ $totalBookings }}</p>
                <p class="mt-1 text-xs text-slate-500">Your RiderHub activity</p>
            </article>
            <article class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-5 shadow-sm">
                <p class="text-sm font-medium text-indigo-700">In progress</p>
                <p class="mt-2 text-3xl font-extrabold tracking-tight text-indigo-950">{{ $activeBookings }}</p>
                <p class="mt-1 text-xs text-indigo-700/80">Accepted or on the way</p>
            </article>
            <article class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-5 shadow-sm">
                <p class="text-sm font-medium text-emerald-700">Completed</p>
                <p class="mt-2 text-3xl font-extrabold tracking-tight text-emerald-950">{{ $completedBookings }}</p>
                <p class="mt-1 text-xs text-emerald-700/80">Trips and deliveries completed</p>
            </article>
            <a href="{{ route('customer.bookings.create') }}" class="group flex min-h-36 flex-col justify-between rounded-2xl bg-gradient-to-br from-indigo-700 to-violet-700 p-5 text-white shadow-sm transition hover:shadow-lg hover:shadow-indigo-900/15">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" /></svg>
                </span>
                <span>
                    <span class="block font-bold">Where to next?</span>
                    <span class="mt-1 block text-sm text-indigo-100">Book a ride or delivery <span class="inline-block transition group-hover:translate-x-1" aria-hidden="true">→</span></span>
                </span>
            </a>
            @if ($bookingToRate)
                <a href="{{ route('customer.bookings.show', $bookingToRate) }}" class="group flex min-h-36 flex-col justify-between rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1.8 2.45 4.97 5.48.8-3.97 3.87.94 5.46L10 14.32l-4.9 2.58.94-5.46L2.07 7.57l5.48-.8L10 1.8Z" /></svg>
                    </span>
                    <span>
                        <span class="block font-bold text-amber-950">Leave a review</span>
                        <span class="mt-1 block text-sm text-amber-800">Rate your latest rider <span class="inline-block transition group-hover:translate-x-1" aria-hidden="true">→</span></span>
                    </span>
                </a>
            @else
                <article class="flex min-h-36 flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.31a1 1 0 0 1-1.42.004l-3.75-3.75a1 1 0 0 1 1.414-1.414l3.04 3.04 6.544-6.598a1 1 0 0 1 1.416-.006Z" clip-rule="evenodd" /></svg>
                    </span>
                    <span>
                        <span class="block font-bold text-slate-900">All caught up</span>
                        <span class="mt-1 block text-sm text-slate-500">Reviews appear after completed trips.</span>
                    </span>
                </article>
            @endif
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Recent bookings</h2>
                    <p class="mt-1 text-sm text-slate-500">Keep an eye on your latest rides and deliveries.</p>
                </div>
                <a href="{{ route('customer.bookings.index') }}" class="text-sm font-bold text-indigo-600 transition hover:text-indigo-800">View all bookings <span aria-hidden="true">→</span></a>
            </div>

            @if ($bookings->isEmpty())
                <div class="px-6 py-14 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5 17h2m10 0h2M7 17l2-6h6l2 6m-8-6 1-3h4l1 3M5 17a2 2 0 1 0 4 0m6 0a2 2 0 1 0 4 0" /></svg>
                    </span>
                    <h3 class="mt-4 text-base font-bold text-slate-900">Your first trip is one click away</h3>
                    <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">Create a booking and we’ll keep its status and trip details right here.</p>
                    <a href="{{ route('customer.bookings.create') }}" class="mt-5 inline-flex rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700">Create your first booking</a>
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
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 17h2m10 0h2M7 17l2-6h6l2 6m-8-6 1-3h4l1 3M5 17a2 2 0 1 0 4 0m6 0a2 2 0 1 0 4 0" /></svg>
                                    @endif
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-bold text-slate-900">{{ ucfirst($booking->type) }}</h3>
                                        <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ match ($booking->status) { 'completed' => 'bg-emerald-50 text-emerald-700', 'cancelled' => 'bg-rose-50 text-rose-700', 'accepted', 'picked_up' => 'bg-indigo-50 text-indigo-700', default => 'bg-amber-50 text-amber-700' } }}">{{ str_replace('_', ' ', ucfirst($booking->status)) }}</span>
                                    </div>
                                    <p class="mt-1 truncate text-sm text-slate-600">{{ $booking->pickup_address }} <span class="text-slate-400">→</span> {{ $booking->dropoff_address }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ $booking->created_at->format('M j, Y · g:i A') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-4 sm:justify-end">
                                <span class="font-bold text-slate-900">₱{{ number_format($booking->fare, 2) }}</span>
                                <a href="{{ route('customer.bookings.show', $booking) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Details</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
