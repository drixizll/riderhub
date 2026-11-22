<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Customer workspace</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">My bookings</h1>
                <p class="mt-1 text-sm text-slate-500">Track all your rides and deliveries in one place.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('customer.bookings.export.pdf', request()->query()) }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8Z" /></svg>
                    Export PDF History
                </a>
                <a href="{{ route('customer.bookings.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-indigo-600/25 transition hover:-translate-y-0.5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" /></svg>
                    New booking
                </a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">{{ session('error') }}</div>
        @endif

        <!-- History / Recycle Bin Switcher -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="inline-flex items-center gap-1.5 rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xs">
                <a href="{{ route('customer.bookings.index') }}"
                   class="rounded-xl px-4 py-2 text-xs font-bold transition {{ ! ($showingBin ?? false) ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
                    Booking History
                </a>
                <a href="{{ route('customer.bookings.index', ['bin' => 1]) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-bold transition {{ ($showingBin ?? false) ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>🗑 Recycle Bin</span>
                    @if (($trashedCount ?? 0) > 0)
                        <span class="rounded-full px-1.5 py-0.5 text-[10px] font-extrabold {{ ($showingBin ?? false) ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-700' }}">{{ $trashedCount }}</span>
                    @endif
                </a>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if (! ($showingBin ?? false))
                    <form method="POST"
                          action="{{ route('customer.bookings.clear-history') }}"
                          data-confirm-title="Clear completed & cancelled history?"
                          data-confirm-message="This moves all completed and cancelled bookings to your Recycle Bin (you can restore them within 30 days). Active trips are not affected."
                          data-confirm-button="Move to Recycle Bin"
                          data-confirm-tone="danger">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-xs transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700">
                            🧹 Clear History
                        </button>
                    </form>
                @elseif (($trashedCount ?? 0) > 0)
                    <form method="POST" action="{{ route('customer.bookings.restore-all') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100">
                            ↺ Restore All
                        </button>
                    </form>
                    <form method="POST"
                          action="{{ route('customer.bookings.empty-bin') }}"
                          data-confirm-title="Empty Recycle Bin?"
                          data-confirm-message="All bookings in the Recycle Bin will be permanently deleted and cannot be recovered."
                          data-confirm-button="Delete Forever"
                          data-confirm-tone="danger">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-rose-700">
                            Empty Bin
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if ($showingBin ?? false)
            <div class="rounded-2xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-xs font-medium text-amber-900">
                ⏳ <strong>30-Day Auto-Delete:</strong> Bookings in your Recycle Bin are automatically and permanently deleted after 30 days. Tap <strong>Restore</strong> anytime to bring them back to your history.
            </div>
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
                <h2 class="font-bold text-slate-950">{{ ($showingBin ?? false) ? 'Recycle Bin' : 'Booking history' }}</h2>
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
                            <div class="flex flex-wrap items-center justify-between gap-2 sm:justify-end">
                                <span class="font-bold text-slate-900">₱{{ number_format($booking->fare, 2) }}</span>
                                @if ($showingBin ?? false)
                                    @php
                                        $daysLeft = max(0, 30 - (int) $booking->deleted_at?->diffInDays(now()));
                                    @endphp
                                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700">Deletes in {{ $daysLeft }}d</span>
                                    <form method="POST" action="{{ route('customer.bookings.restore', $booking->id) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100">↺ Restore</button>
                                    </form>
                                    <form method="POST"
                                          action="{{ route('customer.bookings.force-delete', $booking->id) }}"
                                          data-confirm-title="Delete forever?"
                                          data-confirm-message="Permanently delete booking #{{ $booking->id }}? This cannot be undone."
                                          data-confirm-button="Delete forever"
                                          data-confirm-tone="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 transition hover:bg-rose-100">Delete</button>
                                    </form>
                                @else
                                    <a href="{{ route('customer.bookings.show', $booking) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">View details</a>
                                    @if (in_array($booking->status, ['completed', 'cancelled'], true))
                                        <form method="POST" action="{{ route('customer.bookings.trash', $booking) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Move to Recycle Bin" class="rounded-lg border border-slate-200 px-2.5 py-2 text-xs font-bold text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600" aria-label="Move booking to recycle bin">🗑</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $bookings->links() }}</div>
            @endif
        </section>
    </div>
</x-app-layout>
