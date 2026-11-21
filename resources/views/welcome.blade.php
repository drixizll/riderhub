<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') === 'Laravel' ? 'RiderHub' : config('app.name', 'RiderHub') }} — Every trip, made easier</title>
        <meta name="description" content="Book a ride or send a package with RiderHub. Convenient rides, reliable delivery, and live rider tracking in one place.">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('icons/riderhub.svg') }}">
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
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8" aria-label="Main navigation">
                <a href="{{ url('/') }}" class="transition hover:opacity-90" aria-label="RiderHub home">
                    <x-brand-logo size="md" />
                </a>

                <div class="flex items-center gap-2 sm:gap-4">
                    <a href="#services" class="hidden text-sm font-semibold text-slate-600 transition hover:text-indigo-600 md:inline-flex">Services</a>
                    <a href="#about-us" class="hidden text-sm font-semibold text-slate-600 transition hover:text-indigo-600 md:inline-flex">About us</a>
                    <a href="#how-it-works" class="hidden text-sm font-semibold text-slate-600 transition hover:text-indigo-600 lg:inline-flex">How it works</a>
                    <x-weather-widget variant="compact" />
                    <x-theme-toggle />
                    <x-install-app-button />
                    <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:text-indigo-600">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Sign up</a>
                </div>
            </nav>
        </header>

        <main class="relative z-10">
            <section class="hero-mesh-bg relative overflow-hidden border-b border-indigo-100/80">
                <div class="hero-map-grid pointer-events-none absolute inset-0" aria-hidden="true"></div>
                <div class="aurora-blob aurora-blob-1 pointer-events-none !absolute !opacity-35" aria-hidden="true"></div>
                <div class="aurora-blob aurora-blob-2 pointer-events-none !absolute !opacity-30" aria-hidden="true"></div>
                <svg class="pointer-events-none absolute inset-0 h-full w-full opacity-40" viewBox="0 0 1440 720" fill="none" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                    <path d="M-80 540 C 260 440, 420 220, 760 280 C 1060 330, 1180 140, 1520 90" stroke="url(#hero-line-1)" stroke-width="2.5" class="hero-route-sweep" />
                    <path d="M-60 660 C 310 610, 520 430, 880 460 C 1180 485, 1280 340, 1540 270" stroke="url(#hero-line-2)" stroke-width="2" class="hero-route-sweep" style="animation-duration: 4.5s; animation-direction: reverse;" />
                    <defs>
                        <linearGradient id="hero-line-1" x1="0" y1="0" x2="1440" y2="0" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#8b5cf6" stop-opacity="0"/>
                            <stop offset="50%" stop-color="#7c3aed" stop-opacity="0.7"/>
                            <stop offset="100%" stop-color="#c084fc" stop-opacity="0"/>
                        </linearGradient>
                        <linearGradient id="hero-line-2" x1="0" y1="0" x2="1440" y2="0" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#a855f7" stop-opacity="0"/>
                            <stop offset="50%" stop-color="#6d28d9" stop-opacity="0.5"/>
                            <stop offset="100%" stop-color="#8b5cf6" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                </svg>
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
                        <div class="hero-orb absolute -left-5 -top-5 h-24 w-24 rounded-full bg-amber-200/70 blur-2xl" aria-hidden="true"></div>
                        <div class="hero-orb-delayed absolute -bottom-6 -right-5 h-32 w-32 rounded-full bg-indigo-200/80 blur-2xl" aria-hidden="true"></div>
                        <div class="relative overflow-hidden rounded-3xl border border-white/70 bg-white/90 p-5 shadow-2xl shadow-indigo-950/15 backdrop-blur-md sm:p-7">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-500">Your next trip</p>
                                    <p class="mt-1 text-lg font-bold text-slate-900">A smoother way to go</p>
                                </div>
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">READY WHEN YOU ARE</span>
                            </div>
                            <div class="relative mt-6 h-64 overflow-hidden rounded-2xl bg-indigo-50">
                                <svg class="absolute inset-0 h-full w-full" viewBox="0 0 420 260" fill="none" preserveAspectRatio="none" aria-hidden="true">
                                    <path d="M38 220C100 210 94 140 158 146C220 152 217 62 280 82C334 99 333 42 389 34" stroke="#C7D2FE" stroke-width="24" stroke-linecap="round" />
                                    <path d="M38 220C100 210 94 140 158 146C220 152 217 62 280 82C334 99 333 42 389 34" stroke="#4F46E5" stroke-width="4" stroke-dasharray="7 9" stroke-linecap="round" class="route-dash-animated" />
                                </svg>
                                <div class="absolute left-[7%] top-[72%] flex h-10 w-10 items-center justify-center rounded-full border-4 border-white bg-indigo-600 text-white shadow-lg">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1.5a5.5 5.5 0 0 0-5.5 5.5c0 4.1 5.5 11.5 5.5 11.5S15.5 11.1 15.5 7A5.5 5.5 0 0 0 10 1.5Zm0 7.5a2 2 0 1 1 0-4 2 2 0 0 1 0 4Z" /></svg>
                                </div>
                                <div class="absolute right-[5%] top-[7%] flex h-10 w-10 items-center justify-center rounded-full border-4 border-white bg-amber-400 text-slate-900 shadow-lg">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1.5a5.5 5.5 0 0 0-5.5 5.5c0 4.1 5.5 11.5 5.5 11.5S15.5 11.1 15.5 7A5.5 5.5 0 0 0 10 1.5Zm0 7.5a2 2 0 1 1 0-4 2 2 0 0 1 0 4Z" /></svg>
                                </div>
                                <div class="absolute left-1/2 top-1/2 flex -translate-x-1/2 -translate-y-1/2 items-center gap-3 rounded-xl bg-white px-4 py-3 shadow-lg">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 16h2m10 0h2M7 16l2-6h6l2 6m-8-6 1-3h4l1 3M5 16a2 2 0 1 0 4 0m6 0a2 2 0 1 0 4 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    </span>
                                    <span>
                                        <span class="block text-sm font-bold text-slate-900">Your rider</span>
                                        <span class="block text-xs text-slate-500">On the way</span>
                                    </span>
                                    <span class="ml-2 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-100"></span>
                                </div>
                            </div>
                            <div class="mt-5 flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                                <div>
                                    <p class="text-xs font-medium text-slate-500">From pickup to drop-off</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900">A clear ride, every time</p>
                                </div>
                                <span class="rounded-lg bg-white px-3 py-2 text-sm font-bold text-indigo-700 shadow-sm">Let’s go</span>
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

            <section id="about-us" class="reveal-on-scroll relative overflow-hidden bg-white py-16 sm:py-24">
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

            <section id="how-it-works" class="reveal-on-scroll bg-slate-50 py-16 sm:py-20">
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

            <section class="reveal-on-scroll mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
                <div class="overflow-hidden rounded-3xl bg-indigo-700 px-6 py-12 text-center shadow-xl shadow-indigo-900/10 sm:px-12 sm:py-14">
                    <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Ready to make your next move?</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg leading-7 text-indigo-100">Create your RiderHub account and book your first trip or delivery today.</p>
                    <a href="{{ route('register') }}" class="mt-8 inline-flex items-center justify-center rounded-xl bg-white px-6 py-3.5 text-base font-bold text-indigo-700 shadow-sm transition hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-700">Get started</a>
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-8 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <a href="{{ url('/') }}" class="transition hover:opacity-90">
                    <x-brand-logo size="sm" />
                </a>
                <p>&copy; {{ date('Y') }} RiderHub. All rights reserved.</p>
                <a href="{{ route('login') }}" class="font-semibold text-slate-600 transition hover:text-indigo-600">Already have an account? Log in</a>
            </div>
        </footer>
        <x-ai-assistant />
    </body>
</html>
