<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') === 'Laravel' ? 'RiderHub' : config('app.name', 'RiderHub') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('icons/riderhub.svg') }}">
        <meta name="theme-color" content="#7c3aed">

        @include('layouts.theme-init')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="aurora-bg min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
        <div class="aurora-layer" aria-hidden="true">
            <div class="hero-map-grid absolute inset-0 opacity-50"></div>
            <div class="aurora-blob aurora-blob-1"></div>
            <div class="aurora-blob aurora-blob-2"></div>
            <div class="aurora-blob aurora-blob-3"></div>
            <canvas data-riderhub-canvas class="h-full w-full"></canvas>
        </div>
        <div class="page-loading fixed inset-x-0 top-0 z-[100]" role="status" aria-live="polite" aria-label="Loading page">
            <div class="h-1 bg-indigo-100/70">
                <span class="page-loading-progress block h-full w-full origin-left bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-cyan-400" aria-hidden="true"></span>
            </div>
            <div class="page-loading-pill fixed left-1/2 top-4 inline-flex items-center gap-2.5 rounded-full border border-indigo-200/80 bg-white/95 px-4 py-2 text-xs font-bold text-indigo-700 shadow-xl shadow-indigo-900/15 backdrop-blur-md">
                <span class="loading-spinner inline-block h-3.5 w-3.5 rounded-full border-2 border-indigo-600 border-r-transparent" aria-hidden="true"></span>
                <span>Loading RiderHub…</span>
            </div>
        </div>
        <div x-data="{ sidebarOpen: false, sidebarHover: false }" class="relative z-10 min-h-screen">
            @include('layouts.navigation')
            @include('layouts.sidebar')

            <div class="min-h-[calc(100vh-4rem)] transition-[padding] duration-300 ease-out lg:pl-20">
                @isset($header)
                    <header class="border-b border-slate-200 bg-white">
                        <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>

        @if (session('success'))
            <div
                x-data="{ show: true }"
                x-show="show"
                x-init="setTimeout(() => show = false, 5000)"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                role="status"
                class="fixed bottom-5 left-5 z-50 flex max-w-sm items-center gap-3 rounded-2xl border border-emerald-200 bg-white/95 px-4 py-3.5 shadow-2xl shadow-emerald-950/15 backdrop-blur-md"
            >
                <span class="success-check-circle flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-md shadow-emerald-500/30">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path class="success-check-path" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                <p class="text-sm font-semibold text-slate-900">{{ session('success') }}</p>
                <button type="button" @click="show = false" class="ml-auto rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Dismiss notification">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" /></svg>
                </button>
            </div>
        @endif

        <x-confirm-modal />
        <x-ai-assistant />
    </body>
</html>
