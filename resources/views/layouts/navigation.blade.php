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
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-50 text-sm font-bold text-indigo-700 ring-1 ring-indigo-100 transition hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" aria-label="Open account menu">
                        @if (Auth::user()->profile_photo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url(Auth::user()->profile_photo_path) }}" alt="" class="h-10 w-10 rounded-full object-cover">
                        @else
                            {{ \Illuminate\Support\Str::of(Auth::user()->name)->explode(' ')->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}
                        @endif
                    </button>
                </x-slot>

                <x-slot name="content">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <p class="text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</p>
                        <p class="mt-0.5 truncate text-xs text-slate-500">{{ Auth::user()->email }}</p>
                    </div>
                    <x-dropdown-link :href="route($dashboardRoute)">Dashboard</x-dropdown-link>
                    <x-dropdown-link :href="route('profile.edit')">{{ __('Account settings') }}</x-dropdown-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-slate-700 transition hover:bg-indigo-50 hover:text-indigo-700">
                            {{ __('Log out') }}
                        </button>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</header>
