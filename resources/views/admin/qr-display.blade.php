<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QR Code Absensi - Display</title>
    @vite(['resources/css/app.css'])
</head>

<body class="bg-slate-950 text-white min-h-screen flex flex-col items-center justify-center overflow-hidden">
    <!-- Animated Background -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-500/10 rounded-full blur-3xl animate-pulse"
            style="animation-delay: 1s"></div>
    </div>

    <div class="relative z-10 text-center max-w-lg mx-auto px-4">
        <!-- Header -->
        <div class="mb-6">
            <h1
                class="text-2xl lg:text-3xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
                Absensi Karyawan</h1>
            <p class="text-gray-400 mt-1 text-sm">Scan QR Code untuk melakukan absensi</p>
        </div>

        <!-- Current Time -->
        <div class="mb-6">
            <p class="text-5xl lg:text-6xl font-bold text-white tabular-nums" id="currentTime">--:--:--</p>
            <p class="text-gray-400 mt-1" id="currentDate">Loading...</p>
        </div>

        <!-- Active Shift Badge -->
        <div class="mb-6">
            <span
                class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-medium bg-indigo-500/15 text-indigo-400 border border-indigo-500/20"
                id="activeShift">
                <span class="w-2 h-2 rounded-full bg-indigo-400 mr-2 animate-pulse"></span>
                Mendeteksi shift...
            </span>
        </div>

        <!-- QR Code Container -->
        <div class="relative">
            <div class="bg-white rounded-3xl p-6 shadow-2xl shadow-indigo-500/10 inline-block mx-auto" id="qrContainer">
                <div id="qrCode"
                    class="w-64 h-64 lg:w-72 lg:h-72 flex items-center justify-center transition-opacity duration-300">
                    <div class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-500 border-t-transparent">
                    </div>
                </div>
            </div>

            <!-- Countdown Ring -->
            <div class="mt-4 flex items-center justify-center gap-2">
                <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-slate-900/80 border border-white/10">
                    <svg class="w-4 h-4 text-indigo-400 animate-spin" style="animation-duration: 3s" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="text-sm text-gray-300">Refresh dalam <strong class="text-indigo-400"
                            id="countdown">-</strong> detik</span>
                </div>
            </div>
        </div>

        <!-- Status Info -->
        <div class="mt-8 grid grid-cols-2 gap-3 max-w-xs mx-auto">
            <div class="bg-slate-900/60 rounded-xl border border-white/5 p-3 text-center">
                <p class="text-lg font-bold text-emerald-400" id="todayCount">-</p>
                <p class="text-xs text-gray-500">Sudah Absen</p>
            </div>
            <div class="bg-slate-900/60 rounded-xl border border-white/5 p-3 text-center">
                <p class="text-lg font-bold text-amber-400" id="remainingCount">-</p>
                <p class="text-xs text-gray-500">Belum Absen</p>
            </div>
        </div>
    </div>

    <script>
        let refreshInterval;
        let countdownValue = 0;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Update clock every second
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
            const dateStr = now.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            document.getElementById('currentTime').textContent = timeStr;
            document.getElementById('currentDate').textContent = dateStr;

            // Determine active shift
            const hours = now.getHours();
            const shiftEl = document.getElementById('activeShift');
            if (hours >= 7 && hours < 14) {
                shiftEl.innerHTML =
                    '<span class="w-2 h-2 rounded-full bg-emerald-400 mr-2 animate-pulse"></span>Shift 1 (07:00 - 14:00)';
            } else if (hours >= 14 && hours < 21) {
                shiftEl.innerHTML =
                    '<span class="w-2 h-2 rounded-full bg-indigo-400 mr-2 animate-pulse"></span>Shift 2 (14:00 - 21:00)';
            } else {
                shiftEl.innerHTML = '<span class="w-2 h-2 rounded-full bg-gray-400 mr-2"></span>Di luar jam kerja';
            }
        }

        // Generate new QR Code
        async function generateQR() {
            try {
                const response = await fetch('/api/qr/generate', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await response.json();

                if (data.success) {
                    const qrEl = document.getElementById('qrCode');
                    qrEl.style.opacity = '0';
                    setTimeout(() => {
                        qrEl.innerHTML = data.qr_svg;
                        // Style SVG
                        const svg = qrEl.querySelector('svg');
                        if (svg) {
                            svg.style.width = '100%';
                            svg.style.height = '100%';
                        }
                        qrEl.style.opacity = '1';
                    }, 200);

                    // Setup next refresh
                    countdownValue = data.next_refresh;
                    startCountdown(data.next_refresh);
                }
            } catch (error) {
                console.error('QR generation failed:', error);
                setTimeout(generateQR, 3000);
            }
        }

        // Countdown timer
        function startCountdown(seconds) {
            clearInterval(refreshInterval);
            countdownValue = seconds;
            document.getElementById('countdown').textContent = countdownValue;

            refreshInterval = setInterval(() => {
                countdownValue--;
                document.getElementById('countdown').textContent = Math.max(0, countdownValue);
                if (countdownValue <= 0) {
                    clearInterval(refreshInterval);
                    generateQR();
                }
            }, 1000);
        }

        // Fetch today's stats
        async function fetchStats() {
            try {
                const response = await fetch('/api/dashboard/stats', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await response.json();
                document.getElementById('todayCount').textContent = data.present_today;
                document.getElementById('remainingCount').textContent = data.absent_today;
            } catch (e) {}
        }

        // Initialize
        updateClock();
        setInterval(updateClock, 1000);
        generateQR();
        fetchStats();
        setInterval(fetchStats, 30000);
    </script>
</body>

</html>
