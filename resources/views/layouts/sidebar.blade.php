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

<aside
    @mouseenter="sidebarHover = true"
    @mouseleave="sidebarHover = false"
    class="fixed bottom-0 left-0 top-16 z-50 flex w-64 -translate-x-full flex-col overflow-hidden border-r border-slate-200/80 bg-white/95 backdrop-blur-md transition-all duration-300 ease-out lg:translate-x-0"
    :class="[
        sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full',
        sidebarHover ? 'lg:w-64 lg:shadow-2xl lg:shadow-indigo-950/15' : 'lg:w-20 lg:shadow-none'
    ]"
>
    <div class="no-scrollbar flex-1 overflow-y-auto overflow-x-hidden px-3 py-6">
        <p class="whitespace-nowrap px-3 text-[11px] font-bold uppercase tracking-widest text-slate-400 transition-all duration-200"
           :class="sidebarHover || sidebarOpen ? 'opacity-100 translate-x-0' : 'lg:opacity-0 lg:-translate-x-2'">
            Workspace
        </p>
        <nav class="mt-3 space-y-1.5" aria-label="Main sidebar">
            @foreach ($navigation as $item)
                @php
                    $active = request()->routeIs($item['active']);
                @endphp
                <a href="{{ route($item['route']) }}"
                   @click="sidebarOpen = false"
                   title="{{ $item['label'] }}"
                   class="{{ $active
                       ? 'bg-gradient-to-r from-indigo-50 via-indigo-50/80 to-violet-50/60 text-indigo-700 shadow-xs ring-1 ring-inset ring-indigo-200/80'
                       : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} group relative flex items-center gap-3 rounded-xl px-2.5 py-2.5 text-sm font-semibold transition-all duration-200 active:scale-[0.98]">
                    @if ($active)
                        <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-gradient-to-b from-indigo-500 to-violet-600" aria-hidden="true"></span>
                    @endif
                    <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-all duration-200 {{ $active ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'bg-slate-100 text-slate-500 group-hover:scale-105 group-hover:bg-indigo-50 group-hover:text-indigo-600' }}">
                        @if ($item['icon'] === 'home')
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z" /></svg>
                        @elseif ($item['icon'] === 'plus')
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" /></svg>
                        @elseif ($item['icon'] === 'people')
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m16 0v-2a4 4 0 0 0-3-3.87M14 3.13a4 4 0 0 1 0 7.75M10 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" /></svg>
                        @else
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" /></svg>
                        @endif
                        @if (($item['badge'] ?? 0) > 0)
                            <span class="absolute -right-1 -top-1 h-2.5 w-2.5 rounded-full bg-amber-500 ring-2 ring-white transition-opacity duration-200"
                                  :class="sidebarHover || sidebarOpen ? 'opacity-0' : 'opacity-100'" aria-hidden="true"></span>
                        @endif
                    </span>
                    <span class="whitespace-nowrap transition-all duration-200"
                          :class="sidebarHover || sidebarOpen ? 'opacity-100 translate-x-0' : 'lg:opacity-0 lg:-translate-x-2 lg:pointer-events-none'">
                        {{ $item['label'] }}
                    </span>
                    @if (($item['badge'] ?? 0) > 0)
                        <span class="ml-auto inline-flex min-w-6 items-center justify-center rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-extrabold text-amber-800 shadow-xs transition-all duration-200"
                              :class="sidebarHover || sidebarOpen ? 'opacity-100 scale-100' : 'lg:opacity-0 lg:scale-75 lg:pointer-events-none'">
                            {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="mt-6 border-t border-slate-100 pt-5">
            <p class="whitespace-nowrap px-3 text-[11px] font-bold uppercase tracking-widest text-slate-400 transition-all duration-200"
               :class="sidebarHover || sidebarOpen ? 'opacity-100 translate-x-0' : 'lg:opacity-0 lg:-translate-x-2'">
                Tools &amp; Reports
            </p>
            <div class="mt-3 space-y-1.5">
                @if ($role === 'admin')
                    <a href="{{ route('admin.bookings.export.pdf') }}" target="_blank" title="Export PDF Report"
                       class="group flex items-center gap-3 rounded-xl px-2.5 py-2.5 text-sm font-semibold text-slate-600 transition-all duration-200 hover:bg-indigo-50/70 hover:text-indigo-700 active:scale-[0.98]">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 transition group-hover:scale-105 group-hover:bg-indigo-600 group-hover:text-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8Z" /></svg>
                        </span>
                        <span class="whitespace-nowrap transition-all duration-200" :class="sidebarHover || sidebarOpen ? 'opacity-100 translate-x-0' : 'lg:opacity-0 lg:-translate-x-2 lg:pointer-events-none'">Export PDF Report</span>
                    </a>
                    <a href="{{ route('admin.bookings.export.csv') }}" data-no-loading="true" title="Export CSV (Excel)"
                       class="group flex items-center gap-3 rounded-xl px-2.5 py-2.5 text-sm font-semibold text-slate-600 transition-all duration-200 hover:bg-emerald-50/70 hover:text-emerald-700 active:scale-[0.98]">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition group-hover:scale-105 group-hover:bg-emerald-600 group-hover:text-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0-4-4m4 4 4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2" /></svg>
                        </span>
                        <span class="whitespace-nowrap transition-all duration-200" :class="sidebarHover || sidebarOpen ? 'opacity-100 translate-x-0' : 'lg:opacity-0 lg:-translate-x-2 lg:pointer-events-none'">Export CSV (Excel)</span>
                    </a>
                @elseif ($role === 'customer')
                    <a href="{{ route('customer.bookings.export.pdf') }}" target="_blank" title="Export PDF History"
                       class="group flex items-center gap-3 rounded-xl px-2.5 py-2.5 text-sm font-semibold text-slate-600 transition-all duration-200 hover:bg-indigo-50/70 hover:text-indigo-700 active:scale-[0.98]">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 transition group-hover:scale-105 group-hover:bg-indigo-600 group-hover:text-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8Z" /></svg>
                        </span>
                        <span class="whitespace-nowrap transition-all duration-200" :class="sidebarHover || sidebarOpen ? 'opacity-100 translate-x-0' : 'lg:opacity-0 lg:-translate-x-2 lg:pointer-events-none'">Export PDF History</span>
                    </a>
                @elseif ($role === 'rider')
                    <a href="{{ route('rider.earnings.export.pdf') }}" target="_blank" title="Earnings PDF Report"
                       class="group flex items-center gap-3 rounded-xl px-2.5 py-2.5 text-sm font-semibold text-slate-600 transition-all duration-200 hover:bg-emerald-50/70 hover:text-emerald-700 active:scale-[0.98]">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition group-hover:scale-105 group-hover:bg-emerald-600 group-hover:text-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8Z" /></svg>
                        </span>
                        <span class="whitespace-nowrap transition-all duration-200" :class="sidebarHover || sidebarOpen ? 'opacity-100 translate-x-0' : 'lg:opacity-0 lg:-translate-x-2 lg:pointer-events-none'">Earnings PDF Report</span>
                    </a>
                @endif

                <button type="button" @click="sidebarOpen = false; $dispatch('riderhub-open-ai')" title="Ask RiderHub AI"
                        class="group flex w-full items-center gap-3 rounded-xl px-2.5 py-2.5 text-left text-sm font-semibold text-slate-600 transition-all duration-200 hover:bg-violet-50/80 hover:text-violet-700 active:scale-[0.98]">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white shadow-sm shadow-violet-600/30 transition group-hover:scale-105">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3z" /></svg>
                    </span>
                    <span class="whitespace-nowrap transition-all duration-200" :class="sidebarHover || sidebarOpen ? 'opacity-100 translate-x-0' : 'lg:opacity-0 lg:-translate-x-2 lg:pointer-events-none'">Ask RiderHub AI</span>
                </button>
            </div>
        </div>

        <div class="mt-6 border-t border-slate-100 pt-5">
            <p class="whitespace-nowrap px-3 text-[11px] font-bold uppercase tracking-widest text-slate-400 transition-all duration-200"
               :class="sidebarHover || sidebarOpen ? 'opacity-100 translate-x-0' : 'lg:opacity-0 lg:-translate-x-2'">
                Account
            </p>
            @php
                $profileActive = request()->routeIs('profile.*');
            @endphp
            <a href="{{ route('profile.edit') }}"
               @click="sidebarOpen = false"
               title="Account settings"
               class="{{ $profileActive
                   ? 'bg-gradient-to-r from-indigo-50 via-indigo-50/80 to-violet-50/60 text-indigo-700 shadow-xs ring-1 ring-inset ring-indigo-200/80'
                   : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} group relative mt-3 flex items-center gap-3 rounded-xl px-2.5 py-2.5 text-sm font-semibold transition-all duration-200 active:scale-[0.98]">
                @if ($profileActive)
                    <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-gradient-to-b from-indigo-500 to-violet-600" aria-hidden="true"></span>
                @endif
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-all duration-200 {{ $profileActive ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'bg-slate-100 text-slate-500 group-hover:scale-105 group-hover:bg-indigo-50 group-hover:text-indigo-600' }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="12" cy="8" r="4" stroke-width="1.9" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M4 21a8 8 0 0 1 16 0" /></svg>
                </span>
                <span class="whitespace-nowrap transition-all duration-200"
                      :class="sidebarHover || sidebarOpen ? 'opacity-100 translate-x-0' : 'lg:opacity-0 lg:-translate-x-2 lg:pointer-events-none'">
                    Account settings
                </span>
            </a>
        </div>
    </div>

    <div class="border-t border-slate-100 p-3 transition-all duration-300"
         :class="sidebarHover || sidebarOpen ? 'opacity-100 translate-y-0' : 'lg:opacity-0 lg:translate-y-2 lg:pointer-events-none'">
        <div class="mb-3">
            <x-install-app-button />
        </div>
        <div class="card-interactive relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-violet-600 to-indigo-700 p-4 text-white shadow-lg shadow-indigo-900/15">
            <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-white/15 blur-xl" aria-hidden="true"></div>
            <p class="relative text-sm font-extrabold">Misamis Oriental Live</p>
            <p class="relative mt-1 text-xs leading-5 text-indigo-100">Fast rides, live GPS tracking, and weather-smart trips in one hub.</p>
            <a href="{{ route($dashboardRoute) }}" class="group relative mt-3 inline-flex items-center gap-1 rounded-lg bg-white/15 px-3 py-1.5 text-xs font-bold text-white backdrop-blur-sm transition hover:bg-white/25">
                <span>Go to overview</span>
                <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
            </a>
        </div>
    </div>
</aside>
