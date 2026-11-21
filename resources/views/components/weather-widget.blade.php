@props([
    'variant' => 'card',
    'lat' => 8.5378,
    'lng' => 124.7536,
    'title' => 'Tagoloan Live Weather',
])

@if ($variant === 'compact')
    <div
        x-data="riderhubWeather({{ (float) $lat }}, {{ (float) $lng }})"
        x-init="init()"
        class="relative hidden items-center md:inline-flex"
    >
        <button
            type="button"
            @click="open = !open"
            @click.outside="open = false"
            class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50/90 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            aria-label="Live weather status"
        >
            <span class="text-sm leading-none" x-text="emoji"></span>
            <span x-show="loading" class="h-3 w-12 animate-pulse rounded bg-slate-200"></span>
            <span x-show="!loading && weather.available" x-cloak class="tabular-nums font-bold" x-text="`${weather.temperature}°C`"></span>
            <span x-show="!loading && weather.available" x-cloak class="hidden text-slate-500 xl:inline" x-text="weather.label"></span>
            <span x-show="!loading && !weather.available" x-cloak class="text-slate-500">Weather</span>
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-1 scale-95"
            class="absolute right-0 top-full z-40 mt-2 w-72 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl shadow-slate-900/10"
        >
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Live Weather</p>
                    <p class="mt-1 text-lg font-extrabold text-slate-950" x-text="weather.available ? `${weather.temperature}°C · ${weather.label}` : 'Weather unavailable'"></p>
                </div>
                <span class="text-2xl leading-none" x-text="emoji"></span>
            </div>
            <template x-if="weather.available">
                <div class="mt-3 grid grid-cols-3 gap-2 rounded-xl bg-slate-50 p-2.5 text-center text-xs">
                    <div><p class="text-slate-400">Rain</p><p class="mt-0.5 font-bold text-slate-800" x-text="`${weather.rain_chance}%`"></p></div>
                    <div><p class="text-slate-400">Wind</p><p class="mt-0.5 font-bold text-slate-800" x-text="`${weather.wind_kmh} km/h`"></p></div>
                    <div><p class="text-slate-400">Humidity</p><p class="mt-0.5 font-bold text-slate-800" x-text="`${weather.humidity}%`"></p></div>
                </div>
            </template>
            <p class="mt-3 rounded-xl px-3 py-2 text-xs leading-5"
               :class="weather.severity === 'alert' ? 'bg-rose-50 text-rose-800' : (weather.severity === 'caution' ? 'bg-amber-50 text-amber-800' : 'bg-emerald-50 text-emerald-800')"
               x-text="weather.advisory"></p>
        </div>
    </div>
@else
    <section
        x-data="riderhubWeather({{ (float) $lat }}, {{ (float) $lng }})"
        x-init="init()"
        @riderhub-weather-coords.window="fetchWeather($event.detail.lat, $event.detail.lng)"
        {{ $attributes->merge(['class' => 'card-interactive overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm']) }}
    >
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full"
                          :class="weather.severity === 'alert' ? 'bg-rose-500 animate-ping' : (weather.severity === 'caution' ? 'bg-amber-500' : 'bg-emerald-500')"></span>
                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">{{ $title }}</p>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span x-show="loading" class="inline-block h-7 w-24 animate-pulse rounded-lg bg-slate-100"></span>
                    <p x-show="!loading && weather.available" x-cloak class="text-2xl font-extrabold tracking-tight text-slate-950 tabular-nums" x-text="`${weather.temperature}°C`"></p>
                    <p x-show="!loading" x-cloak class="text-sm font-semibold text-slate-600" x-text="weather.label"></p>
                </div>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-2xl shadow-inner" x-text="emoji"></div>
        </div>

        <template x-if="!loading && weather.available">
            <div class="mt-4 grid grid-cols-3 gap-2 rounded-xl bg-slate-50 p-3 text-center text-xs">
                <div>
                    <p class="text-slate-400">Rain chance</p>
                    <p class="mt-0.5 font-bold text-slate-900 tabular-nums" x-text="`${weather.rain_chance}%`"></p>
                </div>
                <div>
                    <p class="text-slate-400">Wind</p>
                    <p class="mt-0.5 font-bold text-slate-900 tabular-nums" x-text="`${weather.wind_kmh} km/h`"></p>
                </div>
                <div>
                    <p class="text-slate-400">Feels like</p>
                    <p class="mt-0.5 font-bold text-slate-900 tabular-nums" x-text="`${weather.feels_like}°C`"></p>
                </div>
            </div>
        </template>

        <p x-show="!loading" x-cloak
           class="mt-3 rounded-xl px-3.5 py-2.5 text-xs font-medium leading-5"
           :class="weather.severity === 'alert' ? 'bg-rose-50 text-rose-800' : (weather.severity === 'caution' ? 'bg-amber-50 text-amber-800' : 'bg-emerald-50 text-emerald-800')"
           x-text="weather.advisory"></p>
    </section>
@endif

@once
    <script>
        window.riderhubWeather = function (initialLat, initialLng) {
            return {
                open: false,
                loading: true,
                weather: {
                    available: false,
                    temperature: null,
                    feels_like: null,
                    humidity: null,
                    wind_kmh: null,
                    rain_chance: null,
                    label: 'Loading weather…',
                    icon: 'cloud',
                    severity: 'good',
                    advisory: 'Checking live road and sky conditions…',
                },
                get emoji() {
                    return {
                        sun: '☀️',
                        moon: '🌙',
                        'partly-cloudy': '⛅',
                        cloud: '☁️',
                        rain: '🌧️',
                        storm: '⛈️',
                    }[this.weather.icon] || '🌤️';
                },
                init() {
                    this.fetchWeather(initialLat, initialLng);
                },
                async fetchWeather(lat, lng) {
                    this.loading = true;
                    try {
                        const url = new URL(@json(route('weather.current')), window.location.origin);
                        url.searchParams.set('lat', Number(lat).toFixed(4));
                        url.searchParams.set('lng', Number(lng).toFixed(4));
                        const response = await fetch(url, { headers: { Accept: 'application/json' } });
                        if (response.ok) {
                            this.weather = await response.json();
                        }
                    } catch (e) {
                        this.weather.label = 'Weather unavailable';
                    } finally {
                        this.loading = false;
                    }
                },
            };
        };
    </script>
@endonce
