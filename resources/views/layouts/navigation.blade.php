@php
    $dashboardRoute = match (Auth::user()->role) {
        'customer' => 'customer.dashboard',
        'rider' => 'rider.dashboard',
        'admin' => 'admin.dashboard',
        default => 'dashboard',
    };
@endphp

<header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-[1600px] items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = !sidebarOpen"
                    class="inline-flex items-center justify-center rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 lg:hidden"
                    aria-label="Toggle sidebar"
                    :aria-expanded="sidebarOpen.toString()">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <a href="{{ route($dashboardRoute) }}" class="inline-flex items-center gap-3" aria-label="RiderHub dashboard">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 17h2m10 0h2M7 17l2-6h6l2 6m-8-6 1-3h4l1 3M5 17a2 2 0 1 0 4 0m6 0a2 2 0 1 0 4 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                <span class="text-lg font-extrabold tracking-tight text-slate-900">Rider<span class="text-indigo-600">Hub</span></span>
            </a>
        </div>

        <div class="flex items-center gap-3">
            <x-weather-widget variant="compact" />
            <x-theme-toggle />
            <x-notification-menu />
            <span class="hidden text-right sm:block">
                <span class="block text-sm font-semibold text-slate-800">{{ Auth::user()->name }}</span>
                <span class="block text-xs capitalize text-slate-500">{{ Auth::user()->role }} account</span>
            </span>
            <x-dropdown align="right" width="64" contentClasses="bg-white">
                <x-slot name="trigger">
                    <button class="group relative flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 p-0.5 shadow-md shadow-indigo-600/20 transition duration-200 hover:scale-105 hover:shadow-lg hover:shadow-indigo-600/30 active:scale-95 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" aria-label="Open account menu">
                        <span class="flex h-full w-full items-center justify-center overflow-hidden rounded-full bg-indigo-50 text-xs font-extrabold text-indigo-700">
                            @if (Auth::user()->profile_photo_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url(Auth::user()->profile_photo_path) }}" alt="" class="h-full w-full object-cover">
                            @else
                                {{ \Illuminate\Support\Str::of(Auth::user()->name)->explode(' ')->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}
                            @endif
                        </span>
                        <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white bg-emerald-500" aria-hidden="true"></span>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-violet-600 to-indigo-700 px-4 py-4 text-white">
                        <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-white/15 blur-xl" aria-hidden="true"></div>
                        <div class="relative flex items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-white/30 bg-white/15 text-sm font-extrabold text-white shadow-inner">
                                @if (Auth::user()->profile_photo_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url(Auth::user()->profile_photo_path) }}" alt="" class="h-full w-full object-cover">
                                @else
                                    {{ \Illuminate\Support\Str::of(Auth::user()->name)->explode(' ')->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}
                                @endif
                            </span>
                            <div class="min-w-0">
                                <span class="inline-flex rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-indigo-50">{{ Auth::user()->role }}</span>
                                <p class="mt-1 truncate text-sm font-extrabold text-white">{{ Auth::user()->name }}</p>
                                <p class="truncate text-[11px] text-indigo-100/90">{{ Auth::user()->email }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-1.5">
                        <a href="{{ route($dashboardRoute) }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-indigo-50 hover:text-indigo-700">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition group-hover:bg-indigo-100 group-hover:text-indigo-600">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z" /></svg>
                            </span>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-indigo-50 hover:text-indigo-700">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition group-hover:bg-indigo-100 group-hover:text-indigo-600">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="12" cy="8" r="4" stroke-width="1.8" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 21a8 8 0 0 1 16 0" /></svg>
                            </span>
                            <span>{{ __('Account settings') }}</span>
                        </a>
                        <a href="{{ route('notifications.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-indigo-50 hover:text-indigo-700">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition group-hover:bg-indigo-100 group-hover:text-indigo-600">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-8 12h4" /></svg>
                            </span>
                            <span>Notifications</span>
                        </a>
                    </div>

                    <div class="border-t border-slate-100 p-1.5">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-rose-600 transition hover:bg-rose-50 hover:text-rose-700">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-500 transition group-hover:bg-rose-100 group-hover:text-rose-600">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                                </span>
                                <span>{{ __('Log out') }}</span>
                            </button>
                        </form>
                    </div>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</header>
