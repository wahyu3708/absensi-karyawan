<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <meta name="description" content="Sistem Absensi Karyawan - Kelola kehadiran karyawan dengan QR Code dinamis">

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>@yield('title', 'Absensi Karyawan')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-950 text-gray-100 min-h-screen font-sans antialiased">
    <div class="flex min-h-screen" id="app">
        <!-- Sidebar -->
        @include('components.sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col lg:ml-64">
            <!-- Top Bar -->
            <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-white/5">
                <div class="flex items-center justify-between px-4 py-3 lg:px-6">
                    <!-- Mobile Menu Toggle -->
                    <button onclick="toggleSidebar()"
                        class="lg:hidden p-2 rounded-lg hover:bg-white/5 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="flex-1 lg:flex-none">
                        <h2 class="text-lg font-semibold text-white/90 hidden lg:block">@yield('header', 'Dashboard')
                        </h2>
                    </div>

                    <!-- User Info -->
                    <div class="flex items-center gap-3">
                        <!-- Notification Bell -->
                        <div class="relative" id="notifContainer">
                            <button id="notifBell" onclick="toggleNotifPanel()"
                                class="relative p-2 rounded-lg hover:bg-white/5 transition-colors">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <!-- Badge -->
                                <span id="notifBadge"
                                    class="hidden absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center px-1 animate-pulse">
                                    0
                                </span>
                            </button>

                            <!-- Notification Dropdown Panel -->
                            <div id="notifPanel"
                                class="hidden absolute right-0 top-full mt-2 w-80 sm:w-96 bg-slate-900 border border-white/10 rounded-2xl shadow-2xl shadow-black/50 z-50 overflow-hidden">
                                <!-- Header -->
                                <div class="flex items-center justify-between px-4 py-3 border-b border-white/5">
                                    <h3 class="text-sm font-semibold text-white">Notifikasi</h3>
                                    <button onclick="markAllRead()"
                                        class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">
                                        Tandai semua dibaca
                                    </button>
                                </div>

                                <!-- Notification List -->
                                <div id="notifList" class="max-h-80 overflow-y-auto">
                                    <div class="p-8 text-center">
                                        <div
                                            class="animate-spin rounded-full h-6 w-6 border-2 border-indigo-500 border-t-transparent mx-auto">
                                        </div>
                                        <p class="text-xs text-gray-500 mt-2">Memuat...</p>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="px-4 py-2 border-t border-white/5 text-center">
                                    <p class="text-xs text-gray-600">Notifikasi otomatis diperbarui setiap 60 detik</p>
                                </div>
                            </div>
                        </div>

                        <!-- User Dropdown -->
                        <div class="flex items-center gap-2">
                            <div
                                class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-sm font-bold text-white">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="hidden sm:block">
                                <p class="text-sm font-medium text-white/90">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ auth()->user()->role === 'admin' ? 'Administrator' : auth()->user()->position }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-6">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm"
                        id="flashSuccess">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm"
                        id="flashError">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <script>
        // ── Sidebar Toggle ────────────────────────────────
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Auto-dismiss flash messages
        setTimeout(() => {
            ['flashSuccess', 'flashError'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });
        }, 5000);

        // ── Notification System ───────────────────────────
        // csrfToken is already set as window.csrfToken in app.js
        let notifPanelOpen = false;
        let lastUnreadCount = 0;

        function toggleNotifPanel() {
            const panel = document.getElementById('notifPanel');
            notifPanelOpen = !notifPanelOpen;

            if (notifPanelOpen) {
                panel.classList.remove('hidden');
                loadNotifications();
            } else {
                panel.classList.add('hidden');
            }
        }

        // Close panel when clicking outside
        document.addEventListener('click', (e) => {
            const container = document.getElementById('notifContainer');
            if (container && !container.contains(e.target) && notifPanelOpen) {
                toggleNotifPanel();
            }
        });

        async function loadNotifications() {
            try {
                const res = await fetch('/api/notifications', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                renderNotifications(data.notifications);
                updateBadge(data.unread_count);
            } catch (err) {
                console.error('Failed to load notifications:', err);
            }
        }

        function renderNotifications(notifications) {
            const list = document.getElementById('notifList');
            if (!notifications || notifications.length === 0) {
                list.innerHTML = `
                    <div class="p-8 text-center">
                        <svg class="w-10 h-10 mx-auto text-gray-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <p class="text-gray-600 text-sm">Belum ada notifikasi</p>
                    </div>
                `;
                return;
            }

            list.innerHTML = notifications.map(n => `
                <div class="px-4 py-3 border-b border-white/5 hover:bg-white/[0.02] transition-colors cursor-pointer ${!n.is_read ? 'bg-indigo-500/[0.03]' : ''}"
                     onclick="handleNotifClick(${n.id}, '${n.data?.url || ''}')">
                    <div class="flex items-start gap-3">
                        <span class="text-lg shrink-0 mt-0.5">${n.icon}</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-white truncate">${escapeHtml(n.title)}</p>
                                ${!n.is_read ? '<span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>' : ''}
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5 line-clamp-2">${escapeHtml(n.body)}</p>
                            <p class="text-xs text-gray-600 mt-1">${n.time_ago}</p>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        async function handleNotifClick(id, url) {
            // Mark as read
            try {
                await fetch(`/api/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': window.csrfToken,
                        'Accept': 'application/json',
                    }
                });
            } catch (e) {}

            // Redirect if URL provided
            if (url) {
                window.location.href = url;
            } else {
                loadNotifications();
            }
        }

        async function markAllRead() {
            try {
                await fetch('/api/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': window.csrfToken,
                        'Accept': 'application/json',
                    }
                });
                loadNotifications();
            } catch (e) {}
        }

        function updateBadge(count) {
            const badge = document.getElementById('notifBadge');
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // ── Polling & Browser Notifications ───────────────
        async function checkForNewNotifications() {
            try {
                const res = await fetch('/api/notifications/check-reminders', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                updateBadge(data.unread_count);

                // Show browser notification for new ones
                if (data.has_new && data.notifications.length > 0) {
                    data.notifications.forEach(n => {
                        showBrowserNotification(n.title, n.body);
                    });

                    // Refresh panel if open
                    if (notifPanelOpen) {
                        loadNotifications();
                    }
                }
            } catch (err) {
                console.error('Notification check failed:', err);
            }
        }

        function showBrowserNotification(title, body) {
            if (!('Notification' in window)) return;
            if (Notification.permission !== 'granted') return;

            try {
                new Notification(title, {
                    body: body,
                    icon: '/icons/icon-192x192.png',
                    badge: '/icons/icon-192x192.png',
                    vibrate: [100, 50, 100],
                });
            } catch (e) {
                console.warn('Browser notification failed:', e);
            }
        }

        // Request notification permission
        async function requestNotifPermission() {
            if (!('Notification' in window)) return;
            if (Notification.permission === 'default') {
                await Notification.requestPermission();
            }
        }

        // ── Init ──────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            // Request browser notification permission after 2 seconds
            setTimeout(requestNotifPermission, 2000);

            // Initial badge check
            checkForNewNotifications();

            // Poll every 60 seconds
            setInterval(checkForNewNotifications, 60000);
        });
    </script>

    @stack('scripts')

    {{-- DEBUG: Cari semua deklarasi csrfToken di halaman ini --}}
    <script>
        (function() {
            console.group('🔍 DEBUG: Scanning for csrfToken declarations');

            // Ambil semua <script> tags di halaman
            const scripts = document.querySelectorAll('script');
            scripts.forEach((script, index) => {
                const src = script.src || '(inline)';
                const content = script.textContent || '';

                // Cari deklarasi csrfToken di inline scripts
                if (content.includes('csrfToken')) {
                    const lines = content.split('\n');
                    lines.forEach((line, lineIdx) => {
                        if (line.includes('csrfToken')) {
                            console.warn(
                                `⚠️ Script #${index} [${src}] - Line ${lineIdx + 1}: ${line.trim()}`
                            );
                        }
                    });
                }

                // Cek juga external scripts
                if (script.src && script.src.includes('app')) {
                    console.info(`📦 External script: ${script.src} (type: ${script.type || 'classic'})`);
                }
            });

            // Cek apakah window.csrfToken sudah ada
            console.info(`✅ window.csrfToken = ${window.csrfToken ? 'EXISTS' : 'UNDEFINED'}`);

            // Coba cari di full page source via line numbers
            // Error bilang "scan:527" — ini adalah line di rendered HTML
            console.info('💡 Untuk melihat source rendered HTML:');
            console.info('   Klik kanan halaman → View Page Source');
            console.info('   Lalu cari "csrfToken" (Ctrl+F)');
            console.info('   Atau langsung ke line 527');

            console.groupEnd();
        })();
    </script>
</body>

</html>
