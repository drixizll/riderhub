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
                                    {{ $mapConfig['region_name'] ?? 'Tagoloan, Misamis Oriental' }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Click the map or tap a barangay below to place the selected pin.</p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" data-select-point="pickup" class="rounded-lg bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700 ring-1 ring-indigo-200">Set pickup</button>
                            <button type="button" data-select-point="dropoff" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">Set drop-off</button>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 border-b border-slate-100 bg-slate-50/70 px-5 py-2.5">
                        <span class="mr-1 text-[11px] font-semibold text-slate-400">Quick pins:</span>
                        <button type="button" data-quick-pin='{"name":"Poblacion, Tagoloan, Misamis Oriental","lat":8.5378,"lng":124.7536}' class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Poblacion</button>
                        <button type="button" data-quick-pin='{"name":"Baluarte, Tagoloan, Misamis Oriental","lat":8.5515,"lng":124.7615}' class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Baluarte</button>
                        <button type="button" data-quick-pin='{"name":"Casinglot, Tagoloan, Misamis Oriental","lat":8.5245,"lng":124.7495}' class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Casinglot</button>
                        <button type="button" data-quick-pin='{"name":"Sta. Ana, Tagoloan, Misamis Oriental","lat":8.5572,"lng":124.7734}' class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Sta. Ana</button>
                        <button type="button" data-quick-pin='{"name":"Natumolan, Tagoloan, Misamis Oriental","lat":8.5458,"lng":124.7648}' class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Natumolan</button>
                        <button type="button" data-quick-pin='{"name":"Mohon, Tagoloan, Misamis Oriental","lat":8.5635,"lng":124.7568}' class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Mohon</button>
                    </div>
                    <div id="booking-map" class="h-72 w-full bg-slate-100 sm:h-96" aria-label="Interactive pickup and drop-off map for Tagoloan, Misamis Oriental"></div>
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
                        </div>
                    </section>

                    <section class="card-interactive rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                        <div class="flex items-center justify-between">
                            <div><p class="text-sm font-bold text-slate-950">Estimated fare</p><p class="mt-1 text-xs text-slate-500">Calculated from straight-line distance</p></div>
                            <p id="fare-estimate" class="text-2xl font-extrabold text-indigo-700">₱—</p>
                        </div>
                        <p class="mt-4 border-t border-slate-100 pt-4 text-xs leading-5 text-slate-500">Final fare is calculated from the selected coordinates. Actual road distance may differ.</p>
                    </section>

                    <x-weather-widget title="Tagoloan Weather Advisory" :lat="old('pickup_lat', $mapConfig['default_pickup']['lat'] ?? 8.5378)" :lng="old('pickup_lng', $mapConfig['default_pickup']['lng'] ?? 124.7536)" />
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
                <option value="Gracia, Tagoloan, Misamis Oriental"></option>
                <option value="Mohon, Tagoloan, Misamis Oriental"></option>
                <option value="Natumolan, Tagoloan, Misamis Oriental"></option>
                <option value="Rosario, Tagoloan, Misamis Oriental"></option>
                <option value="Sta. Ana, Tagoloan, Misamis Oriental"></option>
                <option value="Sta. Cruz, Tagoloan, Misamis Oriental"></option>
                <option value="Sugbongcogon, Tagoloan, Misamis Oriental"></option>
                <option value="Tagoloan Municipal Hall, Poblacion, Tagoloan"></option>
                <option value="Tagoloan Community College, Baluarte, Tagoloan"></option>
                <option value="Tagoloan Public Market, Poblacion, Tagoloan"></option>
                <option value="Phividec Industrial Estate, Sta. Cruz, Tagoloan"></option>
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
            const baseFare = Number(@json($fareBase));
            const ratePerKm = Number(@json($farePerKm));

            function updateFare() {
                const startLat = Number(points.pickup.lat.value) * Math.PI / 180;
                const endLat = Number(points.dropoff.lat.value) * Math.PI / 180;
                const deltaLat = endLat - startLat;
                const deltaLng = (Number(points.dropoff.lng.value) - Number(points.pickup.lng.value)) * Math.PI / 180;
                const a = Math.sin(deltaLat / 2) ** 2 + Math.cos(startLat) * Math.cos(endLat) * Math.sin(deltaLng / 2) ** 2;
                const distance = 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                fareOutput.textContent = `₱${(baseFare + distance * ratePerKm).toFixed(2)}`;
            }

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

            async function findAddress(name) {
                const point = points[name];
                const address = point.address.value.trim();
                if (!address) {
                    point.address.focus();
                    point.address.setCustomValidity('Enter an address to search for it.');
                    point.address.reportValidity();
                    point.address.setCustomValidity('');
                    return;
                }

                const button = document.querySelector(`[data-geocode="${name}"]`);
                button.disabled = true;
                button.textContent = 'Searching…';
                try {
                    const query = /tagoloan/i.test(address) ? address : `${address}, ${mapCfg.region_name}`;
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
                        point.address.setCustomValidity(`No location found in ${mapCfg.region_name}. Try a barangay name or tap directly on the map.`);
                        point.address.reportValidity();
                        point.address.setCustomValidity('');
                        return;
                    }
                    setPoint(name, Number(results[0].lat), Number(results[0].lon));
                    map.setView([Number(results[0].lat), Number(results[0].lon)], 15);
                } catch (error) {
                    point.address.setCustomValidity('Could not search this address right now. You can select the location on the map.');
                    point.address.reportValidity();
                    point.address.setCustomValidity('');
                } finally {
                    button.disabled = false;
                    button.textContent = 'Find';
                }
            }

            document.querySelectorAll('[data-geocode]').forEach((button) => {
                button.addEventListener('click', () => findAddress(button.dataset.geocode));
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
