<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ in_array(config('app.name'), ['Laravel', 'RideHub', ''], true) ? 'RiderHub' : config('app.name', 'RiderHub') }} — Every trip, made easier</title>
        <meta name="description" content="Book a ride or send a package with RiderHub. Convenient rides, reliable delivery, and live rider tracking in one place.">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('icons/riderhub.svg') }}?v=3">
        <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('icons/icon-192.png') }}?v=3">
        <meta name="theme-color" content="#7c3aed">

        @include('layouts.theme-init')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="aurora-bg bg-white font-sans text-slate-800 antialiased">
        <div class="aurora-layer" aria-hidden="true">
            <div class="aurora-blob aurora-blob-1"></div>
            <div class="aurora-blob aurora-blob-2"></div>
            <div class="aurora-blob aurora-blob-3"></div>
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
        <header class="sticky top-0 z-30 border-b border-slate-100/80 bg-white/90 backdrop-blur-md">
            <nav class="mx-auto flex max-w-7xl items-center justify-between gap-2 px-3 py-3.5 sm:px-6 sm:py-4 lg:px-8" aria-label="Main navigation">
                <a href="{{ url('/') }}" class="shrink-0 transition hover:opacity-90" aria-label="RiderHub home">
                    <x-brand-logo size="md" />
                </a>

                <div class="flex shrink-0 items-center gap-1.5 sm:gap-3.5">
                    <a href="#services" class="hidden text-sm font-semibold text-slate-600 transition hover:text-indigo-600 md:inline-flex">Services</a>
                    <a href="#about-us" class="hidden text-sm font-semibold text-slate-600 transition hover:text-indigo-600 md:inline-flex">About us</a>
                    <a href="#how-it-works" class="hidden text-sm font-semibold text-slate-600 transition hover:text-indigo-600 lg:inline-flex">How it works</a>
                    <a href="#faq" class="hidden text-sm font-semibold text-slate-600 transition hover:text-indigo-600 lg:inline-flex">FAQ</a>
                    <x-live-clock />
                    <x-weather-widget variant="compact" />
                    <x-theme-toggle />
                    <x-install-app-button />
                    <a href="{{ route('login') }}" class="whitespace-nowrap rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-800 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 active:scale-95 sm:px-4 sm:py-2.5 sm:text-sm">Log in</a>
                    <a href="{{ route('register') }}" class="whitespace-nowrap rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-3.5 py-2 text-xs font-bold text-white shadow-md shadow-indigo-600/25 transition duration-200 hover:-translate-y-0.5 hover:shadow-lg active:scale-95 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:px-4 sm:py-2.5 sm:text-sm">Sign up</a>
                </div>
            </nav>
        </header>

        <main class="relative z-10">
            <section class="hero-mesh-bg relative overflow-hidden border-b border-indigo-100/80">
                <canvas data-riderhub-canvas class="pointer-events-none absolute inset-0 h-full w-full" aria-hidden="true"></canvas>
                <div class="hero-map-grid pointer-events-none absolute inset-0 opacity-60" aria-hidden="true"></div>
                <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-2 lg:px-8 lg:py-24">
                    <div class="home-hero-copy max-w-2xl reveal-on-scroll" data-revealed="true">
                        <span class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-white px-3 py-1.5 text-sm font-semibold text-indigo-700 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Your city, connected
                        </span>
                        <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">
                            Every trip.<br>
                            Every delivery.<br>
                            <span class="text-gradient-brand">One easy booking.</span>
                        </h1>
                        <p class="mt-6 max-w-xl text-lg leading-8 text-slate-600">
                            Get where you need to go or send what matters. RiderHub brings reliable rides and package delivery together in one simple app.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3.5 text-base font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Book a Ride
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                            </a>
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3.5 text-base font-bold text-slate-800 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Send a Package
                            </a>
                        </div>
                        <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm font-medium text-slate-500">
                            <span class="inline-flex items-center gap-2"><svg class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.78-9.22a.75.75 0 0 0-1.06-1.06L9 11.44 7.28 9.72a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.06 0l4.25-4.25Z" clip-rule="evenodd" /></svg>Easy to book</span>
                            <span class="inline-flex items-center gap-2"><svg class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.78-9.22a.75.75 0 0 0-1.06-1.06L9 11.44 7.28 9.72a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.06 0l4.25-4.25Z" clip-rule="evenodd" /></svg>Track in real time</span>
                        </div>
                    </div>

                    <div class="home-hero-visual relative mx-auto w-full max-w-lg reveal-on-scroll" data-revealed="true">
                        <div class="hero-orb absolute -left-8 -top-8 h-36 w-36 rounded-full bg-violet-400/35 blur-3xl" aria-hidden="true"></div>
                        <div class="hero-orb-delayed absolute -bottom-8 -right-8 h-40 w-40 rounded-full bg-fuchsia-400/30 blur-3xl" aria-hidden="true"></div>

                        <!-- Futuristic Live App Preview Card -->
                        <div class="relative overflow-hidden rounded-[2rem] border border-white/80 bg-white/90 p-5 shadow-2xl shadow-indigo-950/20 backdrop-blur-xl sm:p-6">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex min-w-0 items-center gap-2.5">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-indigo-700 text-white shadow-md shadow-indigo-600/30">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11Z" /><circle cx="12" cy="10" r="2.5" stroke-width="2" /></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-extrabold uppercase tracking-wider text-indigo-600">RiderHub App · Live</p>
                                        <p class="truncate text-xs font-bold text-slate-900 sm:text-sm">Tagoloan → Cagayan de Oro</p>
                                    </div>
                                </div>
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-extrabold text-emerald-700 ring-1 ring-emerald-200/70 sm:px-3 sm:text-[11px]">
                                    <span class="h-2 w-2 animate-ping rounded-full bg-emerald-500"></span>
                                    LIVE GPS
                                </span>
                            </div>

                            <!-- Live Animated Route Radar Display -->
                            <div class="relative mt-5 h-56 overflow-hidden rounded-2xl border border-indigo-200/60 bg-gradient-to-br from-slate-950 via-indigo-950 to-violet-950 shadow-inner">
                                <div class="hero-map-grid absolute inset-0 opacity-45" aria-hidden="true"></div>
                                <svg class="absolute inset-0 h-full w-full" viewBox="0 0 420 224" fill="none" preserveAspectRatio="none" aria-hidden="true">
                                    <path d="M 36 184 C 96 176, 108 112, 168 118 C 228 124, 224 58, 286 72 C 330 82, 336 42, 376 34" stroke="rgba(167,139,250,0.28)" stroke-width="18" stroke-linecap="round" />
                                    <path d="M 36 184 C 96 176, 108 112, 168 118 C 228 124, 224 58, 286 72 C 330 82, 336 42, 376 34" stroke="#a78bfa" stroke-width="3.5" stroke-dasharray="8 8" stroke-linecap="round" class="route-dash-animated" />
                                </svg>

                                <!-- Animated moving rider pulse along the route -->
                                <div class="hero-rider-orbit absolute left-0 top-0 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-violet-400 to-fuchsia-500 text-white shadow-lg shadow-violet-500/60 ring-2 ring-white">
                                    <span class="hero-radar-ring absolute -inset-2 rounded-full bg-violet-400/50"></span>
                                    <svg class="relative h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 17h2m10 0h2M7 17l2-6h6l2 6m-8-6 1-3h4l1 3" /></svg>
                                </div>

                                <!-- Pickup Pin -->
                                <div class="absolute left-[6%] top-[73%] flex items-center gap-1.5">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-white bg-indigo-600 text-white shadow-lg">
                                        <span class="h-2 w-2 rounded-full bg-white"></span>
                                    </span>
                                    <span class="rounded-md bg-slate-900/80 px-2 py-0.5 text-[10px] font-bold text-indigo-100 backdrop-blur-sm">Pickup</span>
                                </div>

                                <!-- Drop-off Pin -->
                                <div class="absolute right-[4%] top-[8%] flex items-center gap-1.5">
                                    <span class="rounded-md bg-slate-900/80 px-2 py-0.5 text-[10px] font-bold text-amber-200 backdrop-blur-sm">Drop-off</span>
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-white bg-amber-400 text-slate-950 shadow-lg">
                                        <span class="h-2 w-2 rounded-full bg-slate-950"></span>
                                    </span>
                                </div>

                                <!-- Floating Telemetry Pills -->
                                <div class="hero-float-card absolute left-3 top-3 inline-flex items-center gap-2 rounded-xl border border-white/15 bg-slate-900/75 px-3 py-1.5 text-[11px] font-bold text-white backdrop-blur-md">
                                    <span class="text-amber-300">⛅</span>
                                    <span>Misamis Oriental · Clear</span>
                                </div>
                                <div class="hero-float-card-delayed absolute bottom-3 right-3 inline-flex items-center gap-2 rounded-xl border border-white/15 bg-slate-900/75 px-3 py-1.5 text-[11px] font-bold text-emerald-300 backdrop-blur-md">
                                    <span>ETA 4 mins</span>
                                    <span class="text-white/40">·</span>
                                    <span class="text-white">₱92.00</span>
                                </div>
                            </div>

                            <!-- Bottom Action Bar -->
                            <div class="mt-4 grid grid-cols-3 gap-1.5 text-center sm:gap-2">
                                <div class="rounded-xl border border-indigo-100/80 bg-indigo-50/80 px-1.5 py-2 sm:px-2.5 sm:py-2.5">
                                    <p class="text-[9px] font-bold uppercase tracking-wider text-indigo-600 sm:text-[10px]">Tracking</p>
                                    <p class="mt-0.5 text-[11px] font-extrabold text-slate-900 sm:text-xs">Satellite + GPS</p>
                                </div>
                                <div class="rounded-xl border border-indigo-100/80 bg-indigo-50/80 px-1.5 py-2 sm:px-2.5 sm:py-2.5">
                                    <p class="text-[9px] font-bold uppercase tracking-wider text-indigo-600 sm:text-[10px]">Payments</p>
                                    <p class="mt-0.5 text-[11px] font-extrabold text-slate-900 sm:text-xs">GCash · Maya</p>
                                </div>
                                <div class="rounded-xl border border-indigo-100/80 bg-indigo-50/80 px-1.5 py-2 sm:px-2.5 sm:py-2.5">
                                    <p class="text-[9px] font-bold uppercase tracking-wider text-indigo-600 sm:text-[10px]">Smart AI</p>
                                    <p class="mt-0.5 text-[11px] font-extrabold text-slate-900 sm:text-xs">24/7 Assistant</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="services" class="reveal-on-scroll mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-sm font-bold uppercase tracking-widest text-indigo-600">One app, more ways to move</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Everything you need for the journey</h2>
                    <p class="mt-4 text-lg leading-7 text-slate-600">From your daily commute to a package across town, we make getting there simple.</p>
                </div>

                <div class="reveal-stagger mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <article class="reveal-on-scroll rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 17h2m10 0h2M7 17l2-6h6l2 6m-8-6 1-3h4l1 3M5 17a2 2 0 1 0 4 0m6 0a2 2 0 1 0 4 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </span>
                        <h3 class="mt-5 text-lg font-bold text-slate-900">Ride Booking</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Book a convenient ride for your everyday trips with just a few taps.</p>
                    </article>
                    <article class="reveal-on-scroll rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /><path d="m4.5 7.5 7.5 4.25 7.5-4.25M12 12v8.5m-4-15 8 4.5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /></svg>
                        </span>
                        <h3 class="mt-5 text-lg font-bold text-slate-900">Delivery</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Send packages across town and keep important things moving.</p>
                    </article>
                    <article class="reveal-on-scroll rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.8" /></svg>
                        </span>
                        <h3 class="mt-5 text-lg font-bold text-slate-900">Live Rider Tracking</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Follow your rider’s progress and know what’s happening along the way.</p>
                    </article>
                    <article class="reveal-on-scroll rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8" /><path d="M3 10h18m-13 5h3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
                        </span>
                        <h3 class="mt-5 text-lg font-bold text-slate-900">Cash or Online Payment</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Choose the payment option that works best for you.</p>
                    </article>
                </div>
            </section>

            <section id="about-us" class="reveal-on-scroll relative overflow-hidden bg-white/70 py-16 backdrop-blur-[2px] sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div
                        x-data="{
                            active: 0,
                            paused: false,
                            timer: null,
                            slides: [
                                {
                                    image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a7/Tagaloan_River%2C_Tagaloan_Bridge%2C_Santa_Cruz_drone_%28Tagaloan%2C_Misamis_Oriental%3B_03-20-2026%29.jpg/1280px-Tagaloan_River%2C_Tagaloan_Bridge%2C_Santa_Cruz_drone_%28Tagaloan%2C_Misamis_Oriental%3B_03-20-2026%29.jpg',
                                    badge: 'Tagoloan, Misamis Oriental',
                                    title: 'Connecting every barangay across Tagoloan',
                                    caption: 'From Santa Cruz and Tagoloan Bridge to Poblacion, Baluarte, and Casinglot — built by locals for everyday Tagoloan commuters.',
                                    credit: 'Real photo · Tagoloan River & Bridge (Wikimedia Commons, CC BY-SA 4.0)',
                                    stat: 'Tagoloan Local',
                                    statSub: 'Misamis Oriental'
                                },
                                {
                                    image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Tagoloan_Municipal_Hall.jpg/1280px-Tagoloan_Municipal_Hall.jpg',
                                    badge: 'For Locals & Students',
                                    title: 'Reliable daily rides for students and workers',
                                    caption: 'Heading to Tagoloan Community College, Poblacion, or work? Book affordable rides and deliveries with upfront fares and live weather alerts.',
                                    credit: 'Real photo · Tagoloan Municipal Hall (Wikimedia Commons, CC BY-SA 4.0)',
                                    stat: 'Student-Friendly',
                                    statSub: 'Upfront fares'
                                },
                                {
                                    image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ad/Angkas_riders_in_the_Philippines_03.jpg/1280px-Angkas_riders_in_the_Philippines_03.jpg',
                                    badge: 'Philippine Riders',
                                    title: 'Trusted Filipino riders with real-time GPS tracking',
                                    caption: 'Follow your rider live on the Tagoloan map with real-time ETA, verified ratings, and secure Cash or PayMongo GCash/Maya payments.',
                                    credit: 'Real photo · Philippine Riders (Wikimedia Commons, CC BY-SA 4.0)',
                                    stat: 'Live GPS + AI',
                                    statSub: 'Safe & tracked'
                                }
                            ],
                            start() {
                                this.stop();
                                this.timer = window.setInterval(() => {
                                    if (!this.paused) this.next();
                                }, 5500);
                            },
                            stop() {
                                if (this.timer) {
                                    window.clearInterval(this.timer);
                                    this.timer = null;
                                }
                            },
                            next() {
                                this.active = (this.active + 1) % this.slides.length;
                            },
                            prev() {
                                this.active = (this.active - 1 + this.slides.length) % this.slides.length;
                            },
                            goTo(index) {
                                this.active = index;
                                this.start();
                            }
                        }"
                        x-init="start()"
                        @mouseenter="paused = true"
                        @mouseleave="paused = false"
                        class="grid items-center gap-10 lg:grid-cols-[1.15fr_0.85fr] lg:gap-14"
                    >
                        <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-slate-900 shadow-2xl shadow-indigo-950/15">
                            <div class="relative aspect-[16/10] w-full overflow-hidden">
                                <template x-for="(slide, index) in slides" :key="slide.image">
                                    <div
                                        x-show="active === index"
                                        x-transition:enter="transition ease-out duration-700"
                                        x-transition:enter-start="opacity-0 scale-105"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-500"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="absolute inset-0"
                                    >
                                        <img
                                            :src="slide.image"
                                            :alt="slide.title"
                                            referrerpolicy="no-referrer"
                                            class="photo-kenburns h-full w-full object-cover"
                                        >
                                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/25 to-transparent"></div>
                                        <div class="absolute inset-x-0 bottom-0 p-6 sm:p-8">
                                            <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white backdrop-blur-md">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                                <span x-text="slide.badge"></span>
                                            </span>
                                            <h3 class="mt-3 text-xl font-extrabold text-white sm:text-2xl" x-text="slide.title"></h3>
                                            <p class="mt-1.5 max-w-lg text-sm leading-6 text-slate-200" x-text="slide.caption"></p>
                                            <p class="mt-2 text-[11px] font-medium text-slate-300/80" x-text="slide.credit"></p>
                                        </div>
                                    </div>
                                </template>

                                <div class="absolute right-4 top-4 z-10 flex items-center gap-2">
                                    <button type="button" @click="prev(); start()" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-950/50 text-white backdrop-blur-md transition hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-white" aria-label="Previous photo">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L8.832 10l3.938 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z" clip-rule="evenodd" /></svg>
                                    </button>
                                    <button type="button" @click="next(); start()" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-950/50 text-white backdrop-blur-md transition hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-white" aria-label="Next photo">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-3 bg-slate-950 px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <template x-for="(slide, index) in slides" :key="'dot-' + index">
                                        <button
                                            type="button"
                                            @click="goTo(index)"
                                            class="h-2 rounded-full transition-all duration-300"
                                            :class="active === index ? 'w-8 bg-indigo-400' : 'w-2 bg-slate-600 hover:bg-slate-400'"
                                            :aria-label="`Show slide ${index + 1}: ${slide.badge}`"
                                        ></button>
                                    </template>
                                </div>
                                <span class="text-xs font-semibold text-slate-400" x-text="`0${active + 1} / 0${slides.length}`"></span>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-bold uppercase tracking-widest text-indigo-600">About RiderHub</p>
                            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Built for real riders and everyday city journeys</h2>
                            <p class="mt-4 text-base leading-7 text-slate-600">
                                RiderHub was created to make urban rides and package deliveries effortless, transparent, and safe. From instant fare estimates and live weather advisories to real-time GPS tracking and GCash/Maya payments, every detail is engineered for peace of mind.
                            </p>

                            <div class="mt-6 space-y-3">
                                <template x-for="(slide, index) in slides" :key="'tab-' + index">
                                    <button
                                        type="button"
                                        @click="goTo(index)"
                                        class="flex w-full items-center justify-between gap-4 rounded-2xl border p-4 text-left transition duration-200"
                                        :class="active === index
                                            ? 'border-indigo-200 bg-indigo-50/70 shadow-sm'
                                            : 'border-slate-200 bg-white hover:border-indigo-100 hover:bg-slate-50'"
                                    >
                                        <div>
                                            <p class="text-sm font-bold text-slate-900" x-text="slide.badge"></p>
                                            <p class="mt-0.5 text-xs text-slate-500" x-text="slide.title"></p>
                                        </div>
                                        <span class="shrink-0 rounded-xl bg-white px-3 py-1.5 text-right shadow-sm ring-1 ring-slate-100">
                                            <span class="block text-xs font-extrabold text-indigo-600" x-text="slide.stat"></span>
                                            <span class="block text-[10px] text-slate-400" x-text="slide.statSub"></span>
                                        </span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="how-it-works" class="reveal-on-scroll bg-indigo-50/40 py-16 backdrop-blur-[2px] sm:py-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Simple from the start</p>
                        <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">How RiderHub works</h2>
                        <p class="mt-4 text-lg leading-7 text-slate-600">Three easy steps, and you’re on your way.</p>
                    </div>

                    <div class="mt-12 grid gap-8 md:grid-cols-3">
                        <article class="reveal-on-scroll relative rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-600 text-lg font-extrabold text-white">1</span>
                            <h3 class="mt-5 text-xl font-bold text-slate-900">Choose your service</h3>
                            <p class="mt-2 leading-7 text-slate-600">Select a ride or delivery and tell us where to pick up and drop off.</p>
                        </article>
                        <article class="reveal-on-scroll relative rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-600 text-lg font-extrabold text-white">2</span>
                            <h3 class="mt-5 text-xl font-bold text-slate-900">Get matched</h3>
                            <p class="mt-2 leading-7 text-slate-600">We’ll connect you with a nearby rider and share your booking details.</p>
                        </article>
                        <article class="reveal-on-scroll relative rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-600 text-lg font-extrabold text-white">3</span>
                            <h3 class="mt-5 text-xl font-bold text-slate-900">Track and go</h3>
                            <p class="mt-2 leading-7 text-slate-600">Follow your trip or package, then pay with cash or online.</p>
                        </article>
                    </div>
                </div>
            </section>

            <!-- RiderHub in Action — Real Tagoloan & Misamis Oriental Photo Gallery -->
            <section id="gallery" class="reveal-on-scroll border-t border-indigo-900/30 bg-[#0d0b1a] py-16 text-white sm:py-20">
                @php
                    $galleryPhotos = [
                        [
                            'src' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1d/Tagaloan_Plaza_drone_1_%28Antonio_Cosin_Highway%2C_Tagaloan%2C_Misamis_Oriental%3B_03-20-2026%29.jpg/1280px-Tagaloan_Plaza_drone_1_%28Antonio_Cosin_Highway%2C_Tagaloan%2C_Misamis_Oriental%3B_03-20-2026%29.jpg',
                            'fallback' => asset('images/about/city-commute.jpg'),
                            'title' => 'Tagoloan Plaza & Highway',
                            'subtitle' => 'Poblacion, Tagoloan, Misamis Oriental',
                        ],
                        [
                            'src' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Tagoloan_Municipal_Hall.jpg/1280px-Tagoloan_Municipal_Hall.jpg',
                            'fallback' => asset('images/about/rider-service.jpg'),
                            'title' => 'Tagoloan Town Center',
                            'subtitle' => 'Balay Lungsod · Core Pickup Hub',
                        ],
                        [
                            'src' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a7/Tagaloan_River%2C_Tagaloan_Bridge%2C_Santa_Cruz_drone_%28Tagaloan%2C_Misamis_Oriental%3B_03-20-2026%29.jpg/1280px-Tagaloan_River%2C_Tagaloan_Bridge%2C_Santa_Cruz_drone_%28Tagaloan%2C_Misamis_Oriental%3B_03-20-2026%29.jpg',
                            'fallback' => asset('images/about/package-delivery.jpg'),
                            'title' => 'Tagoloan Bridge Corridor',
                            'subtitle' => 'Tagoloan ↔ Cagayan de Oro Route',
                        ],
                        [
                            'src' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/58/Motorcycle_taxis_in_the_Philippines_04.jpg/1280px-Motorcycle_taxis_in_the_Philippines_04.jpg',
                            'fallback' => asset('images/about/live-tracking.jpg'),
                            'title' => 'Verified Partner Riders',
                            'subtitle' => 'Daily Rides & Express Delivery',
                        ],
                    ];
                @endphp

                <div x-data="{ activePhoto: null }" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-widest text-violet-400">Tagoloan &amp; Misamis Oriental</p>
                            <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">RiderHub in Action</h2>
                        </div>
                        <p class="text-xs font-semibold text-violet-300/80">Click photo to enlarge</p>
                    </div>

                    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($galleryPhotos as $photo)
                            <button
                                type="button"
                                @click="activePhoto = @js($photo)"
                                class="group relative aspect-[4/3] w-full overflow-hidden rounded-2xl border border-white/10 bg-slate-900 text-left shadow-lg transition duration-300 hover:-translate-y-1 hover:border-violet-400/50 hover:shadow-violet-950/50 focus:outline-none focus:ring-2 focus:ring-violet-500"
                            >
                                <img
                                    src="{{ $photo['src'] }}"
                                    alt="{{ $photo['title'] }}"
                                    loading="lazy"
                                    referrerpolicy="no-referrer"
                                    onerror="this.onerror=null;this.src='{{ $photo['fallback'] }}';"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/25 to-transparent"></div>
                                <div class="absolute inset-x-0 bottom-0 p-4">
                                    <p class="text-sm font-extrabold text-white">{{ $photo['title'] }}</p>
                                    <p class="mt-0.5 text-xs text-slate-300">{{ $photo['subtitle'] }}</p>
                                </div>
                            </button>
                        @endforeach
                    </div>

                    <!-- Lightbox Modal -->
                    <div
                        x-show="activePhoto !== null"
                        x-cloak
                        x-transition.opacity
                        @click="activePhoto = null"
                        @keydown.escape.window="activePhoto = null"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/85 p-4 backdrop-blur-sm"
                    >
                        <div @click.stop class="relative w-full max-w-4xl overflow-hidden rounded-3xl border border-white/15 bg-slate-900 shadow-2xl">
                            <button
                                type="button"
                                @click="activePhoto = null"
                                class="absolute right-4 top-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-slate-950/70 text-white backdrop-blur-md transition hover:bg-violet-600"
                                aria-label="Close enlarged photo"
                            >
                                ✕
                            </button>
                            <template x-if="activePhoto">
                                <div>
                                    <img
                                        :src="activePhoto.src"
                                        :alt="activePhoto.title"
                                        referrerpolicy="no-referrer"
                                        class="max-h-[75vh] w-full bg-black object-contain"
                                    >
                                    <div class="border-t border-white/10 px-6 py-4">
                                        <p class="text-base font-extrabold text-white" x-text="activePhoto.title"></p>
                                        <p class="mt-0.5 text-xs text-slate-400" x-text="activePhoto.subtitle"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Common Questions & FAQs -->
            <section id="faq" class="reveal-on-scroll mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Common Questions</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Frequently Asked Questions</h2>
                    <p class="mt-3 text-base leading-7 text-slate-600">Everything you need to know about booking rides, sending parcels, and riding with RiderHub across Misamis Oriental.</p>
                </div>

                @php
                    $faqs = [
                        [
                            'q' => 'How are RiderHub fares calculated?',
                            'a' => 'Fares are transparent and calculated upfront before you book: ₱55.00 base fare plus ₱18.50 per kilometer based on your pickup and drop-off pins. No hidden charges.',
                        ],
                        [
                            'q' => 'What payment methods are accepted?',
                            'a' => 'You can pay with Cash directly to your rider upon arrival, or pay online securely via PayMongo using GCash, Maya, or eligible Visa/Mastercard.',
                        ],
                        [
                            'q' => 'Which areas in Misamis Oriental are covered?',
                            'a' => 'RiderHub serves Tagoloan (Poblacion, Baluarte, Sta. Ana, Natumolan), Cagayan de Oro, Villanueva, Jasaan, Opol, El Salvador, and surrounding Misamis Oriental municipalities.',
                        ],
                        [
                            'q' => 'Can I track my rider and message them during a trip?',
                            'a' => 'Yes! Once a verified rider accepts your booking, you get live GPS tracking with ETA, live route weather, verified rider vehicle/plate details, an in-app Trip Chat, and a one-tap Safety & SOS bar.',
                        ],
                        [
                            'q' => 'How do I become a verified RiderHub partner rider?',
                            'a' => 'Create an account and choose the Rider role, complete your profile with your motorcycle model and plate number, and wait for Admin KYC verification. Once verified, toggle Online to start accepting bookings.',
                        ],
                        [
                            'q' => 'Can I cancel a booking or book again quickly?',
                            'a' => 'You can edit or cancel any pending booking before a rider accepts it. For completed trips, you can download a PDF receipt or tap "Book Again" to repeat the same route in one click.',
                        ],
                    ];
                @endphp

                <div x-data="{ open: 0 }" class="mt-10 space-y-3">
                    @foreach ($faqs as $idx => $faq)
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-indigo-200">
                            <button
                                type="button"
                                @click="open = open === {{ $idx }} ? null : {{ $idx }}"
                                :aria-expanded="(open === {{ $idx }}).toString()"
                                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left sm:px-6"
                            >
                                <span class="text-sm font-bold text-slate-900 sm:text-base">{{ $faq['q'] }}</span>
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 transition-transform duration-200" :class="open === {{ $idx }} ? 'rotate-180 bg-indigo-600 text-white' : ''">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                                </span>
                            </button>
                            <div x-show="open === {{ $idx }}" x-cloak x-transition class="border-t border-slate-100 px-5 py-4 text-sm leading-6 text-slate-600 sm:px-6">
                                {{ $faq['a'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="reveal-on-scroll mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8 lg:pb-20">
                <div class="overflow-hidden rounded-3xl bg-indigo-700 px-6 py-12 text-center shadow-xl shadow-indigo-900/10 sm:px-12 sm:py-14">
                    <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Ready to make your next move?</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg leading-7 text-indigo-100">Create your RiderHub account and book your first trip or delivery today.</p>
                    <a href="{{ route('register') }}" class="mt-8 inline-flex items-center justify-center rounded-xl bg-white px-6 py-3.5 text-base font-bold text-indigo-700 shadow-sm transition hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-700">Get started</a>
                    <a href="{{ asset('downloads/RiderHub.apk') }}" download="RiderHub.apk" class="mt-3 inline-flex items-center justify-center gap-2 rounded-xl border border-white/30 bg-white/10 px-6 py-3.5 text-base font-bold text-white backdrop-blur-sm transition hover:bg-white/20 sm:ml-3 sm:mt-8">
                        📱 Download Android App (APK)
                    </a>
                </div>
            </section>
        </main>

        <footer class="relative z-10 border-t border-indigo-900/50 bg-[#0b0a17] text-slate-300">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-14">
                <div class="grid gap-10 md:grid-cols-3 md:gap-8">
                    <!-- Column 1: Brand -->
                    <div>
                        <a href="{{ url('/') }}" class="inline-flex items-center gap-3 transition hover:opacity-90">
                            <img src="{{ asset('icons/riderhub.svg') }}" alt="RiderHub Logo" class="h-10 w-10 rounded-xl shadow-md shadow-indigo-500/20">
                            <div>
                                <p class="text-base font-extrabold tracking-tight text-white">RiderHub</p>
                                <p class="text-xs font-semibold text-violet-400">Misamis Oriental Ride &amp; Delivery</p>
                            </div>
                        </a>
                        <p class="mt-4 max-w-sm text-sm leading-6 text-slate-400">
                            Committed to fast, safe, and transparent motorcycle rides and parcel deliveries across Tagoloan, Cagayan de Oro, and Misamis Oriental.
                        </p>
                    </div>

                    <!-- Column 2: Operations & Hub -->
                    <div>
                        <h3 class="text-xs font-extrabold uppercase tracking-widest text-violet-400">Operations &amp; Coverage</h3>
                        <p class="mt-4 text-sm font-bold text-white">Tagoloan Dispatch Hub</p>
                        <p class="mt-1.5 text-sm text-slate-400">Poblacion &amp; Baluarte, Tagoloan, Misamis Oriental</p>
                        <p class="mt-1.5 text-sm text-slate-400">Monday – Sunday · 24/7 Live Booking</p>
                        <p class="mt-1.5 text-sm text-slate-400">
                            <a href="mailto:riderhub.misor@gmail.com" class="transition hover:text-violet-300">riderhub.misor@gmail.com</a>
                        </p>
                    </div>

                    <!-- Column 3: Quick Links -->
                    <div>
                        <h3 class="text-xs font-extrabold uppercase tracking-widest text-violet-400">Quick Links</h3>
                        <ul class="mt-4 space-y-2.5 text-sm">
                            <li><a href="{{ route('login') }}" class="text-slate-300 transition hover:text-violet-300">Sign In to Portal</a></li>
                            <li><a href="{{ route('register') }}" class="text-slate-300 transition hover:text-violet-300">Create Account</a></li>
                            <li><a href="#about-us" class="text-slate-300 transition hover:text-violet-300">About RiderHub</a></li>
                            <li><a href="#services" class="text-slate-300 transition hover:text-violet-300">Ride &amp; Delivery Services</a></li>
                            <li><a href="#how-it-works" class="text-slate-300 transition hover:text-violet-300">How It Works</a></li>
                            <li><a href="#faq" class="text-slate-300 transition hover:text-violet-300">Common Questions &amp; FAQ</a></li>
                            <li><a href="{{ asset('downloads/RiderHub.apk') }}" download="RiderHub.apk" class="font-semibold text-emerald-400 transition hover:text-emerald-300">📱 Download Android App (APK)</a></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-10 border-t border-slate-800/90 pt-6 text-center text-xs text-slate-500">
                    &copy; {{ date('Y') }} RiderHub Misamis Oriental. Smart Ride-Hailing &amp; Delivery Management System. All rights reserved.
                </div>
            </div>
        </footer>
        <x-ai-assistant />
    </body>
</html>
