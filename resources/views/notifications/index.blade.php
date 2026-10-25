<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Your activity</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Notifications</h1>
                <p class="mt-1 text-sm text-slate-500">Booking, trip, and rider review updates in one place.</p>
            </div>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Mark all as read</button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status') === 'notifications-read')
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">All notifications are marked as read.</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @forelse ($notifications as $notification)
                <div class="flex items-start gap-4 border-b border-slate-100 p-5 last:border-b-0 sm:p-6 {{ $notification->read_at ? '' : 'bg-indigo-50/40' }}">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ ($notification->data['category'] ?? '') === 'rating' ? 'bg-amber-50 text-amber-600' : 'bg-indigo-50 text-indigo-600' }}">
                        @if (($notification->data['category'] ?? '') === 'rating')
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1.8 2.45 4.97 5.48.8-3.97 3.87.94 5.46L10 14.32l-4.9 2.58.94-5.46L2.07 7.57l5.48-.8L10 1.8Z" /></svg>
                        @else
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-8 12h4" /></svg>
                        @endif
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-bold text-slate-900">{{ $notification->data['title'] ?? 'RiderHub update' }}</h2>
                            @unless ($notification->read_at)
                                <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-indigo-700">New</span>
                            @endunless
                        </div>
                        <p class="mt-1 text-sm leading-6 text-slate-600">{{ $notification->data['message'] ?? 'You have a new update.' }}</p>
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                            <time class="text-xs text-slate-400" datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                @csrf
                                <button type="submit" class="text-sm font-bold text-indigo-600 hover:text-indigo-800">{{ $notification->read_at ? 'Open update' : 'Read update' }} <span aria-hidden="true">→</span></button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-16 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-8 12h4" /></svg>
                    </span>
                    <h2 class="mt-4 text-base font-bold text-slate-900">No notifications yet</h2>
                    <p class="mt-1 text-sm text-slate-500">Updates about your bookings and trips will appear here.</p>
                </div>
            @endforelse
        </section>

        {{ $notifications->links() }}
    </div>
</x-app-layout>
