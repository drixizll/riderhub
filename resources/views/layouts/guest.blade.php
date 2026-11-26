<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ in_array(config('app.name'), ['Laravel', 'RideHub', ''], true) ? 'RiderHub' : config('app.name', 'RiderHub') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('icons/riderhub.svg') }}?v=3">
        <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('icons/icon-192.png') }}?v=3">
        <meta name="theme-color" content="#7c3aed">

        @include('layouts.theme-init')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="hero-mesh-bg min-h-screen font-sans text-slate-800 antialiased">
        <div class="hero-map-grid pointer-events-none fixed inset-0 z-0" aria-hidden="true"></div>
        <canvas data-riderhub-canvas class="pointer-events-none fixed inset-0 z-0 h-full w-full" aria-hidden="true"></canvas>
        <div class="auth-ambience" aria-hidden="true">
            <span class="auth-glow auth-glow-one"></span>
            <span class="auth-glow auth-glow-two"></span>
        </div>
        <div class="page-loading fixed inset-x-0 top-0 z-[100]" role="status" aria-live="polite" aria-label="Loading page">
            <div class="h-1 bg-indigo-100/70">
                <span class="page-loading-progress block h-full w-full origin-left bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-violet-400" aria-hidden="true"></span>
            </div>
            <div class="page-loading-pill fixed left-1/2 top-4 inline-flex items-center gap-2.5 rounded-full border border-indigo-200/80 bg-white/95 px-4 py-2 text-xs font-bold text-indigo-700 shadow-xl shadow-indigo-900/15 backdrop-blur-md">
                <span class="loading-spinner inline-block h-3.5 w-3.5 rounded-full border-2 border-indigo-600 border-r-transparent" aria-hidden="true"></span>
                <span>Loading RiderHub…</span>
            </div>
        </div>
        <div class="auth-content min-h-screen">
            <header class="sticky top-0 z-30 border-b border-slate-200/70 bg-white/85 backdrop-blur-md">
                <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8" aria-label="Main navigation">
                    <a href="{{ url('/') }}" class="transition hover:opacity-90" aria-label="RiderHub home">
                        <x-brand-logo size="md" />
                    </a>

                    <div class="flex items-center gap-2 text-sm font-semibold sm:gap-4">
                        <x-theme-toggle />
                        @if (request()->routeIs('login'))
                            <span class="hidden text-slate-500 sm:inline">New to RiderHub?</span>
                            <a href="{{ route('register') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Create account</a>
                        @elseif (request()->routeIs('register'))
                            <span class="hidden text-slate-500 sm:inline">Already a member?</span>
                            <a href="{{ route('login') }}" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-bold text-slate-800 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700">Log in</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-bold text-slate-800 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700">Back to log in</a>
                        @endif
                    </div>
                </nav>
            </header>

            <main class="mx-auto grid min-h-[calc(100vh-9rem)] max-w-7xl items-center gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8 lg:py-14">
                <aside class="auth-story-enter relative hidden min-h-[600px] overflow-hidden rounded-3xl bg-gradient-to-br from-[#13092b] via-[#271158] to-[#0c061c] p-10 text-white shadow-2xl shadow-indigo-950/35 ring-1 ring-white/15 lg:flex lg:flex-col lg:justify-between">
                    <img
                        src="{{ asset('images/gallery/tagoloan-christmas.jpg') }}"
                        alt=""
                        class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-35"
                        aria-hidden="true"
                    >
                    <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-[#0b0618]/95 via-[#1b0c3b]/70 to-[#2e1065]/55" aria-hidden="true"></div>
                    <div class="auth-orb-float absolute -right-20 -top-16 h-72 w-72 rounded-full bg-fuchsia-500/25 blur-3xl" aria-hidden="true"></div>
                    <div class="auth-orb-float-delayed absolute -bottom-28 -left-16 h-80 w-80 rounded-full bg-amber-400/15 blur-3xl" aria-hidden="true"></div>
                    <div class="relative">
                        <x-realtime-greeting variant="hero" />
                        <h1 class="mt-7 max-w-md text-4xl font-extrabold leading-tight tracking-tight text-white">
                            Your next move starts with <span class="bg-gradient-to-r from-violet-200 via-fuchsia-200 to-amber-200 bg-clip-text text-transparent">RiderHub.</span>
                        </h1>
                        <p class="mt-5 max-w-md text-base leading-7 text-indigo-100/90">
                            Built for locals, commuters, and students across Tagoloan and Misamis Oriental. One account for rides, express deliveries, and live GPS tracking.
                        </p>

                        <div class="mt-7 grid grid-cols-3 gap-2.5">
                            <div class="rounded-2xl border border-white/15 bg-white/10 p-3 backdrop-blur-md">
                                <p class="text-lg font-black text-amber-300">₱55</p>
                                <p class="mt-0.5 text-[11px] font-semibold text-indigo-100/80">Base Fare</p>
                            </div>
                            <div class="rounded-2xl border border-white/15 bg-white/10 p-3 backdrop-blur-md">
                                <p class="text-lg font-black text-emerald-300">Live</p>
                                <p class="mt-0.5 text-[11px] font-semibold text-indigo-100/80">GPS &amp; Chat</p>
                            </div>
                            <div class="rounded-2xl border border-white/15 bg-white/10 p-3 backdrop-blur-md">
                                <p class="text-lg font-black text-violet-200">24/7</p>
                                <p class="mt-0.5 text-[11px] font-semibold text-indigo-100/80">GCash &amp; Cash</p>
                            </div>
                        </div>
                    </div>

                    <div class="auth-feature-float relative rounded-2xl border border-white/20 bg-white/10 p-5 shadow-lg backdrop-blur-md">
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white text-indigo-700 shadow-md">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11Z" />
                                    <circle cx="12" cy="10" r="2.5" stroke-width="2" />
                                </svg>
                            </span>
                            <div>
                                <p class="font-extrabold">From pickup to drop-off</p>
                                <p class="mt-1 text-sm text-indigo-100">Book easily. Follow along. Arrive with confidence.</p>
                            </div>
                        </div>
                        <div class="mt-5 flex items-center gap-2 text-xs font-semibold text-indigo-100">
                            <span class="h-px flex-1 bg-white/25"></span>
                            <span>RIDE · DELIVERY · TRACK</span>
                            <span class="h-px flex-1 bg-white/25"></span>
                        </div>
                    </div>
                </aside>

                <div class="auth-form-enter mx-auto w-full max-w-xl">
                    {{ $slot }}
                </div>
            </main>

            <footer class="border-t border-slate-200 bg-white">
                <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-center text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:text-left lg:px-8">
                    <p>&copy; {{ date('Y') }} RiderHub. All rights reserved.</p>
                    <a href="{{ url('/') }}" class="font-semibold text-slate-600 transition hover:text-indigo-600">Back to home</a>
                </div>
            </footer>
        </div>
    </body>
</html>
