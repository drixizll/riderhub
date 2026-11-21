@php
    $unreadNotifications = Auth::user()->unreadNotifications()->latest()->limit(6)->get();
    $unreadNotificationCount = Auth::user()->unreadNotifications()->count();
@endphp

<div x-data="{ open: false }" @click.outside="open = false" class="relative">
    <button
        type="button"
        @click="open = !open"
        :aria-expanded="open.toString()"
        class="group relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/90 bg-white text-slate-600 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 hover:shadow-md active:scale-95 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        aria-label="Notifications{{ $unreadNotificationCount ? ', '.$unreadNotificationCount.' unread' : '' }}"
    >
        <svg class="h-5 w-5 transition duration-200 group-hover:rotate-[-10deg]" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-8 12h4" />
        </svg>
        @if ($unreadNotificationCount > 0)
            <span class="absolute -right-1 -top-1 flex min-h-5 min-w-5 items-center justify-center rounded-full bg-gradient-to-r from-rose-500 to-pink-600 px-1 text-[10px] font-extrabold text-white shadow-md shadow-rose-600/35 ring-2 ring-white">
                {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-220"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-140"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        class="absolute right-0 z-50 mt-3 w-[min(23rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-2xl shadow-indigo-950/15"
    >
        <div class="relative overflow-hidden bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 px-4 py-3.5 text-white">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-extrabold tracking-tight">Notifications</h2>
                    @if ($unreadNotificationCount > 0)
                        <span class="rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-extrabold text-white backdrop-blur-sm">{{ $unreadNotificationCount }} new</span>
                    @endif
                </div>
                <a href="{{ route('notifications.index') }}" class="rounded-lg bg-white/15 px-2.5 py-1 text-[11px] font-bold text-white backdrop-blur-sm transition hover:bg-white/25">View all</a>
            </div>
        </div>

        @if ($unreadNotifications->isEmpty())
            <div class="px-6 py-10 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 shadow-inner">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </span>
                <p class="mt-3 text-sm font-bold text-slate-900">All caught up!</p>
                <p class="mt-1 text-xs text-slate-500">New booking, payment, and rider updates will appear here.</p>
            </div>
        @else
            <div class="max-h-96 divide-y divide-slate-100 overflow-y-auto">
                @foreach ($unreadNotifications as $notification)
                    @php
                        $category = $notification->data['category'] ?? 'booking';
                    @endphp
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        <button type="submit" class="group block w-full px-4 py-3.5 text-left transition hover:bg-indigo-50/70">
                            <span class="flex items-start gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $category === 'payment' ? 'bg-emerald-50 text-emerald-600' : ($category === 'rating' ? 'bg-amber-50 text-amber-600' : 'bg-indigo-50 text-indigo-600') }}">
                                    @if ($category === 'rating')
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1.8 2.45 4.97 5.48.8-3.97 3.87.94 5.46L10 14.32l-4.9 2.58.94-5.46L2.07 7.57l5.48-.8L10 1.8Z" /></svg>
                                    @elseif ($category === 'payment')
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" /><path d="M3 10h18" /></svg>
                                    @else
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11Z" /><circle cx="12" cy="10" r="2.5" /></svg>
                                    @endif
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center justify-between gap-2">
                                        <span class="truncate text-sm font-bold text-slate-900 group-hover:text-indigo-700">{{ $notification->data['title'] ?? 'RiderHub update' }}</span>
                                        <span class="h-2 w-2 shrink-0 rounded-full bg-indigo-500"></span>
                                    </span>
                                    <span class="mt-0.5 block text-xs leading-5 text-slate-600">{{ $notification->data['message'] ?? 'You have a new update.' }}</span>
                                    <span class="mt-1.5 block text-[11px] font-medium text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                                </span>
                            </span>
                        </button>
                    </form>
                @endforeach
            </div>
            <form method="POST" action="{{ route('notifications.read-all') }}" class="border-t border-slate-100 bg-slate-50/70 px-4 py-2.5 text-center">
                @csrf
                <button type="submit" class="text-xs font-bold text-indigo-600 transition hover:text-indigo-800">Mark all as read</button>
            </form>
        @endif
    </div>
</div>
