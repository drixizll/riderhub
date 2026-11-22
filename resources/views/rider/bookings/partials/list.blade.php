@if ($bookings->isEmpty())
    <div class="px-6 py-14 text-center">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 17h2m10 0h2M7 17l2-6h6l2 6m-8-6 1-3h4l1 3M5 17a2 2 0 1 0 4 0m6 0a2 2 0 1 0 4 0" /></svg>
        </span>
        <h3 class="mt-4 font-bold text-slate-900">No open requests right now</h3>
        <p class="mt-2 text-sm text-slate-500">New customer bookings will show up here.</p>
    </div>
@else
    <div class="divide-y divide-slate-100">
        @foreach ($bookings as $booking)
            <article class="flex flex-col gap-5 px-5 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full {{ $booking->type === 'delivery' ? 'bg-amber-50 text-amber-700' : 'bg-indigo-50 text-indigo-700' }} px-2.5 py-1 text-xs font-bold uppercase">{{ $booking->type }}</span>
                        <span class="text-xs text-slate-400">Request #{{ $booking->id }} · {{ $booking->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-[1fr_auto_1fr] sm:items-center">
                        <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pickup</p><p class="mt-1 font-semibold text-slate-900">{{ $booking->pickup_address }}</p></div>
                        <span class="hidden text-slate-300 sm:block" aria-hidden="true">→</span>
                        <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Drop-off</p><p class="mt-1 font-semibold text-slate-900">{{ $booking->dropoff_address }}</p></div>
                    </div>
                    @php
                        $c = $booking->customer;
                        $cInitials = \Illuminate\Support\Str::of($c->name)->explode(' ')->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('') ?: 'C';
                        $cOnline = $c->last_seen_at && $c->last_seen_at->greaterThanOrEqualTo(now()->subMinutes(2));
                    @endphp
                    <div class="mt-3 flex flex-wrap items-center gap-2.5 text-sm text-slate-500">
                        <div class="relative h-7 w-7 shrink-0">
                            @if ($c->profile_photo_path)
                                <img src="{{ route('profile.photo.show', ['user' => $c->id, 'v' => $c->updated_at?->timestamp]) }}" alt="{{ $c->name }}" class="h-7 w-7 rounded-full object-cover ring-1 ring-slate-200">
                            @else
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-[10px] font-extrabold text-indigo-700">{{ $cInitials }}</span>
                            @endif
                            <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-white {{ $cOnline ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        </div>
                        <span>Customer: <span class="font-semibold text-slate-700">{{ $c->name }}</span></span>
                        <span class="text-xs font-medium {{ $cOnline ? 'text-emerald-600' : 'text-slate-400' }}">
                            {{ $cOnline ? '· Active now' : ($c->last_seen_at ? '· Offline '.$c->last_seen_at->diffForHumans() : '') }}
                        </span>
                        @if ($booking->type === 'delivery' && $booking->item_description)
                            <span class="text-slate-300">·</span><span>Package: {{ $booking->item_description }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center justify-between gap-4 border-t border-slate-100 pt-4 lg:border-0 lg:pt-0">
                    <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Fare</p><p class="mt-1 text-xl font-extrabold text-slate-950">₱{{ number_format($booking->fare, 2) }}</p></div>
                    @if ($activeBooking)
                        <button type="button" disabled class="cursor-not-allowed rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-400" title="Complete your active booking first">Already on a trip</button>
                    @else
                        <form method="POST"
                              action="{{ route('rider.bookings.accept', $booking) }}"
                              data-confirm-title="Accept this booking?"
                              data-confirm-message="Ready to accept booking #{{ $booking->id }} ({{ ucfirst($booking->type) }}) for ₱{{ number_format($booking->fare, 2) }}?"
                              data-confirm-button="Yes, accept trip"
                              data-confirm-tone="primary">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-indigo-600/25 transition duration-200 hover:-translate-y-0.5 hover:shadow-lg active:scale-95">Accept booking →</button>
                        </form>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
    @if (method_exists($bookings, 'links'))
        <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $bookings->links() }}</div>
    @endif
@endif
