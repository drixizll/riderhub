@php
    $role = Auth::user()->role;
    $unreadNotificationCount = Auth::user()->unreadNotifications()->count();
    $dashboardRoute = match ($role) {
        'customer' => 'customer.dashboard',
        'rider' => 'rider.dashboard',
        'admin' => 'admin.dashboard',
        default => 'dashboard',
    };

    $navigation = match ($role) {
        'customer' => [
            ['label' => 'Overview', 'route' => 'customer.dashboard', 'active' => 'customer.dashboard', 'icon' => 'home'],
            ['label' => 'My bookings', 'route' => 'customer.bookings.index', 'active' => 'customer.bookings.*', 'icon' => 'clipboard', 'badge' => Auth::user()->bookings()->where('status', 'pending')->count()],
            ['label' => 'Book a ride', 'route' => 'customer.bookings.create', 'active' => 'customer.bookings.create', 'icon' => 'plus'],
        ],
        'rider' => [
            ['label' => 'Overview', 'route' => 'rider.dashboard', 'active' => 'rider.dashboard', 'icon' => 'home'],
            ['label' => 'Booking requests', 'route' => 'rider.bookings.index', 'active' => 'rider.bookings.*', 'icon' => 'clipboard', 'badge' => \App\Models\Booking::whereNull('rider_id')->where('status', 'pending')->count()],
        ],
        'admin' => [
            ['label' => 'Overview', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'home'],
            ['label' => 'All bookings', 'route' => 'admin.bookings.index', 'active' => 'admin.bookings.*', 'icon' => 'clipboard', 'badge' => \App\Models\Booking::where('status', 'pending')->count()],
            ['label' => 'People directory', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'icon' => 'people'],
        ],
        default => [],
    };
@endphp

<div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/40 lg:hidden" @click="sidebarOpen = false" aria-hidden="true"></div>

<aside class="fixed bottom-0 left-0 top-16 z-50 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
    <div class="flex-1 overflow-y-auto px-4 py-6">
        <p class="px-3 text-xs font-bold uppercase tracking-widest text-slate-400">Workspace</p>
        <nav class="mt-3 space-y-1" aria-label="Main sidebar">
            @foreach ($navigation as $item)
                @php($active = request()->routeIs($item['active']))
                <a href="{{ route($item['route']) }}"
                   @click="sidebarOpen = false"
                   class="{{ $active ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition">
                    @if ($item['icon'] === 'home')
                        <svg class="h-5 w-5 {{ $active ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z" /></svg>
                    @elseif ($item['icon'] === 'plus')
                        <svg class="h-5 w-5 {{ $active ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 5v14m-7-7h14" /></svg>
                    @elseif ($item['icon'] === 'people')
                        <svg class="h-5 w-5 {{ $active ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m16 0v-2a4 4 0 0 0-3-3.87M14 3.13a4 4 0 0 1 0 7.75M10 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" /></svg>
                    @else
                        <svg class="h-5 w-5 {{ $active ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" /></svg>
                    @endif
                    {{ $item['label'] }}
                    @if (($item['badge'] ?? 0) > 0)
                        <span class="ml-auto inline-flex min-w-6 items-center justify-center rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-extrabold text-amber-800">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="mt-8 border-t border-slate-100 pt-6">
            <p class="px-3 text-xs font-bold uppercase tracking-widest text-slate-400">Workspace</p>
            <a href="{{ route('notifications.index') }}"
               @click="sidebarOpen = false"
               class="{{ request()->routeIs('notifications.*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} mt-3 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition">
                <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-8 12h4" /></svg>
                Notifications
                @if ($unreadNotificationCount > 0)
                    <span class="ml-auto rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-extrabold text-rose-700">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                @endif
            </a>
            <p class="mt-6 px-3 text-xs font-bold uppercase tracking-widest text-slate-400">Account</p>
            <a href="{{ route('profile.edit') }}"
               @click="sidebarOpen = false"
               class="{{ request()->routeIs('profile.*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} mt-3 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition">
                <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="12" cy="8" r="4" stroke-width="1.8" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 21a8 8 0 0 1 16 0" /></svg>
                Account settings
            </a>
        </div>
    </div>

    <div class="border-t border-slate-100 p-4">
        <div class="mb-3">
            <x-install-app-button />
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-50 p-4">
            <p class="text-sm font-bold text-slate-900">Need a hand?</p>
            <p class="mt-1 text-xs leading-5 text-slate-600">Your RiderHub workspace keeps your trips and account in one place.</p>
            <a href="{{ route($dashboardRoute) }}" class="mt-3 inline-flex text-xs font-bold text-indigo-700 hover:text-indigo-900">Go to overview <span class="ml-1" aria-hidden="true">→</span></a>
        </div>
    </div>
</aside>
