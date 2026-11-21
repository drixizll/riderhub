<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') === 'Laravel' ? 'RiderHub' : config('app.name', 'RiderHub') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('icons/riderhub.svg') }}">
        <meta name="theme-color" content="#7c3aed">

        @include('layouts.theme-init')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
        <div class="auth-ambience" aria-hidden="true">
            <span class="auth-glow auth-glow-one"></span>
            <span class="auth-glow auth-glow-two"></span>
        </div>
        <div class="page-loading fixed inset-x-0 top-0 z-[100] h-1 bg-indigo-100/70" role="status" aria-live="polite" aria-label="Loading page">
            <span class="page-loading-progress block h-full w-full origin-left bg-gradient-to-r from-indigo-500 via-violet-500 to-indigo-400" aria-hidden="true"></span>
        </div>
        <div class="auth-content min-h-screen">
            <header class="border-b border-slate-200 bg-white">
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
                            <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-slate-700 transition hover:text-indigo-600">Log in</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-slate-700 transition hover:text-indigo-600">Back to log in</a>
                        @endif
                    </div>
                </nav>
            </header>

            <main class="mx-auto grid min-h-[calc(100vh-9rem)] max-w-7xl items-center gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8 lg:py-14">
                <aside class="auth-story-enter relative hidden min-h-[560px] overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-700 via-indigo-700 to-violet-800 p-10 text-white shadow-xl shadow-indigo-900/10 lg:flex lg:flex-col lg:justify-between">
                    <div class="auth-orb-float absolute -right-20 -top-16 h-72 w-72 rounded-full bg-white/10 blur-3xl" aria-hidden="true"></div>
                    <div class="auth-orb-float-delayed absolute -bottom-28 -left-16 h-80 w-80 rounded-full bg-amber-300/15 blur-3xl" aria-hidden="true"></div>
                    <div class="relative">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-indigo-100">
                            <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                            Rides and deliveries, simplified
                        </span>
                        <h1 class="mt-8 max-w-md text-4xl font-extrabold leading-tight tracking-tight">
                            Your next move starts with RiderHub.
                        </h1>
                        <p class="mt-5 max-w-md text-base leading-7 text-indigo-100">
                            One account for getting there, sending packages, and keeping track of every trip.
                        </p>
                    </div>

                    <div class="auth-feature-float relative rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur-sm">
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white text-indigo-700">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 17h2m10 0h2M7 17l2-6h6l2 6m-8-6 1-3h4l1 3M5 17a2 2 0 1 0 4 0m6 0a2 2 0 1 0 4 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            <div>
                                <p class="font-bold">From pickup to drop-off</p>
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
