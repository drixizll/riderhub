<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Install RiderHub App — Android APK &amp; iOS</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/riderhub.svg') }}?v=3">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('icons/icon-192.png') }}?v=3">
    <meta name="theme-color" content="#7c3aed">
    @include('layouts.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0b0a17] font-sans text-slate-100 antialiased">
    <header class="sticky top-0 z-30 border-b border-white/10 bg-[#0b0a17]/90 backdrop-blur-md">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3.5 sm:px-6">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5 transition hover:opacity-90">
                <img src="{{ asset('icons/icon-192.png') }}?v=3" alt="RiderHub" class="h-9 w-9 rounded-xl shadow-md shadow-violet-600/30">
                <div>
                    <p class="text-base font-extrabold tracking-tight text-white">RiderHub</p>
                    <p class="text-[11px] font-semibold text-violet-400">Mobile App Download</p>
                </div>
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ url('/') }}" class="rounded-xl border border-white/15 bg-white/5 px-3.5 py-2 text-xs font-bold text-slate-200 transition hover:bg-white/10">← Back to Home</a>
                <a href="{{ route('login') }}" class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-3.5 py-2 text-xs font-bold text-white shadow-md shadow-indigo-600/25 transition hover:opacity-95">Log in</a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16">
        <div class="mx-auto max-w-2xl text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-violet-500/30 bg-violet-500/15 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-violet-300">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                Android &amp; iOS Ready
            </span>
            <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-white sm:text-5xl">Get the RiderHub App</h1>
            <p class="mt-3 text-sm leading-6 text-slate-300 sm:text-base">
                Install RiderHub on your Android phone or iPhone for fullscreen rides, express parcel deliveries, live GPS tracking, AI voice navigation, and real-time trip chat across Misamis Oriental.
            </p>
        </div>

        <div class="mt-12 grid gap-6 lg:grid-cols-2">
            <!-- Android Card -->
            <section class="flex flex-col justify-between rounded-3xl border border-emerald-500/25 bg-gradient-to-b from-slate-900/95 to-[#121024] p-6 shadow-2xl sm:p-8">
                <div>
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/15 text-2xl">🤖</span>
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-400">Android Phones</p>
                                <h2 class="text-xl font-extrabold text-white">Download Android APK</h2>
                            </div>
                        </div>
                        <span class="rounded-full bg-emerald-500/15 px-2.5 py-1 text-[11px] font-bold text-emerald-300">v1.0 · 998 KB</span>
                    </div>

                    <p class="mt-4 text-sm leading-6 text-slate-300">
                        Verified fullscreen Trusted Web Activity (<code class="rounded bg-white/10 px-1.5 py-0.5 text-xs text-violet-200">online.riderhub.twa</code>) — installs directly on any Android device with no browser bar.
                    </p>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <a
                            href="{{ asset('downloads/RiderHub.apk') }}"
                            download="RiderHub.apk"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-5 py-3.5 text-sm font-extrabold text-slate-950 shadow-lg shadow-emerald-500/25 transition hover:-translate-y-0.5 hover:opacity-95"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0-4-4m4 4 4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2" /></svg>
                            Download RiderHub.apk
                        </a>
                        <a
                            href="https://cloud.repohive.com/app/w4063otcuvc8"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/5 px-4 py-3.5 text-xs font-bold text-slate-200 transition hover:bg-white/10"
                        >
                            Mirror Link ↗
                        </a>
                    </div>

                    <ol class="mt-6 space-y-2.5 rounded-2xl border border-white/10 bg-black/25 p-4 text-xs leading-5 text-slate-300">
                        <li><strong class="text-white">1.</strong> Tap <strong>Download RiderHub.apk</strong> above.</li>
                        <li><strong class="text-white">2.</strong> Open the downloaded file from your notification bar or Downloads folder.</li>
                        <li><strong class="text-white">3.</strong> If prompted, tap <strong>Allow from this source</strong>, then tap <strong>Install</strong>.</li>
                    </ol>
                </div>

                <!-- Scan QR to Download APK -->
                <div class="mt-6 flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-violet-300">Scan on Android Phone</p>
                        <p class="mt-1 text-xs text-slate-400">Point your phone camera at this QR code to download <strong class="text-slate-200">RiderHub.apk</strong> instantly.</p>
                    </div>
                    <img
                        src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data={{ urlencode(url('downloads/RiderHub.apk')) }}"
                        alt="QR Code to download RiderHub.apk"
                        class="h-20 w-20 shrink-0 rounded-xl bg-white p-1.5 shadow-md"
                    >
                </div>
            </section>

            <!-- iOS / iPhone Card -->
            <section class="flex flex-col justify-between rounded-3xl border border-violet-500/25 bg-gradient-to-b from-slate-900/95 to-[#121024] p-6 shadow-2xl sm:p-8">
                <div>
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-500/15 text-2xl">🍎</span>
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-wider text-violet-400">iPhone &amp; iPad (iOS)</p>
                                <h2 class="text-xl font-extrabold text-white">Install on iOS via Safari</h2>
                            </div>
                        </div>
                        <span class="rounded-full bg-violet-500/15 px-2.5 py-1 text-[11px] font-bold text-violet-300">iOS 15+ · All iPhones</span>
                    </div>

                    <p class="mt-4 text-sm leading-6 text-slate-300">
                        Apple iOS does not use <code class="rounded bg-white/10 px-1.5 py-0.5 text-xs text-violet-200">.apk</code> files. Install RiderHub directly from <strong>Safari</strong> in 10 seconds for a fullscreen native-style app with the RiderHub icon on your Home Screen.
                    </p>

                    <ol class="mt-6 space-y-3 rounded-2xl border border-white/10 bg-black/25 p-4 text-xs leading-5 text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-violet-600 text-[11px] font-extrabold text-white">1</span>
                            <span>Open <strong class="text-white">https://riderhub.online</strong> in <strong>Safari</strong> (not Chrome or Messenger).</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-violet-600 text-[11px] font-extrabold text-white">2</span>
                            <span>Tap the <strong>Share button (⬆️)</strong> at the bottom center of Safari.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-violet-600 text-[11px] font-extrabold text-white">3</span>
                            <span>Scroll down, tap <strong>Add to Home Screen (➕)</strong>, then tap <strong>Add</strong>!</span>
                        </li>
                    </ol>

                    <div x-data="{ copied: false }" class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <a
                            href="{{ url('/') }}"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-indigo-600/25 transition hover:-translate-y-0.5 hover:opacity-95"
                        >
                            Open Web App Now →
                        </a>
                        <button
                            type="button"
                            @click="navigator.clipboard?.writeText('https://riderhub.online'); copied = true; setTimeout(() => copied = false, 2500)"
                            class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/5 px-4 py-3.5 text-xs font-bold text-slate-200 transition hover:bg-white/10"
                        >
                            <span x-text="copied ? 'Copied Link ✓' : 'Copy Safari Link'">Copy Safari Link</span>
                        </button>
                    </div>
                </div>

                <!-- Scan QR for iPhone Safari -->
                <div class="mt-6 flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-violet-300">Scan on iPhone Camera</p>
                        <p class="mt-1 text-xs text-slate-400">Scan to open <strong class="text-slate-200">riderhub.online</strong> directly in Safari and Add to Home Screen.</p>
                    </div>
                    <img
                        src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data={{ urlencode('https://riderhub.online') }}"
                        alt="QR Code to open RiderHub on iPhone Safari"
                        class="h-20 w-20 shrink-0 rounded-xl bg-white p-1.5 shadow-md"
                    >
                </div>
            </section>
        </div>

        <div class="mt-10 grid gap-4 sm:grid-cols-4">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-center">
                <p class="text-lg">🗺️</p>
                <p class="mt-1 text-xs font-bold text-white">Live GPS &amp; AI Voice</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-center">
                <p class="text-lg">💬</p>
                <p class="mt-1 text-xs font-bold text-white">Messenger Trip Chat</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-center">
                <p class="text-lg">💳</p>
                <p class="mt-1 text-xs font-bold text-white">Cash, GCash &amp; Maya</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-center">
                <p class="text-lg">🛡️</p>
                <p class="mt-1 text-xs font-bold text-white">Verified Riders &amp; SOS</p>
            </div>
        </div>
    </main>
</body>
</html>
