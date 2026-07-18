@extends('layouts.app')

@section('title', 'Pengaturan Lokasi - Admin')

@section('header', 'Pengaturan Lokasi Toko')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Success Message --}}
        @if (session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <p class="text-sm text-emerald-400">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Info Card --}}
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-semibold">Lokasi GPS Toko</h3>
                    <p class="text-gray-500 text-xs">Atur titik lokasi dan radius geofence untuk validasi absensi karyawan</p>
                </div>
            </div>
            <div class="bg-indigo-500/5 border border-indigo-500/20 rounded-lg p-3">
                <p class="text-xs text-indigo-300">
                    💡 <strong>Tips:</strong> Klik pada peta untuk menentukan titik lokasi, atau masukkan koordinat secara manual.
                    Radius menentukan jarak maksimal karyawan dari toko agar bisa melakukan absensi.
                </p>
            </div>
        </div>

        {{-- Current Settings Summary --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-4 text-center">
                <p class="text-xs text-gray-500 mb-1">Latitude</p>
                <p class="text-lg font-semibold text-white" id="displayLat">{{ $settings['latitude'] }}</p>
            </div>
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-4 text-center">
                <p class="text-xs text-gray-500 mb-1">Longitude</p>
                <p class="text-lg font-semibold text-white" id="displayLng">{{ $settings['longitude'] }}</p>
            </div>
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-4 text-center">
                <p class="text-xs text-gray-500 mb-1">Radius Geofence</p>
                <p class="text-lg font-semibold text-indigo-400" id="displayRadius">{{ $settings['radius'] }} meter</p>
            </div>
        </div>

        {{-- Map --}}
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 overflow-hidden">
            <div id="map" class="w-full h-80 sm:h-96"></div>
        </div>

        {{-- Edit Form --}}
        <form action="{{ route('admin.location.update') }}" method="POST"
            class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5 space-y-5">
            @csrf
            @method('PUT')

            <h3 class="text-white font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Lokasi
            </h3>

            {{-- Name & Address --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-400 mb-1">Nama Lokasi</label>
                    <input type="text" name="name" id="name"
                        value="{{ old('name', $settings['name']) }}"
                        class="w-full bg-slate-800/80 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-gray-600"
                        placeholder="Nama toko / cabang" required>
                    @error('name')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-400 mb-1">Alamat</label>
                    <input type="text" name="address" id="address"
                        value="{{ old('address', $settings['address']) }}"
                        class="w-full bg-slate-800/80 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-gray-600"
                        placeholder="Alamat lengkap toko">
                    @error('address')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Coordinates --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="latitude" class="block text-sm font-medium text-gray-400 mb-1">Latitude</label>
                    <input type="number" name="latitude" id="latitude" step="0.00000001"
                        value="{{ old('latitude', $settings['latitude']) }}"
                        class="w-full bg-slate-800/80 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono"
                        required>
                    @error('latitude')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="longitude" class="block text-sm font-medium text-gray-400 mb-1">Longitude</label>
                    <input type="number" name="longitude" id="longitude" step="0.00000001"
                        value="{{ old('longitude', $settings['longitude']) }}"
                        class="w-full bg-slate-800/80 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono"
                        required>
                    @error('longitude')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="radius" class="block text-sm font-medium text-gray-400 mb-1">Radius (meter)</label>
                    <input type="number" name="radius" id="radius" min="10" max="5000"
                        value="{{ old('radius', $settings['radius']) }}"
                        class="w-full bg-slate-800/80 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        required>
                    @error('radius')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-xl shadow-lg shadow-indigo-500/25 transition-all duration-300 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Lokasi
                </button>
                <button type="button" onclick="useCurrentLocation()"
                    class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-gray-300 font-medium rounded-xl border border-white/10 transition-all duration-200 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                    Gunakan Lokasi Saat Ini
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        const initLat = {{ $settings['latitude'] }};
        const initLng = {{ $settings['longitude'] }};
        const initRadius = {{ $settings['radius'] }};

        // Initialize map
        const map = L.map('map').setView([initLat, initLng], 17);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 19,
        }).addTo(map);

        // Marker & Circle
        let marker = L.marker([initLat, initLng], { draggable: true }).addTo(map);
        let circle = L.circle([initLat, initLng], {
            radius: initRadius,
            color: '#6366f1',
            fillColor: '#6366f1',
            fillOpacity: 0.15,
            weight: 2,
        }).addTo(map);

        // Update form & display when marker moves
        function updateLocation(lat, lng) {
            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);
            document.getElementById('displayLat').textContent = lat.toFixed(8);
            document.getElementById('displayLng').textContent = lng.toFixed(8);

            marker.setLatLng([lat, lng]);
            circle.setLatLng([lat, lng]);
            map.panTo([lat, lng]);
        }

        // Drag marker
        marker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            updateLocation(pos.lat, pos.lng);
        });

        // Click map to set location
        map.on('click', function(e) {
            updateLocation(e.latlng.lat, e.latlng.lng);
        });

        // Update radius circle when input changes
        document.getElementById('radius').addEventListener('input', function() {
            const r = parseInt(this.value) || 50;
            circle.setRadius(r);
            document.getElementById('displayRadius').textContent = r + ' meter';
        });

        // Update map when lat/lng inputs change manually
        document.getElementById('latitude').addEventListener('change', function() {
            const lat = parseFloat(this.value);
            const lng = parseFloat(document.getElementById('longitude').value);
            if (!isNaN(lat) && !isNaN(lng)) {
                updateLocation(lat, lng);
            }
        });

        document.getElementById('longitude').addEventListener('change', function() {
            const lat = parseFloat(document.getElementById('latitude').value);
            const lng = parseFloat(this.value);
            if (!isNaN(lat) && !isNaN(lng)) {
                updateLocation(lat, lng);
            }
        });

        // Use current location button
        function useCurrentLocation() {
            if (!navigator.geolocation) {
                alert('GPS tidak tersedia di perangkat ini.');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    updateLocation(pos.coords.latitude, pos.coords.longitude);
                    map.setZoom(18);
                },
                (err) => {
                    alert('Gagal mendapatkan lokasi: ' + err.message);
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }
    </script>
@endpush
