@extends('layouts.app')

@section('title', 'Scan QR Code - Absensi')

@section('header', 'Scan QR Code')

@section('content')

    <div class="max-w-lg mx-auto space-y-4">
        {{-- Status Card --}}
        <div id="statusCard" class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5 text-center">
            @if ($todayAttendance && $todayAttendance->clock_in && $todayAttendance->clock_out)
                <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-emerald-400">Absensi Lengkap!</h3>
                <p class="text-gray-400 text-sm mt-1">Masuk: {{ $todayAttendance->clock_in->format('H:i') }} · Keluar:
                    {{ $todayAttendance->clock_out->format('H:i') }}</p>
            @elseif($todayAttendance && $todayAttendance->clock_in)
                <p class="text-gray-400 text-sm mb-2">Anda sudah absen masuk pada <strong
                        class="text-white">{{ $todayAttendance->clock_in->format('H:i') }}</strong></p>
                <button onclick="handleClockOut()" id="clockOutBtn"
                    class="px-6 py-3 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-500 hover:to-red-500 text-white font-semibold rounded-xl shadow-lg transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                    id="clockOutBtnMain">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Absensi Keluar (Clock Out)
                    </span>
                </button>
            @else
                <p class="text-gray-400 text-sm">Scan QR Code yang ditampilkan di monitor kantor untuk melakukan absensi
                    masuk</p>
            @endif
        </div>

        {{-- Location & Distance Info --}}
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-4">
            <div class="flex items-center gap-3 mb-3">
                <div id="gpsIcon" class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center shrink-0">
                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-indigo-500 border-t-transparent"></div>
                </div>
                <div class="flex-1 min-w-0">
                    <p id="gpsStatus" class="text-sm text-gray-400">Mendapatkan lokasi GPS...</p>
                    <p id="gpsDetail" class="text-xs text-gray-600">Pastikan GPS diaktifkan pada perangkat Anda</p>
                </div>
                <button id="gpsRetryBtn" onclick="retryLocation()"
                    class="hidden shrink-0 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium rounded-lg transition-colors">
                    Coba Lagi
                </button>
            </div>

            {{-- Distance Indicator --}}
            <div id="distanceSection" class="hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500">Jarak dari toko</span>
                    <span id="distanceText" class="text-xs font-semibold text-gray-400">-- meter</span>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-2">
                    <div id="distanceBar" class="h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
                <div class="flex justify-between mt-1">
                    <span class="text-xs text-gray-600">0m</span>
                    <span class="text-xs text-gray-600">Radius: {{ $companyLocation['radius'] }}m</span>
                </div>
            </div>
        </div>

        {{-- QR Scanner --}}
        @if (!$todayAttendance || !$todayAttendance->clock_in)
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5">
                <h3 class="text-sm font-semibold text-white mb-3 text-center">Arahkan Kamera ke QR Code</h3>

                {{-- GPS Warning Banner --}}
                <div id="gpsWarning" class="mb-4 p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-center">
                    <p class="text-sm text-amber-400 font-medium">⚠️ GPS Wajib Aktif</p>
                    <p class="text-xs text-gray-400 mt-1">Anda harus berada dalam radius
                        {{ $companyLocation['radius'] }} meter dari toko untuk bisa absen</p>
                </div>

                {{-- Scanner Container --}}
                <div id="scannerContainer"
                    class="relative rounded-xl overflow-hidden bg-black aspect-square max-w-sm mx-auto">
                    <div id="reader" class="w-full h-full"></div>
                    {{-- Scanning overlay --}}
                    <div class="absolute inset-0 pointer-events-none" id="scanOverlay">
                        <div class="absolute inset-0 border-2 border-indigo-500/30 rounded-xl"></div>
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-48">
                            <div
                                class="absolute top-0 left-0 w-8 h-8 border-t-2 border-l-2 border-indigo-400 rounded-tl-lg">
                            </div>
                            <div
                                class="absolute top-0 right-0 w-8 h-8 border-t-2 border-r-2 border-indigo-400 rounded-tr-lg">
                            </div>
                            <div
                                class="absolute bottom-0 left-0 w-8 h-8 border-b-2 border-l-2 border-indigo-400 rounded-bl-lg">
                            </div>
                            <div
                                class="absolute bottom-0 right-0 w-8 h-8 border-b-2 border-r-2 border-indigo-400 rounded-br-lg">
                            </div>
                            {{-- Scan line animation --}}
                            <div
                                class="absolute left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-indigo-400 to-transparent animate-scan">
                            </div>
                        </div>
                    </div>

                    {{-- Block overlay when GPS not ready or out of range --}}
                    <div id="scanBlockOverlay"
                        class="absolute inset-0 bg-slate-950/80 flex items-center justify-center z-10">
                        <div class="text-center p-4">
                            <div id="blockIconContainer">
                                <svg class="w-12 h-12 mx-auto text-amber-400 mb-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <p id="blockMessage" class="text-amber-400 text-sm font-medium">Menunggu lokasi GPS...</p>
                            <p id="blockDetail" class="text-gray-500 text-xs mt-1">Scanner akan aktif setelah lokasi GPS
                                terdeteksi dan
                                Anda berada dalam radius toko</p>
                            <button id="blockRetryBtn" onclick="retryLocation()"
                                class="hidden mt-3 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium rounded-lg transition-colors">
                                🔄 Coba Lagi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Result Modal --}}
        <div id="resultModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
            <div class="absolute inset-0 bg-black/70" onclick="closeModal()"></div>
            <div class="relative bg-slate-900 rounded-2xl border border-white/10 p-6 max-w-sm w-full text-center">
                <div id="resultIcon" class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"></div>
                <h3 id="resultTitle" class="text-lg font-semibold text-white mb-2"></h3>
                <p id="resultMessage" class="text-sm text-gray-400 mb-4"></p>
                <button onclick="closeModal()"
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm transition-colors">OK</button>
            </div>
        </div>
    </div>

    <style>
        @keyframes scan {

            0%,
            100% {
                top: 0;
            }

            50% {
                top: calc(100% - 2px);
            }
        }

        .animate-scan {
            animation: scan 2s ease-in-out infinite;
        }
    </style>
