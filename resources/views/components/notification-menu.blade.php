@php
    $unreadNotifications = Auth::user()->unreadNotifications()->latest()->limit(5)->get();
    $unreadNotificationCount = Auth::user()->unreadNotifications()->count();
@endphp

<details class="notification-menu relative">
    <summary class="relative flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 [&::-webkit-details-marker]:hidden" aria-label="Notifications{{ $unreadNotificationCount ? ', '.$unreadNotificationCount.' unread' : '' }}">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-8 12h4" /></svg>
        @if ($unreadNotificationCount > 0)
            <span class="absolute -right-1 -top-1 flex min-h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-extrabold text-white ring-2 ring-white">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
        @endif
    </summary>

    <div class="absolute right-0 z-50 mt-3 w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <div><h2 class="text-sm font-extrabold text-slate-900">Notifications</h2><p class="text-xs text-slate-500">{{ $unreadNotificationCount }} unread</p></div>
            <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">View all</a>
        </div>
        @if ($unreadNotifications->isEmpty())
            <div class="px-5 py-8 text-center">
                <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-8 12h4" /></svg>
                </span>
                <p class="mt-3 text-sm font-semibold text-slate-700">You’re all caught up</p>
                <p class="mt-1 text-xs text-slate-500">Important trip updates will show here.</p>
            </div>
        @else
            <div class="max-h-96 overflow-y-auto divide-y divide-slate-100">
                @foreach ($unreadNotifications as $notification)
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-3 text-left transition hover:bg-indigo-50/60">
                            <span class="flex items-start gap-3">
                                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-indigo-500"></span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-bold text-slate-900">{{ $notification->data['title'] ?? 'RiderHub update' }}</span>
                                    <span class="mt-1 block text-xs leading-5 text-slate-600">{{ $notification->data['message'] ?? 'You have a new update.' }}</span>
                                    <span class="mt-1.5 block text-[11px] text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                                </span>
                            </span>
                        </button>
                    </form>
                @endforeach
            </div>
            <form method="POST" action="{{ route('notifications.read-all') }}" class="border-t border-slate-100 px-4 py-3 text-center">
                @csrf
                <button type="submit" class="text-xs font-bold text-slate-500 transition hover:text-indigo-700">Mark all as read</button>
            </form>
        @endif
    </div>
</details>
