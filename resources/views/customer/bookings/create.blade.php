<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.dashboard') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50" aria-label="Back to dashboard">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6" /></svg>
            </a>
            <div>
                <p class="text-sm font-semibold text-indigo-600">Customer workspace</p>
                <h1 class="mt-0.5 text-2xl font-extrabold tracking-tight text-slate-950">Create a booking</h1>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-indigo-100 bg-indigo-50/70 px-5 py-4 text-sm leading-6 text-indigo-900">
            Select your service and mark pickup and drop-off points on the map. You can drag either pin to fine-tune the location.
        </div>

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <p class="font-bold">Please check the booking details:</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('customer.bookings.store') }}" class="space-y-6" id="booking-form">
            @csrf

            <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-bold text-slate-950">Choose your route</h2>
                                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-0.5 text-[11px] font-bold text-indigo-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    {{ $mapConfig['region_name'] ?? 'Misamis Oriental' }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Click anywhere in Misamis Oriental or tap a quick location below to place the pin.</p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" data-select-point="pickup" class="rounded-lg bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700 ring-1 ring-indigo-200">Set pickup</button>
                            <button type="button" data-select-point="dropoff" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">Set drop-off</button>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 border-b border-slate-100 bg-slate-50/70 px-5 py-2.5">
                        <span class="mr-1 text-[11px] font-semibold text-slate-400">Quick pins:</span>
                        <button type="button" data-quick-pin='{"name":"Tagoloan, Misamis Oriental","lat":8.5378,"lng":124.7536}' class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Tagoloan</button>
                        <button type="button" data-quick-pin='{"name":"Cagayan de Oro City, Misamis Oriental","lat":8.4810,"lng":124.6498}' class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Cagayan de Oro</button>
                        <button type="button" data-quick-pin='{"name":"Villanueva, Misamis Oriental","lat":8.5844,"lng":124.7775}' class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Villanueva</button>
                        <button type="button" data-quick-pin='{"name":"Jasaan, Misamis Oriental","lat":8.6544,"lng":124.7535}' class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Jasaan</button>
                        <button type="button" data-quick-pin='{"name":"Opol, Misamis Oriental","lat":8.5194,"lng":124.5720}' class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Opol</button>
                        <button type="button" data-quick-pin='{"name":"El Salvador City, Misamis Oriental","lat":8.5633,"lng":124.5228}' class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">El Salvador</button>
                        <button type="button" data-quick-pin='{"name":"Balingasag, Misamis Oriental","lat":8.7436,"lng":124.7764}' class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Balingasag</button>
                        <button type="button" data-quick-pin='{"name":"Gingoog City, Misamis Oriental","lat":8.8211,"lng":125.1003}' class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Gingoog</button>
                    </div>
                    <div id="booking-map" class="h-72 w-full bg-slate-100 sm:h-96" aria-label="Interactive pickup and drop-off map for Misamis Oriental"></div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 px-5 py-3 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                        <span>Map data &copy; OpenStreetMap contributors</span>
                        <button type="button" id="use-current-location" class="inline-flex items-center gap-1.5 self-start font-bold text-indigo-600 hover:text-indigo-800 sm:self-auto">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="8" stroke-width="1.8" /><circle cx="12" cy="12" r="2" stroke-width="1.8" /><path stroke-linecap="round" stroke-width="1.8" d="M12 2v2m0 16v2M2 12h2m16 0h2" /></svg>
                            Use my current location
                        </button>
                    </div>
                </div>

                <div class="space-y-5">
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                        <h2 class="font-bold text-slate-950">Service and payment</h2>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="type" class="block text-sm font-semibold text-slate-700">What do you need?</label>
                                <select id="type" name="type" required class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                                    <option value="ride" @selected(old('type', 'ride') === 'ride')>Book a ride</option>
                                    <option value="delivery" @selected(old('type') === 'delivery')>Send a package</option>
                                </select>
                            </div>
                            <div>
                                <label for="payment_method" class="block text-sm font-semibold text-slate-700">Payment method</label>
                                <select id="payment_method" name="payment_method" required class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                                    <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>Cash to rider</option>
                                    <option value="online" @selected(old('payment_method') === 'online')>GCash, Maya, or card via PayMongo</option>
                                </select>
                                <p class="mt-2 text-xs leading-5 text-slate-500">Pay securely on PayMongo with GCash, Maya (formerly PayMaya), or Visa/Mastercard credit, debit, and prepaid cards. Your rider request is sent after payment is confirmed.</p>
                            </div>
                            <div id="delivery-fields" class="{{ old('type') === 'delivery' ? '' : 'hidden' }}">
                                <label for="item_description" class="block text-sm font-semibold text-slate-700">Package description</label>
                                <textarea id="item_description" name="item_description" rows="3" maxlength="255" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" placeholder="What are you sending?">{{ old('item_description') }}</textarea>
                                <x-input-error :messages="$errors->get('item_description')" class="mt-2" />
                            </div>
                            <div x-data="{ code: @js(old('promo_code', '')) }">
                                <label for="promo_code" class="block text-sm font-semibold text-slate-700">Promo / Voucher code <span class="font-normal text-slate-400">(optional)</span></label>
                                <input id="promo_code" name="promo_code" type="text" x-model="code" maxlength="40" placeholder="e.g. TAGOLOAN20" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm uppercase text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach (config('riderhub.promos', []) as $promoKey => $promoInfo)
                                        <button type="button" @click="code = '{{ $promoKey }}'" class="rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-0.5 text-[11px] font-bold text-indigo-700 transition hover:bg-indigo-100">
                                            {{ $promoKey }} · {{ $promoInfo['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="card-interactive rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm sm:p-6">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-bold text-slate-950">Estimated fare</p>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-extrabold text-emerald-700">● Auto</span>
                                </div>
                                <p id="fare-distance-meta" class="mt-1 text-xs font-medium text-slate-500">Updates automatically as you pick locations</p>
                            </div>
                            <div class="text-right">
                                <p id="fare-estimate" class="text-2xl font-extrabold text-indigo-700 transition-transform duration-200">₱—</p>
                                <p id="fare-promo-meta" class="hidden text-[11px] font-bold text-emerald-600"></p>
                            </div>
                        </div>
                        <p class="mt-4 border-t border-slate-100 pt-4 text-xs leading-5 text-slate-500">Final fare is calculated automatically from your pinned pickup and drop-off coordinates.</p>
                    </section>

                    <x-weather-widget title="Tagoloan Weather Advisory" :lat="old('pickup_lat', $mapConfig['default_pickup']['lat'] ?? 8.5378)" :lng="old('pickup_lng', $mapConfig['default_pickup']['lng'] ?? 124.7536)" />
                </div>
            </section>

            <!-- Saved Favorite Places Quick-Fill -->
            <section
                x-data="{
                    target: 'pickup',
                    places: [
                        { icon: '🏠', label: 'Home (Baluarte)', address: 'Baluarte, Tagoloan, Misamis Oriental', lat: 8.5450, lng: 124.7640 },
                        { icon: '🎓', label: 'School (TCC)', address: 'Tagoloan Community College, Tagoloan, Misamis Oriental', lat: 8.5389, lng: 124.7568 },
                        { icon: '🏛️', label: 'Tagoloan Plaza', address: 'Poblacion, Tagoloan, Misamis Oriental', lat: 8.5378, lng: 124.7536 },
                        { icon: '🏢', label: 'Work (SM CDO)', address: 'SM CDO Downtown, Cagayan de Oro, Misamis Oriental', lat: 8.4856, lng: 124.6565 },
                    ],
                    applyPlace(p) {
                        window.dispatchEvent(new CustomEvent('riderhub-set-point', {
                            detail: {
                                name: this.target,
                                lat: p.lat,
                                lng: p.lng,
                                address: p.address,
                            },
                        }));
                        if (this.target === 'pickup') {
                            this.target = 'dropoff';
                        }
                    }
                }"
                class="rounded-2xl border border-indigo-100 bg-indigo-50/50 p-4 shadow-xs"
            >
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-indigo-700">📍 Saved Favorite Places</p>
                    <div class="inline-flex rounded-xl border border-indigo-200 bg-white p-0.5 text-[11px] font-bold">
                        <button type="button" @click="target = 'pickup'" :class="target === 'pickup' ? 'bg-indigo-600 text-white' : 'text-slate-600'" class="rounded-lg px-2.5 py-1 transition">Fill Pickup</button>
                        <button type="button" @click="target = 'dropoff'" :class="target === 'dropoff' ? 'bg-amber-500 text-white' : 'text-slate-600'" class="rounded-lg px-2.5 py-1 transition">Fill Drop-off</button>
                    </div>
                </div>
                <div class="mt-2.5 flex flex-wrap gap-2">
                    <template x-for="place in places" :key="place.label">
                        <button type="button" @click="applyPlace(place)" class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200/80 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-xs transition hover:-translate-y-0.5 hover:border-indigo-400 hover:text-indigo-700">
                            <span x-text="place.icon"></span>
                            <span x-text="place.label"></span>
                        </button>
                    </template>
                </div>
            </section>

            <section class="grid gap-5 lg:grid-cols-2">
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-50 text-sm font-extrabold text-indigo-700">1</span>
                        <div><h2 class="font-bold text-slate-950">Pickup point</h2><p class="text-xs text-slate-500">Where should the rider meet you?</p></div>
                    </div>
                    <label for="pickup_address" class="mt-5 block text-sm font-semibold text-slate-700">Pickup address</label>
                    <div class="mt-2 flex gap-2">
                        <input type="text" id="pickup_address" name="pickup_address" list="tagoloan-places" value="{{ old('pickup_address') }}" required maxlength="255" placeholder="e.g. Poblacion, Baluarte, Casinglot, Tagoloan"
                               class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <button type="button" data-geocode="pickup" class="shrink-0 rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Find</button>
                    </div>
                    <x-input-error :messages="$errors->get('pickup_address')" class="mt-2" />
                    <input type="hidden" id="pickup_lat" name="pickup_lat" value="{{ old('pickup_lat', $mapConfig['default_pickup']['lat'] ?? 8.5378) }}">
                    <input type="hidden" id="pickup_lng" name="pickup_lng" value="{{ old('pickup_lng', $mapConfig['default_pickup']['lng'] ?? 124.7536) }}">
                    <p data-coordinates="pickup" class="mt-3 text-xs text-slate-400"></p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-50 text-sm font-extrabold text-amber-700">2</span>
                        <div><h2 class="font-bold text-slate-950">Drop-off point</h2><p class="text-xs text-slate-500">Where are you headed?</p></div>
                    </div>
                    <label for="dropoff_address" class="mt-5 block text-sm font-semibold text-slate-700">Drop-off address</label>
                    <div class="mt-2 flex gap-2">
                        <input type="text" id="dropoff_address" name="dropoff_address" list="tagoloan-places" value="{{ old('dropoff_address') }}" required maxlength="255" placeholder="e.g. Sta. Ana, Natumolan, Phividec, Tagoloan"
                               class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <button type="button" data-geocode="dropoff" class="shrink-0 rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Find</button>
                    </div>
                    <x-input-error :messages="$errors->get('dropoff_address')" class="mt-2" />
                    <input type="hidden" id="dropoff_lat" name="dropoff_lat" value="{{ old('dropoff_lat', $mapConfig['default_dropoff']['lat'] ?? 8.5515) }}">
                    <input type="hidden" id="dropoff_lng" name="dropoff_lng" value="{{ old('dropoff_lng', $mapConfig['default_dropoff']['lng'] ?? 124.7615) }}">
                    <p data-coordinates="dropoff" class="mt-3 text-xs text-slate-400"></p>
                </article>
            </section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('customer.bookings.index') }}" class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Confirm booking
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                </button>
            </div>
            <datalist id="tagoloan-places">
                <option value="Poblacion, Tagoloan, Misamis Oriental"></option>
                <option value="Baluarte, Tagoloan, Misamis Oriental"></option>
                <option value="Casinglot, Tagoloan, Misamis Oriental"></option>
                <option value="Sta. Ana, Tagoloan, Misamis Oriental"></option>
                <option value="Natumolan, Tagoloan, Misamis Oriental"></option>
                <option value="Phividec Industrial Estate, Tagoloan, Misamis Oriental"></option>
                <option value="Tagoloan Community College, Tagoloan, Misamis Oriental"></option>
                <option value="Cagayan de Oro City, Misamis Oriental"></option>
                <option value="Limketkai Center, Cagayan de Oro, Misamis Oriental"></option>
                <option value="SM CDO Downtown, Cagayan de Oro, Misamis Oriental"></option>
                <option value="Villanueva, Misamis Oriental"></option>
                <option value="Jasaan, Misamis Oriental"></option>
                <option value="Opol, Misamis Oriental"></option>
                <option value="El Salvador City, Misamis Oriental"></option>
                <option value="Claveria, Misamis Oriental"></option>
                <option value="Balingasag, Misamis Oriental"></option>
                <option value="Initao, Misamis Oriental"></option>
                <option value="Laguindingan Airport, Misamis Oriental"></option>
                <option value="Gingoog City, Misamis Oriental"></option>
            </datalist>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mapCfg = @json($mapConfig);
            const serviceBounds = L.latLngBounds(
                [mapCfg.bounds.south, mapCfg.bounds.west],
                [mapCfg.bounds.north, mapCfg.bounds.east],
            );
            const map = L.map('booking-map', {
                center: [mapCfg.center.lat, mapCfg.center.lng],
                zoom: mapCfg.default_zoom || 14,
                minZoom: mapCfg.min_zoom || 12,
                maxBounds: serviceBounds,
                maxBoundsViscosity: 0.9,
            });
            const tileLayers = {
                'Street Map': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri',
                    maxZoom: 19,
                }),
                'Satellite': L.layerGroup([
                    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        attribution: 'Tiles &copy; Esri',
                        maxZoom: 19,
                    }),
                    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 19,
                    }),
                    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 19,
                    }),
                ]),
                'OpenStreetMap': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19,
                }),
            };
            tileLayers['Street Map'].addTo(map);
            L.control.layers(tileLayers, null, { position: 'topright', collapsed: false }).addTo(map);

            const points = {
                pickup: {
                    marker: L.marker([Number(document.getElementById('pickup_lat').value), Number(document.getElementById('pickup_lng').value)], { draggable: true }).addTo(map),
                    lat: document.getElementById('pickup_lat'),
                    lng: document.getElementById('pickup_lng'),
                    address: document.getElementById('pickup_address'),
                },
                dropoff: {
                    marker: L.marker([Number(document.getElementById('dropoff_lat').value), Number(document.getElementById('dropoff_lng').value)], { draggable: true }).addTo(map),
                    lat: document.getElementById('dropoff_lat'),
                    lng: document.getElementById('dropoff_lng'),
                    address: document.getElementById('dropoff_address'),
                },
            };
            let selectedPoint = 'pickup';
            const fareOutput = document.getElementById('fare-estimate');
            const fareDistanceMeta = document.getElementById('fare-distance-meta');
            const farePromoMeta = document.getElementById('fare-promo-meta');
            const promoInput = document.getElementById('promo_code');
            const promosConfig = @json(config('riderhub.promos', []));
            const baseFare = Number(@json($fareBase));
            const ratePerKm = Number(@json($farePerKm));

            const knownPlaces = {
                'poblacion, tagoloan, misamis oriental': { lat: 8.5378, lng: 124.7536 },
                'baluarte, tagoloan, misamis oriental': { lat: 8.5450, lng: 124.7640 },
                'casinglot, tagoloan, misamis oriental': { lat: 8.5267, lng: 124.7574 },
                'sta. ana, tagoloan, misamis oriental': { lat: 8.5553, lng: 124.7706 },
                'natumolan, tagoloan, misamis oriental': { lat: 8.5515, lng: 124.7615 },
                'phividec industrial estate, tagoloan, misamis oriental': { lat: 8.5620, lng: 124.7750 },
                'tagoloan community college, tagoloan, misamis oriental': { lat: 8.5389, lng: 124.7568 },
                'tagoloan, misamis oriental': { lat: 8.5378, lng: 124.7536 },
                'cagayan de oro city, misamis oriental': { lat: 8.4810, lng: 124.6498 },
                'limketkai center, cagayan de oro, misamis oriental': { lat: 8.4806, lng: 124.6562 },
                'sm cdo downtown, cagayan de oro, misamis oriental': { lat: 8.4856, lng: 124.6565 },
                'villanueva, misamis oriental': { lat: 8.5844, lng: 124.7775 },
                'jasaan, misamis oriental': { lat: 8.6544, lng: 124.7535 },
                'opol, misamis oriental': { lat: 8.5194, lng: 124.5720 },
                'el salvador city, misamis oriental': { lat: 8.5633, lng: 124.5228 },
                'claveria, misamis oriental': { lat: 8.6100, lng: 124.8944 },
                'balingasag, misamis oriental': { lat: 8.7436, lng: 124.7764 },
                'initao, misamis oriental': { lat: 8.4972, lng: 124.3075 },
                'laguindingan airport, misamis oriental': { lat: 8.6122, lng: 124.4565 },
                'gingoog city, misamis oriental': { lat: 8.8211, lng: 125.1003 },
            };

            function updateFare() {
                const startLat = Number(points.pickup.lat.value) * Math.PI / 180;
                const endLat = Number(points.dropoff.lat.value) * Math.PI / 180;
                const deltaLat = endLat - startLat;
                const deltaLng = (Number(points.dropoff.lng.value) - Number(points.pickup.lng.value)) * Math.PI / 180;
                const a = Math.sin(deltaLat / 2) ** 2 + Math.cos(startLat) * Math.cos(endLat) * Math.sin(deltaLng / 2) ** 2;
                const distance = 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                const rawFare = baseFare + distance * ratePerKm;

                let finalFare = rawFare;
                let discount = 0;
                const code = (promoInput?.value || '').trim().toUpperCase();
                if (code && promosConfig[code]) {
                    const promo = promosConfig[code];
                    if (promo.type === 'percent') {
                        discount = Math.min(Number(promo.max_discount || 999), rawFare * (Number(promo.value) / 100));
                    } else if (promo.type === 'fixed') {
                        discount = Number(promo.value || 0);
                    }
                    finalFare = Math.max(baseFare, rawFare - discount);
                }

                fareOutput.textContent = `₱${finalFare.toFixed(2)}`;
                if (fareDistanceMeta) {
                    const eta = Math.max(3, Math.round(distance * 2.2 + 3));
                    fareDistanceMeta.textContent = `~${distance.toFixed(1)} km · est. ${eta} mins travel`;
                }
                if (farePromoMeta) {
                    if (discount > 0) {
                        farePromoMeta.textContent = `${code} applied (-₱${discount.toFixed(2)})`;
                        farePromoMeta.classList.remove('hidden');
                    } else {
                        farePromoMeta.classList.add('hidden');
                    }
                }
            }

            if (promoInput) {
                promoInput.addEventListener('input', updateFare);
                promoInput.addEventListener('change', updateFare);
            }
            document.querySelectorAll('[id="promo_code"] ~ div button').forEach((btn) => {
                btn.addEventListener('click', () => setTimeout(updateFare, 20));
            });

            window.addEventListener('riderhub-set-point', (event) => {
                const { name, lat, lng, address } = event.detail || {};
                if (!points[name]) return;
                if (address) points[name].address.value = address;
                setPoint(name, Number(lat), Number(lng));
                map.setView([Number(lat), Number(lng)], 15);
            });

            let weatherTimer = null;
            function setPoint(name, lat, lng) {
                const point = points[name];
                point.lat.value = Number(lat).toFixed(7);
                point.lng.value = Number(lng).toFixed(7);
                point.marker.setLatLng([lat, lng]);
                document.querySelector(`[data-coordinates="${name}"]`).textContent = `${Number(lat).toFixed(5)}, ${Number(lng).toFixed(5)}`;
                updateFare();
                if (name === 'pickup') {
                    window.clearTimeout(weatherTimer);
                    weatherTimer = window.setTimeout(() => {
                        window.dispatchEvent(new CustomEvent('riderhub-weather-coords', { detail: { lat: Number(lat), lng: Number(lng) } }));
                    }, 500);
                }
            }

            Object.entries(points).forEach(([name, point]) => {
                setPoint(name, Number(point.lat.value), Number(point.lng.value));
                point.marker.on('dragend', (event) => {
                    const position = event.target.getLatLng();
                    setPoint(name, position.lat, position.lng);
                });
                document.querySelector(`[data-select-point="${name}"]`).addEventListener('click', () => {
                    selectedPoint = name;
                    document.querySelectorAll('[data-select-point]').forEach((button) => {
                        button.className = button.dataset.selectPoint === name
                            ? 'rounded-lg bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700 ring-1 ring-indigo-200'
                            : 'rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50';
                    });
                });
            });

            document.querySelectorAll('[data-quick-pin]').forEach((button) => {
                button.addEventListener('click', () => {
                    const place = JSON.parse(button.dataset.quickPin);
                    setPoint(selectedPoint, place.lat, place.lng);
                    points[selectedPoint].address.value = place.name;
                    map.setView([place.lat, place.lng], 15);
                });
            });

            map.on('click', (event) => setPoint(selectedPoint, event.latlng.lat, event.latlng.lng));

            async function findAddress(name, silent = false) {
                const point = points[name];
                const address = point.address.value.trim();
                if (!address) {
                    if (!silent) {
                        point.address.focus();
                        point.address.setCustomValidity('Enter an address to search for it.');
                        point.address.reportValidity();
                        point.address.setCustomValidity('');
                    }
                    return;
                }

                // 0ms instant lookup for known Tagoloan / Misamis Oriental places
                const normalized = address.toLowerCase();
                const instantMatch = knownPlaces[normalized]
                    || Object.entries(knownPlaces).find(([k]) => normalized.length >= 4 && (k.startsWith(normalized) || normalized.includes(k.split(',')[0])))?.[1];
                if (instantMatch) {
                    setPoint(name, instantMatch.lat, instantMatch.lng);
                    map.setView([instantMatch.lat, instantMatch.lng], 15);
                    return;
                }

                const button = document.querySelector(`[data-geocode="${name}"]`);
                if (button) {
                    button.disabled = true;
                    button.textContent = '…';
                }
                try {
                    const query = /misamis oriental|cagayan de oro/i.test(address) ? address : `${address}, ${mapCfg.region_name}`;
                    const url = new URL('https://nominatim.openstreetmap.org/search');
                    url.search = new URLSearchParams({
                        format: 'jsonv2',
                        limit: '1',
                        countrycodes: 'ph',
                        bounded: '1',
                        viewbox: `${mapCfg.bounds.west},${mapCfg.bounds.north},${mapCfg.bounds.east},${mapCfg.bounds.south}`,
                        q: query,
                    });
                    const response = await fetch(url, { headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error('Address search is unavailable.');
                    const results = await response.json();
                    if (!results.length) {
                        if (!silent) {
                            point.address.setCustomValidity(`No location found in ${mapCfg.region_name}. Try a barangay name or tap directly on the map.`);
                            point.address.reportValidity();
                            point.address.setCustomValidity('');
                        }
                        return;
                    }
                    setPoint(name, Number(results[0].lat), Number(results[0].lon));
                    map.setView([Number(results[0].lat), Number(results[0].lon)], 15);
                } catch (error) {
                    if (!silent) {
                        point.address.setCustomValidity('Could not search this address right now. You can select the location on the map.');
                        point.address.reportValidity();
                        point.address.setCustomValidity('');
                    }
                } finally {
                    if (button) {
                        button.disabled = false;
                        button.textContent = 'Find';
                    }
                }
            }

            document.querySelectorAll('[data-geocode]').forEach((button) => {
                button.addEventListener('click', () => findAddress(button.dataset.geocode, false));
            });

            // Automatic fare & pin update whenever user picks or types a location (no need to tap Find!)
            const autoTimers = { pickup: null, dropoff: null };
            ['pickup', 'dropoff'].forEach((name) => {
                const input = points[name].address;
                const triggerAuto = (immediate = false) => {
                    window.clearTimeout(autoTimers[name]);
                    const val = input.value.trim().toLowerCase();
                    if (val.length < 3) return;
                    if (immediate || knownPlaces[val]) {
                        findAddress(name, true);
                        return;
                    }
                    autoTimers[name] = window.setTimeout(() => findAddress(name, true), 650);
                };
                input.addEventListener('input', () => triggerAuto(false));
                input.addEventListener('change', () => triggerAuto(true));
            });

            document.getElementById('type').addEventListener('change', (event) => {
                document.getElementById('delivery-fields').classList.toggle('hidden', event.target.value !== 'delivery');
            });

            document.getElementById('use-current-location').addEventListener('click', () => {
                if (!navigator.geolocation) return;
                navigator.geolocation.getCurrentPosition(
                    ({ coords }) => {
                        if (!serviceBounds.contains([coords.latitude, coords.longitude])) {
                            window.alert(`Your current GPS location appears to be outside ${mapCfg.region_name}. Please pin your location directly on the Tagoloan map.`);
                            return;
                        }
                        setPoint('pickup', coords.latitude, coords.longitude);
                        map.setView([coords.latitude, coords.longitude], 15);
                    },
                    () => window.alert('Unable to access your current location. Check browser location permissions and try again.'),
                    { enableHighAccuracy: true, timeout: 10000 },
                );
            });
        });
    </script>
</x-app-layout>
