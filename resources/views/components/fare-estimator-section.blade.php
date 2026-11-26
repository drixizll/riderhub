@php
    $fareBase = (float) config('riderhub.fare.base', 55.00);
    $farePerKm = (float) config('riderhub.fare.per_km', 18.50);
    $presets = [
        ['label' => 'Casinglot → Poblacion', 'km' => 2.8, 'eta' => 8],
        ['label' => 'Baluarte → Villanueva', 'km' => 5.2, 'eta' => 13],
        ['label' => 'Tagoloan → SM Downtown CDO', 'km' => 13.5, 'eta' => 28],
        ['label' => 'Tagoloan → Limketkai Mall', 'km' => 14.2, 'eta' => 30],
    ];
@endphp

<section id="fare-estimator" class="reveal-on-scroll mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
    <div
        x-data="{
            km: 5.2,
            type: 'ride',
            applyPromo: true,
            base: {{ $fareBase }},
            perKm: {{ $farePerKm }},
            presets: @js($presets),
            get rawFare() {
                return this.base + (Number(this.km) * this.perKm);
            },
            get discount() {
                if (!this.applyPromo) return 0;
                return Math.min(40, this.rawFare * 0.20);
            },
            get finalFare() {
                return Math.max(this.base, this.rawFare - this.discount);
            },
            get etaMins() {
                return Math.max(4, Math.round(Number(this.km) * 2.1 + 3));
            },
            fmt(v) {
                return Number(v).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }"
        class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#120826] via-[#1f1042] to-[#0e071f] p-6 text-white shadow-2xl shadow-indigo-950/30 ring-1 ring-violet-400/25 sm:p-8 lg:p-10"
    >
        <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-fuchsia-500/20 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-16 h-64 w-64 rounded-full bg-violet-500/20 blur-3xl" aria-hidden="true"></div>

        <div class="relative grid gap-8 lg:grid-cols-[1.15fr_0.85fr] lg:items-center">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-amber-300/35 bg-amber-400/15 px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider text-amber-200">
                    <span>⚡</span> Instant Fare Estimator · Misamis Oriental
                </span>
                <h2 class="mt-4 text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                    Know your exact fare <span class="bg-gradient-to-r from-violet-200 via-fuchsia-200 to-amber-200 bg-clip-text text-transparent">before you book.</span>
                </h2>
                <p class="mt-2 text-sm leading-6 text-violet-200/80">
                    Pick a popular Tagoloan–CDO route or slide your distance below. Upfront pricing — zero hidden fees.
                </p>

                <!-- Service Toggle -->
                <div class="mt-5 inline-flex rounded-xl border border-white/15 bg-white/5 p-1">
                    <button type="button" @click="type = 'ride'" :class="type === 'ride' ? 'bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white shadow-md' : 'text-violet-200 hover:text-white'" class="rounded-lg px-4 py-1.5 text-xs font-extrabold transition">🏍️ Passenger Ride</button>
                    <button type="button" @click="type = 'delivery'" :class="type === 'delivery' ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 shadow-md' : 'text-violet-200 hover:text-white'" class="rounded-lg px-4 py-1.5 text-xs font-extrabold transition">📦 Parcel Delivery</button>
                </div>

                <!-- Popular Tagoloan/CDO Route Presets -->
                <div class="mt-5">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-violet-300/80">Popular Local Routes</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <template x-for="p in presets" :key="p.label">
                            <button
                                type="button"
                                @click="km = p.km"
                                :class="Math.abs(Number(km) - p.km) < 0.05 ? 'border-amber-300/70 bg-amber-400/20 text-amber-200' : 'border-white/15 bg-white/5 text-violet-100 hover:border-violet-300/40 hover:bg-white/10'"
                                class="rounded-full border px-3 py-1.5 text-xs font-bold transition active:scale-95"
                            >
                                <span x-text="p.label"></span>
                                <span class="ml-1 opacity-70" x-text="'(' + p.km + ' km)'"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Distance Slider -->
                <div class="mt-6">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="text-violet-200">Trip Distance</span>
                        <span class="rounded-full bg-white/10 px-3 py-1 font-mono text-sm font-black text-amber-300" x-text="Number(km).toFixed(1) + ' km · ~' + etaMins + ' mins'"></span>
                    </div>
                    <input
                        type="range"
                        min="1"
                        max="30"
                        step="0.5"
                        x-model="km"
                        aria-label="Estimated trip distance in kilometers"
                        class="mt-3 h-2 w-full cursor-pointer appearance-none rounded-full bg-white/15 accent-amber-400"
                    >
                    <div class="mt-1.5 flex justify-between text-[10px] font-semibold text-violet-300/60">
                        <span>1 km (Short town ride)</span>
                        <span>15 km (Tagoloan ↔ CDO)</span>
                        <span>30 km (Inter-town)</span>
                    </div>
                </div>
            </div>

            <!-- Live Fare Summary Card -->
            <div class="rounded-3xl border border-white/15 bg-white/10 p-6 backdrop-blur-xl">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-violet-200" x-text="type === 'delivery' ? 'Express Parcel Estimate' : 'Passenger Ride Estimate'"></span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-[10px] font-extrabold text-emerald-300 ring-1 ring-emerald-400/30">● Upfront Rate</span>
                </div>

                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-4xl font-black tracking-tight text-white sm:text-5xl" x-text="'₱' + fmt(finalFare)"></span>
                    <span x-show="applyPromo" class="text-sm font-bold text-violet-300/70 line-through" x-text="'₱' + fmt(rawFare)"></span>
                </div>

                <dl class="mt-5 space-y-2 border-t border-white/10 pt-4 text-xs">
                    <div class="flex justify-between text-violet-200/85">
                        <dt>Base fare</dt>
                        <dd class="font-mono font-bold text-white" x-text="'₱' + fmt(base)"></dd>
                    </div>
                    <div class="flex justify-between text-violet-200/85">
                        <dt x-text="'Distance (' + Number(km).toFixed(1) + ' km × ₱' + fmt(perKm) + ')'"></dt>
                        <dd class="font-mono font-bold text-white" x-text="'₱' + fmt(Number(km) * perKm)"></dd>
                    </div>
                    <div x-show="applyPromo" class="flex justify-between text-emerald-300">
                        <dt>Promo <span class="rounded bg-emerald-400/20 px-1.5 py-0.5 font-mono text-[10px] font-extrabold">TAGOLOAN20</span></dt>
                        <dd class="font-mono font-extrabold" x-text="'-₱' + fmt(discount)"></dd>
                    </div>
                </dl>

                <label class="mt-4 flex cursor-pointer items-center justify-between rounded-xl border border-white/15 bg-white/5 px-3.5 py-2.5 text-xs font-bold text-violet-100">
                    <span>🎟️ Preview with <span class="text-amber-300">TAGOLOAN20</span> (20% off)</span>
                    <input type="checkbox" x-model="applyPromo" class="h-4 w-4 rounded border-white/30 bg-transparent text-amber-400 focus:ring-amber-400">
                </label>

                <a href="{{ route('register') }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-violet-500 via-fuchsia-500 to-amber-400 px-5 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-fuchsia-600/30 transition hover:brightness-110 active:scale-[0.98]">
                    Book This Trip Now
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                </a>
            </div>
        </div>
    </div>
</section>