@endsection

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        let currentLat = null;
        let currentLng = null;
        let scanner = null;
        let isProcessing = false;
        let locationReady = false;
        let isInRange = false;
        let scannerStarted = false;
        let retryCount = 0;
        const MAX_AUTO_RETRIES = 3;

        // Company location from database
        const COMPANY_LAT = {{ $companyLocation['latitude'] }};
        const COMPANY_LNG = {{ $companyLocation['longitude'] }};
        const GEOFENCE_RADIUS = {{ $companyLocation['radius'] }};

        // ── Check if running on secure context ──────────────
        const isSecureContext = window.isSecureContext ||
            window.location.protocol === 'https:' ||
            window.location.hostname === 'localhost' ||
            window.location.hostname === '127.0.0.1';

        // Calculate distance using Haversine formula
        function calculateDistance(lat1, lng1, lat2, lng2) {
            const R = 6371000; // Earth radius in meters
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLng = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        function updateDistanceUI(distance) {
            const distSection = document.getElementById('distanceSection');
            const distText = document.getElementById('distanceText');
            const distBar = document.getElementById('distanceBar');

            if (!distSection) return;

            distSection.classList.remove('hidden');

            const rounded = Math.round(distance);
            distText.textContent = rounded + ' meter';

            // Calculate bar width (cap at 200% of radius for display)
            const maxDisplay = GEOFENCE_RADIUS * 2;
            const percentage = Math.min((distance / maxDisplay) * 100, 100);
            distBar.style.width = percentage + '%';

            if (distance <= GEOFENCE_RADIUS) {
                distBar.className =
                    'h-2 rounded-full transition-all duration-500 bg-gradient-to-r from-emerald-500 to-emerald-400';
                distText.className = 'text-xs font-semibold text-emerald-400';
                isInRange = true;
            } else {
                distBar.className = 'h-2 rounded-full transition-all duration-500 bg-gradient-to-r from-red-500 to-red-400';
                distText.className = 'text-xs font-semibold text-red-400';
                isInRange = false;
            }
        }

        function updateGPSStatus(status, detail, type) {
            const gpsIcon = document.getElementById('gpsIcon');
            const gpsStatus = document.getElementById('gpsStatus');
            const gpsDetail = document.getElementById('gpsDetail');
            const gpsWarning = document.getElementById('gpsWarning');
            const retryBtn = document.getElementById('gpsRetryBtn');

            if (gpsStatus) gpsStatus.textContent = status;
            if (gpsDetail) gpsDetail.textContent = detail;
            if (retryBtn) retryBtn.classList.toggle('hidden', type !== 'error');

            if (type === 'loading') {
                if (gpsIcon) gpsIcon.innerHTML =
                    `<div class="animate-spin rounded-full h-5 w-5 border-2 border-indigo-500 border-t-transparent"></div>`;
                if (gpsIcon) gpsIcon.className =
                    'w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center shrink-0';
            } else if (type === 'success') {
                if (gpsIcon) gpsIcon.innerHTML =
                    `<svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`;
                if (gpsIcon) gpsIcon.className =
                    'w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center shrink-0';
                if (gpsWarning) gpsWarning.classList.add('hidden');
            } else if (type === 'warning') {
                if (gpsIcon) gpsIcon.innerHTML =
                    `<svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>`;
                if (gpsIcon) gpsIcon.className =
                    'w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center shrink-0';
            } else if (type === 'error') {
                if (gpsIcon) gpsIcon.innerHTML =
                    `<svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`;
                if (gpsIcon) gpsIcon.className =
                    'w-10 h-10 rounded-lg bg-red-500/10 flex items-center justify-center shrink-0';
            }
        }

        function updateScanBlock() {
            const overlay = document.getElementById('scanBlockOverlay');
            const blockMsg = document.getElementById('blockMessage');
            const blockDetail = document.getElementById('blockDetail');
            const blockRetry = document.getElementById('blockRetryBtn');
            if (!overlay) return;

            if (!locationReady) {
                overlay.classList.remove('hidden');
                if (blockMsg) blockMsg.textContent = 'Menunggu lokasi GPS...';
                if (blockDetail) blockDetail.textContent = 'Scanner aktif setelah lokasi terdeteksi dan dalam radius toko';
                if (blockRetry) blockRetry.classList.add('hidden');
            } else if (!isInRange) {
                overlay.classList.remove('hidden');
                if (blockMsg) blockMsg.textContent = 'Di luar radius toko';
                if (blockDetail) blockDetail.textContent = 'Mendekat ke toko untuk bisa scan QR Code';
                if (blockRetry) blockRetry.classList.add('hidden');
            } else {
                overlay.classList.add('hidden');
                if (!scannerStarted) {
                    initScanner();
                }
            }
        }

        function showGPSError(msg, detail) {
            const blockMsg = document.getElementById('blockMessage');
            const blockDetail = document.getElementById('blockDetail');
            const blockRetry = document.getElementById('blockRetryBtn');
            if (blockMsg) blockMsg.textContent = msg;
            if (blockDetail) blockDetail.textContent = detail;
            if (blockRetry) blockRetry.classList.remove('hidden');
        }

        // ── Show HTTPS warning if needed ──────────────
        function showInsecureOriginWarning() {
            const origin = window.location.origin;
            updateGPSStatus(
                'GPS diblokir oleh browser',
                'Koneksi tidak aman (HTTP). GPS memerlukan HTTPS.',
                'error'
            );
            showGPSError(
                'GPS Diblokir — Koneksi HTTP',
                'Browser memblokir GPS pada koneksi HTTP. Buka Chrome → ketik chrome://flags/#unsafely-treat-insecure-origin-as-secure → tambahkan ' +
                origin + ' → set Enabled → Relaunch'
            );

            // Also update the block overlay with more helpful info
            const blockDetail = document.getElementById('blockDetail');
            if (blockDetail) {
                blockDetail.innerHTML =
                    `Browser memblokir GPS pada koneksi HTTP.<br>
                    <span class="text-indigo-400">Solusi:</span> Buka Chrome, ketik di address bar:<br>
                    <code class="text-indigo-400 text-xs">chrome://flags/#unsafely-treat-insecure-origin-as-secure</code><br>
                    Tambahkan: <code class="text-indigo-400">${origin}</code><br>
                    Set ke <strong class="text-white">Enabled</strong>, lalu <strong class="text-white">Relaunch</strong>`;
            }
        }

        // ── GPS Request with improved error handling ──────────────
        let watchId = null;
        let gpsTimeout = null;

        function requestLocation() {
            if (!navigator.geolocation) {
                if (!isSecureContext) {
                    showInsecureOriginWarning();
                } else {
                    updateGPSStatus('GPS tidak tersedia', 'Perangkat Anda tidak mendukung GPS', 'error');
                    showGPSError('GPS Tidak Tersedia', 'Perangkat ini tidak mendukung GPS');
                }
                return;
            }

            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
            if (gpsTimeout) {
                clearTimeout(gpsTimeout);
                gpsTimeout = null;
            }

            updateGPSStatus('Mendapatkan lokasi GPS...', `Mencari sinyal... (percobaan ${retryCount + 1})`, 'loading');

            const blockMsg = document.getElementById('blockMessage');
            const blockDetail = document.getElementById('blockDetail');
            if (blockMsg) blockMsg.textContent = 'Mendapatkan lokasi GPS...';
            if (blockDetail) blockDetail.textContent = retryCount > 0 ?
                `Percobaan ke-${retryCount + 1}... Pastikan GPS aktif dan beri izin lokasi` :
                'Scanner akan aktif setelah lokasi GPS terdeteksi dan Anda berada dalam radius toko';

            let resolved = false;

            gpsTimeout = setTimeout(() => {
                if (!resolved) {
                    resolved = true;

                    if (retryCount < MAX_AUTO_RETRIES) {
                        retryCount++;
                        console.log(`GPS timeout, auto-retrying (${retryCount}/${MAX_AUTO_RETRIES})...`);
                        updateGPSStatus(
                            'GPS lambat merespons...',
                            `Mencoba ulang otomatis (${retryCount}/${MAX_AUTO_RETRIES})...`,
                            'loading'
                        );
                        setTimeout(() => requestLocation(), 1000);
                    } else {
                        if (!isSecureContext) {
                            showInsecureOriginWarning();
                        } else {
                            updateGPSStatus(
                                'GPS timeout',
                                'Lokasi tidak bisa didapatkan. Pastikan GPS aktif & izin lokasi diberikan.',
                                'error'
                            );
                            showGPSError(
                                'GPS Timeout',
                                'Pastikan: 1) GPS/Lokasi HP aktif, 2) Izin lokasi diberikan ke Chrome, 3) Coba di area terbuka'
                            );
                        }
                    }
                }
            }, 30000);

            function onSuccess(pos) {
                if (!resolved) {
                    resolved = true;
                    if (gpsTimeout) {
                        clearTimeout(gpsTimeout);
                        gpsTimeout = null;
                    }
                }

                retryCount = 0;

                currentLat = pos.coords.latitude;
                currentLng = pos.coords.longitude;
                locationReady = true;

                const distance = calculateDistance(currentLat, currentLng, COMPANY_LAT, COMPANY_LNG);
                const accuracy = Math.round(pos.coords.accuracy);
                updateDistanceUI(distance);

                console.log(
                    `GPS OK: lat=${currentLat}, lng=${currentLng}, accuracy=±${accuracy}m, distance=${Math.round(distance)}m, radius=${GEOFENCE_RADIUS}m`
                    );

                if (distance <= GEOFENCE_RADIUS) {
                    updateGPSStatus(
                        '✓ Dalam area toko',
                        `Jarak: ${Math.round(distance)}m (maks ${GEOFENCE_RADIUS}m) · Akurasi: ±${accuracy}m`,
                        'success'
                    );
                } else {
                    updateGPSStatus(
                        '⚠️ Di luar area toko',
                        `Jarak: ${Math.round(distance)}m — harus ≤ ${GEOFENCE_RADIUS}m · Akurasi: ±${accuracy}m`,
                        'warning'
                    );
                }

                updateScanBlock();
            }

            function onError(err) {
                console.error('Geolocation error:', err.code, err.message);
                if (resolved) return;
                resolved = true;
                if (gpsTimeout) {
                    clearTimeout(gpsTimeout);
                    gpsTimeout = null;
                }

                if (err.code === 1 && !isSecureContext) {
                    showInsecureOriginWarning();
                    return;
                }

                const errorMessages = {
                    1: [
                        'Akses lokasi ditolak',
                        'Buka Pengaturan HP → Izin Aplikasi → Chrome → Lokasi → Izinkan. Lalu refresh halaman ini.'
                    ],
                    2: [
                        'Lokasi tidak tersedia',
                        'Pastikan GPS/Lokasi sudah diaktifkan di Pengaturan HP → Lokasi → ON'
                    ],
                    3: [
                        'Waktu pencarian habis',
                        'GPS lambat merespons. Coba di area terbuka atau restart GPS HP.'
                    ],
                };

                const [status, detail] = errorMessages[err.code] || ['Gagal mendapatkan lokasi',
                    'Coba lagi atau restart browser'
                ];

                if ((err.code === 2 || err.code === 3) && retryCount < MAX_AUTO_RETRIES) {
                    retryCount++;
                    console.log(`GPS error code ${err.code}, auto-retrying (${retryCount}/${MAX_AUTO_RETRIES})...`);
                    updateGPSStatus(
                        status,
                        `Mencoba ulang otomatis (${retryCount}/${MAX_AUTO_RETRIES})...`,
                        'loading'
                    );
                    setTimeout(() => requestLocation(), 2000);
                    return;
                }

                updateGPSStatus(status, detail, 'error');
                showGPSError(status, detail);
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    onSuccess(pos);
                    watchId = navigator.geolocation.watchPosition(onSuccess, (err) => {
                        console.warn('Watch position error (non-critical):', err.message);
                    }, {
                        enableHighAccuracy: true,
                        maximumAge: 10000,
                        timeout: 20000,
                    });
                },
                (err) => {
                    console.warn('Phase 1 (network) failed:', err.message);
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            onSuccess(pos);
                            watchId = navigator.geolocation.watchPosition(onSuccess, (err2) => {
                                console.warn('Watch position error (non-critical):', err2.message);
                            }, {
                                enableHighAccuracy: true,
                                maximumAge: 10000,
                                timeout: 20000,
                            });
                        },
                        onError, {
                            enableHighAccuracy: true,
                            timeout: 20000,
                            maximumAge: 0
                        }
                    );
                }, {
                    enableHighAccuracy: false,
                    timeout: 10000,
                    maximumAge: 60000
                }
            );
        }

        function retryLocation() {
            locationReady = false;
            isInRange = false;
            retryCount = 0;
            const retryBtn = document.getElementById('gpsRetryBtn');
            if (retryBtn) retryBtn.classList.add('hidden');
            updateScanBlock();
            requestLocation();
        }

        // Start GPS request
        requestLocation();

        function initScanner() {
            const readerEl = document.getElementById('reader');
            if (!readerEl || scannerStarted) return;

            scannerStarted = true;
            scanner = new Html5Qrcode('reader');
            scanner.start({
                    facingMode: 'environment'
                }, {
                    fps: 10,
                    qrbox: {
                        width: 200,
                        height: 200
                    }
                },
                onScanSuccess,
                () => {} // ignore scan failures
            ).catch(err => {
                console.error('Camera error:', err);
                const origin = window.location.origin;
                document.getElementById('reader').innerHTML = `
                <div class="flex items-center justify-center h-full text-center p-4">
                    <div>
                        <svg class="w-12 h-12 mx-auto text-amber-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        <p class="text-amber-400 text-sm font-medium">Tidak dapat mengakses kamera</p>
                        <p class="text-gray-500 text-xs mt-1">Buka di Chrome, ketik di address bar:</p>
                        <p class="text-indigo-400 text-xs mt-1 font-mono break-all">chrome://flags/#unsafely-treat-insecure-origin-as-secure</p>
                        <p class="text-gray-500 text-xs mt-2">Tambahkan: <code class="text-indigo-400">${origin}</code></p>
                        <p class="text-gray-500 text-xs">Set ke <strong class="text-white">Enabled</strong>, lalu <strong class="text-white">Relaunch</strong></p>
                    </div>
                </div>
            `;
            });
        }

        async function onScanSuccess(decodedText) {
            if (isProcessing || !locationReady || !isInRange) return;
            isProcessing = true;

            if (navigator.vibrate) navigator.vibrate(200);

            try {
                if (scanner) {
                    try {
                        await scanner.pause(true);
                    } catch (e) {}
                }

                const response = await fetch('/api/attendance/clock-in', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        qr_data: decodedText,
                        latitude: currentLat,
                        longitude: currentLng,
                    }),
                });

                const data = await response.json();
                showResult(data.success, data.success ? 'Berhasil!' : 'Gagal', data.message);

                if (data.success) {
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    if (scanner) {
                        try {
                            scanner.resume();
                        } catch (e) {}
                    }
                    isProcessing = false;
                }
            } catch (error) {
                console.error('Clock-in error:', error);
                showResult(false, 'Error', 'Terjadi kesalahan: ' + error.message);
                if (scanner) {
                    try {
                        scanner.resume();
                    } catch (e) {}
                }
                isProcessing = false;
            }
        }

        async function handleClockOut() {
            if (!currentLat || !currentLng) {
                showResult(false, 'Lokasi Diperlukan', 'Harap tunggu hingga lokasi GPS terdeteksi.');
                return;
            }

            try {
                const response = await fetch('/api/attendance/clock-out', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        latitude: currentLat,
                        longitude: currentLng,
                    }),
                });

                const data = await response.json();
                showResult(data.success, data.success ? 'Berhasil!' : 'Gagal', data.message);
                if (data.success) setTimeout(() => window.location.reload(), 2000);
            } catch (error) {
                showResult(false, 'Error', 'Terjadi kesalahan.');
            }
        }

        function showResult(success, title, message) {
            const modal = document.getElementById('resultModal');
            const icon = document.getElementById('resultIcon');
            document.getElementById('resultTitle').textContent = title;
            document.getElementById('resultMessage').textContent = message;

            if (success) {
                icon.className = 'w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center bg-emerald-500/10';
                icon.innerHTML =
                    '<svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
            } else {
                icon.className = 'w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center bg-red-500/10';
                icon.innerHTML =
                    '<svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
            }

            modal.classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('resultModal').classList.add('hidden');
        }
    </script>
@endpush
