@php
    $isAdmin = auth()->user()->isAdmin();
    $currentRoute = request()->route()->getName();
    $pendingLeaveCount = \App\Models\LeaveRequest::where('status', 'pending')->count();
@endphp

<aside id="sidebar"
    class="fixed left-0 top-0 z-50 h-full w-64 bg-slate-900/95 backdrop-blur-xl border-r border-white/5 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-out flex flex-col">
    <!-- Logo -->
    <div class="p-5 border-b border-white/5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl overflow-hidden shadow-lg">
                <img src="{{ asset('images/logo.png') }}" alt="Toko Takasimura" class="w-full h-full object-cover">
            </div>
            <div>
                <h1 class="text-base font-bold text-white tracking-tight">Toko Takasimura</h1>
                <p class="text-xs text-gray-500">Wangkal</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
        <p class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Menu Utama</p>

        @if ($isAdmin)
            <!-- Admin Menu -->
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ $currentRoute === 'admin.dashboard' ? 'bg-indigo-500/15 text-indigo-400 shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.qr-display') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ $currentRoute === 'admin.qr-display' ? 'bg-indigo-500/15 text-indigo-400 shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>QR Code Display</span>
            </a>

            <a href="{{ route('admin.attendances') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ $currentRoute === 'admin.attendances' ? 'bg-indigo-500/15 text-indigo-400 shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Data Absensi</span>
            </a>

            <a href="{{ route('admin.leaves') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ $currentRoute === 'admin.leaves' ? 'bg-indigo-500/15 text-indigo-400 shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Kelola Izin</span>
                @if ($pendingLeaveCount > 0)
                    <span
                        class="ml-auto px-2 py-0.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30 animate-pulse">
                        {{ $pendingLeaveCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.employees') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ $currentRoute === 'admin.employees' ? 'bg-indigo-500/15 text-indigo-400 shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Kelola Karyawan</span>
            </a>

            <a href="{{ route('admin.announcements') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ $currentRoute === 'admin.announcements' ? 'bg-indigo-500/15 text-indigo-400 shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
                <span>Pengumuman</span>
            </a>

            <a href="{{ route('admin.location') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ $currentRoute === 'admin.location' ? 'bg-indigo-500/15 text-indigo-400 shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Lokasi Toko</span>
            </a>

            <a href="{{ route('admin.reports') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ $currentRoute === 'admin.reports' ? 'bg-indigo-500/15 text-indigo-400 shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Laporan</span>
            </a>
        @else
            <!-- Employee Menu -->
            <a href="{{ route('employee.dashboard') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ $currentRoute === 'employee.dashboard' ? 'bg-indigo-500/15 text-indigo-400 shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('employee.scan') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ $currentRoute === 'employee.scan' ? 'bg-indigo-500/15 text-indigo-400 shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>Scan QR Code</span>
            </a>

            <a href="{{ route('employee.leaves') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ $currentRoute === 'employee.leaves' ? 'bg-indigo-500/15 text-indigo-400 shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Pengajuan Izin</span>
            </a>

            <a href="{{ route('employee.history') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ $currentRoute === 'employee.history' ? 'bg-indigo-500/15 text-indigo-400 shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Riwayat Absensi</span>
            </a>
        @endif
    </nav>

    <!-- Bottom Section -->
    <div class="p-3 border-t border-white/5">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit"
                class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
