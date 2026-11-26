@props([
    'name' => null,
    'role' => 'guest', // guest | customer | rider | admin
    'variant' => 'dashboard', // dashboard | hero
])

@php
    $pht = now()->timezone('Asia/Manila');
    $hour = (int) $pht->format('G');
    $initialGreeting = match (true) {
        $hour >= 5 && $hour < 12 => 'Good morning',
        $hour >= 12 && $hour < 18 => 'Good afternoon',
        default => 'Good evening',
    };
    $initialIcon = match (true) {
        $hour >= 5 && $hour < 12 => '☀️',
        $hour >= 12 && $hour < 18 => '🌤️',
        default => '🌙',
    };
    $initialPeriod = match (true) {
        $hour >= 5 && $hour < 12 => 'morning',
        $hour >= 12 && $hour < 18 => 'afternoon',
        default => 'evening',
    };
    $firstName = $name ? \Illuminate\Support\Str::before(trim($name), ' ') : null;
    $initialTime = $pht->format('g:i:s A');
    $initialDate = $pht->format('D, M j');
@endphp

@if ($variant === 'hero')
    <div
        x-data="riderHubRealtimeGreeting(@js($role), @js($firstName))"
        x-init="start()"
        class="inline-flex flex-wrap items-center gap-2 rounded-full border px-3.5 py-1.5 text-xs font-bold shadow-md backdrop-blur-md transition-all duration-500 sm:text-sm"
        :class="pillClass"
    >
        <span class="relative flex h-2.5 w-2.5 shrink-0">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-75" :class="dotPingClass"></span>
            <span class="relative inline-flex h-2.5 w-2.5 rounded-full" :class="dotClass"></span>
        </span>
        <span class="text-sm leading-none" x-text="icon">{{ $initialIcon }}</span>
        <span x-text="greeting + ' · Ready to ride?'">{{ $initialGreeting }} · Ready to ride?</span>
        <span class="opacity-40">•</span>
        <span class="font-mono text-[11px] font-extrabold tracking-tight opacity-90 sm:text-xs" x-text="timeStr + ' PHT'">{{ $initialTime }} PHT</span>
    </div>
@else
    <div
        x-data="riderHubRealtimeGreeting(@js($role), @js($firstName))"
        x-init="start()"
        class="space-y-1.5"
    >
        <div class="flex flex-wrap items-center gap-2">
            <span
                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[11px] font-extrabold tracking-wide shadow-xs transition-all duration-500"
                :class="pillClass"
            >
                <span class="relative flex h-2 w-2 shrink-0">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-75" :class="dotPingClass"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full" :class="dotClass"></span>
                </span>
                <span x-text="icon">{{ $initialIcon }}</span>
                <span x-text="periodLabel">{{ ucfirst($initialPeriod) }} Shift</span>
                <span class="opacity-40">·</span>
                <span class="font-mono" x-text="dateStr + ' · ' + timeStr + ' PHT'">{{ $initialDate }} · {{ $initialTime }} PHT</span>
            </span>
            {{ $slot }}
        </div>

        <h1 class="text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">
            <span x-text="headline">{{ $initialGreeting }}{{ $firstName ? ', '.$firstName : '' }}</span>
        </h1>
        <p class="text-sm text-slate-500" x-text="subtitle">
            @if ($role === 'rider')
                Stay safe on the road — manage your active booking or catch nearby requests.
            @elseif ($role === 'admin')
                Real-time Misamis Oriental operations overview, live fleet telemetry, and records.
            @else
                Plan a ride or send a package anywhere across Tagoloan &amp; Misamis Oriental.
            @endif
        </p>
    </div>
@endif

@once
    <script>
        function riderHubRealtimeGreeting(role, firstName) {
            return {
                role: role || 'guest',
                firstName: firstName || '',
                greeting: 'Good day',
                icon: '✨',
                period: 'morning',
                periodLabel: 'Live PHT',
                timeStr: '',
                dateStr: '',
                timer: null,
                get headline() {
                    return this.firstName ? `${this.greeting}, ${this.firstName}` : this.greeting;
                },
                get subtitle() {
                    if (this.role === 'rider') {
                        if (this.period === 'morning') return 'Morning rush is on — catch early commuters & express deliveries safely.';
                        if (this.period === 'afternoon') return 'Afternoon shift active — manage your trips and pick up nearby requests.';
                        return 'Evening shift active — ride safe tonight and track live bookings in real time.';
                    }
                    if (this.role === 'admin') {
                        if (this.period === 'morning') return 'Morning operations live — monitor fleet dispatch, riders, and bookings.';
                        if (this.period === 'afternoon') return 'Afternoon operations live — track active trips, payments, and rider performance.';
                        return 'Evening operations live — real-time Misamis Oriental fleet & safety overview.';
                    }
                    if (this.period === 'morning') return 'Start your morning right — book a quick ride or express parcel delivery.';
                    if (this.period === 'afternoon') return 'Need a lift or quick errand? Verified RiderHub riders are ready nearby.';
                    return 'Safe evening rides & express deliveries across Tagoloan & Misamis Oriental.';
                },
                get pillClass() {
                    if (this.period === 'morning') {
                        return 'border-amber-300/70 bg-gradient-to-r from-amber-50 via-orange-50 to-yellow-50 text-amber-900 shadow-amber-500/10';
                    }
                    if (this.period === 'afternoon') {
                        return 'border-sky-300/70 bg-gradient-to-r from-sky-50 via-indigo-50 to-violet-50 text-indigo-900 shadow-indigo-500/10';
                    }
                    return 'border-violet-400/40 bg-gradient-to-r from-indigo-950 via-violet-950 to-slate-900 text-violet-100 shadow-violet-900/25';
                },
                get dotPingClass() {
                    if (this.period === 'morning') return 'bg-amber-400';
                    if (this.period === 'afternoon') return 'bg-sky-400';
                    return 'bg-emerald-400';
                },
                get dotClass() {
                    if (this.period === 'morning') return 'bg-amber-500';
                    if (this.period === 'afternoon') return 'bg-indigo-500';
                    return 'bg-emerald-400';
                },
                tick() {
                    try {
                        const now = new Date();
                        const hour = Number(new Intl.DateTimeFormat('en-US', {
                            timeZone: 'Asia/Manila',
                            hour: 'numeric',
                            hour12: false,
                        }).format(now)) % 24;

                        this.timeStr = new Intl.DateTimeFormat('en-PH', {
                            timeZone: 'Asia/Manila',
                            hour: 'numeric',
                            minute: '2-digit',
                            second: '2-digit',
                            hour12: true,
                        }).format(now);

                        this.dateStr = new Intl.DateTimeFormat('en-PH', {
                            timeZone: 'Asia/Manila',
                            weekday: 'short',
                            month: 'short',
                            day: 'numeric',
                        }).format(now);

                        if (hour >= 5 && hour < 12) {
                            this.period = 'morning';
                            this.greeting = 'Good morning';
                            this.icon = '☀️';
                            this.periodLabel = 'Morning';
                        } else if (hour >= 12 && hour < 18) {
                            this.period = 'afternoon';
                            this.greeting = 'Good afternoon';
                            this.icon = '🌤️';
                            this.periodLabel = 'Afternoon';
                        } else {
                            this.period = 'evening';
                            this.greeting = 'Good evening';
                            this.icon = '🌙';
                            this.periodLabel = 'Evening';
                        }
                    } catch {}
                },
                start() {
                    this.tick();
                    if (this.timer) clearInterval(this.timer);
                    this.timer = setInterval(() => this.tick(), 1000);
                },
            };
        }
    </script>
@endonce
