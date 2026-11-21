@php
    $role = Auth::user()->role;
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

<div x-show="sidebarOpen" x-transition.opacity.duration.250ms class="fixed inset-0 z-40 bg-slate-950/45 backdrop-blur-xs lg:hidden" @click="sidebarOpen = false" aria-hidden="true"></div>

<aside class="fixed bottom-0 left-0 top-16 z-50 flex w-64 -translate-x-full flex-col border-r border-slate-200/80 bg-white/95 backdrop-blur-md transition-transform duration-300 ease-out lg:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full'">
    <div class="flex-1 overflow-y-auto px-4 py-6">
        <p class="px-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">Workspace</p>
        <nav class="mt-3 space-y-1.5" aria-label="Main sidebar">
            @foreach ($navigation as $item)
                @php
                    $active = request()->routeIs($item['active']);
                @endphp
                <a href="{{ route($item['route']) }}"
                   @click="sidebarOpen = false"
                   class="{{ $active
                       ? 'bg-gradient-to-r from-indigo-50 via-indigo-50/80 to-violet-50/60 text-indigo-700 shadow-xs ring-1 ring-inset ring-indigo-200/80'
                       : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-200 hover:translate-x-0.5 active:scale-[0.98]">
                    @if ($active)
                        <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-gradient-to-b from-indigo-500 to-violet-600" aria-hidden="true"></span>
                    @endif
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-all duration-200 {{ $active ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'bg-slate-100 text-slate-500 group-hover:scale-105 group-hover:bg-indigo-50 group-hover:text-indigo-600' }}">
                        @if ($item['icon'] === 'home')
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z" /></svg>
                        @elseif ($item['icon'] === 'plus')
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" /></svg>
                        @elseif ($item['icon'] === 'people')
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m16 0v-2a4 4 0 0 0-3-3.87M14 3.13a4 4 0 0 1 0 7.75M10 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" /></svg>
                        @else
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" /></svg>
                        @endif
                    </span>
                    <span class="truncate">{{ $item['label'] }}</span>
                    @if (($item['badge'] ?? 0) > 0)
                        <span class="ml-auto inline-flex min-w-6 items-center justify-center rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-extrabold text-amber-800 shadow-xs">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="mt-8 border-t border-slate-100 pt-6">
            <p class="px-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">Account</p>
            @php
                $profileActive = request()->routeIs('profile.*');
            @endphp
            <a href="{{ route('profile.edit') }}"
               @click="sidebarOpen = false"
               class="{{ $profileActive
                   ? 'bg-gradient-to-r from-indigo-50 via-indigo-50/80 to-violet-50/60 text-indigo-700 shadow-xs ring-1 ring-inset ring-indigo-200/80'
                   : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} group relative mt-3 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-200 hover:translate-x-0.5 active:scale-[0.98]">
                @if ($profileActive)
                    <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-gradient-to-b from-indigo-500 to-violet-600" aria-hidden="true"></span>
                @endif
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-all duration-200 {{ $profileActive ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'bg-slate-100 text-slate-500 group-hover:scale-105 group-hover:bg-indigo-50 group-hover:text-indigo-600' }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="12" cy="8" r="4" stroke-width="1.9" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M4 21a8 8 0 0 1 16 0" /></svg>
                </span>
                <span>Account settings</span>
            </a>
        </div>
    </div>

    <div class="border-t border-slate-100 p-4">
        <div class="mb-3">
            <x-install-app-button />
        </div>
        <div class="card-interactive relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-violet-600 to-indigo-700 p-4 text-white shadow-lg shadow-indigo-900/15">
            <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-white/15 blur-xl" aria-hidden="true"></div>
            <p class="relative text-sm font-extrabold">Tagoloan Live</p>
            <p class="relative mt-1 text-xs leading-5 text-indigo-100">Fast rides, live GPS tracking, and weather-smart trips in one hub.</p>
            <a href="{{ route($dashboardRoute) }}" class="group relative mt-3 inline-flex items-center gap-1 rounded-lg bg-white/15 px-3 py-1.5 text-xs font-bold text-white backdrop-blur-sm transition hover:bg-white/25">
                <span>Go to overview</span>
                <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
            </a>
        </div>
    </div>
</aside>
