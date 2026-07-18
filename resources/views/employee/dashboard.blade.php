@extends('layouts.app')

@section('title', 'Dashboard - Absensi Karyawan')

@section('header', 'Dashboard Karyawan')

@section('content')

    <div class="space-y-6">
        <!-- Welcome & Shift Info -->
        <div
            class="bg-gradient-to-r from-indigo-600/20 to-purple-600/20 backdrop-blur-sm rounded-2xl border border-indigo-500/20 p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-white">Halo, {{ $user->name }}! 👋</h2>
                    <p class="text-gray-400 text-sm mt-1">{{ $user->employee_id }} · {{ $user->department }} ·
                        {{ $user->position }}</p>
                    <div class="mt-2 flex items-center gap-2">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-500/15 text-indigo-400 border border-indigo-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 mr-1.5 animate-pulse"></span>
                            {{ $shift->name }}
                            ({{ \Carbon\Carbon::parse($shift->getRawOriginal('start_time'))->format('H:i') }} -
                            {{ \Carbon\Carbon::parse($shift->getRawOriginal('end_time'))->format('H:i') }})
                        </span>
                    </div>
                </div>
                <a href="{{ route('employee.scan') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-xl shadow-lg shadow-indigo-500/25 transition-all duration-300 transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    Scan QR Code
                </a>
            </div>
        </div>

        <!-- Today's Status -->
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5">
            <h3 class="text-sm font-semibold text-white mb-4">Status Absensi Hari Ini</h3>
            @if ($todayAttendance)
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-slate-800/50 rounded-xl p-4 text-center">
                        <p class="text-xs text-gray-500 mb-1">Masuk</p>
                        <p class="text-2xl font-bold text-emerald-400">
                            {{ $todayAttendance->clock_in?->format('H:i') ?? '-' }}</p>
                        @if ($todayAttendance->clock_in_status === 'on_time')
                            <span class="text-xs text-emerald-500">Tepat Waktu ✓</span>
                        @elseif($todayAttendance->clock_in_status === 'late')
                            <span class="text-xs text-amber-400">Terlambat</span>
                        @elseif($todayAttendance->clock_in_status === 'very_late')
                            <span class="text-xs text-red-400">Sangat Terlambat</span>
                        @endif
                    </div>
                    <div class="bg-slate-800/50 rounded-xl p-4 text-center">
                        <p class="text-xs text-gray-500 mb-1">Keluar</p>
                        <p
                            class="text-2xl font-bold {{ $todayAttendance->clock_out ? 'text-indigo-400' : 'text-gray-600' }}">
                            {{ $todayAttendance->clock_out?->format('H:i') ?? '--:--' }}
                        </p>
                        @if ($todayAttendance->clock_out)
                            <span
                                class="text-xs text-indigo-400">{{ $todayAttendance->clock_out_status === 'on_time' ? 'Selesai ✓' : 'Pulang Awal' }}</span>
                        @else
                            <span class="text-xs text-gray-500">Belum keluar</span>
                        @endif
                    </div>
                    <div class="bg-slate-800/50 rounded-xl p-4 text-center">
                        <p class="text-xs text-gray-500 mb-1">Durasi</p>
                        <p class="text-2xl font-bold text-purple-400">{{ $todayAttendance->working_duration ?? '--' }}</p>
                        <span class="text-xs text-gray-500">Jam Kerja</span>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-amber-500/10 flex items-center justify-center">
                        <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-gray-400 text-sm">Anda belum melakukan absensi hari ini</p>
                    <a href="{{ route('employee.scan') }}"
                        class="inline-flex items-center gap-1 text-indigo-400 hover:text-indigo-300 text-sm mt-2 transition-colors">
                        Scan QR Code sekarang →
                    </a>
                </div>
            @endif
        </div>

        <!-- Monthly Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/5 p-4 text-center">
                <p class="text-2xl font-bold text-white">{{ $monthStats['total_present'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Hari Hadir</p>
            </div>
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/5 p-4 text-center">
                <p class="text-2xl font-bold text-emerald-400">{{ $monthStats['total_on_time'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Tepat Waktu</p>
            </div>
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/5 p-4 text-center">
                <p class="text-2xl font-bold text-amber-400">{{ $monthStats['total_late'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Terlambat</p>
            </div>
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/5 p-4 text-center">
                <p class="text-2xl font-bold text-indigo-400">{{ $monthStats['attendance_rate'] }}%</p>
                <p class="text-xs text-gray-500 mt-1">Rate Bulan Ini</p>
            </div>
        </div>

        <!-- Recent Attendance -->
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-white">Riwayat 7 Hari Terakhir</h3>
                <a href="{{ route('employee.history') }}"
                    class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">Lihat Semua →</a>
            </div>
            <div class="space-y-2">
                @forelse($recentAttendances as $att)
                    <div
                        class="flex items-center justify-between p-3 bg-slate-800/30 rounded-xl hover:bg-slate-800/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg {{ $att->clock_in_status === 'on_time' ? 'bg-emerald-500/10' : ($att->clock_in_status === 'late' ? 'bg-amber-500/10' : 'bg-red-500/10') }} flex items-center justify-center">
                                <span
                                    class="text-xs font-bold {{ $att->clock_in_status === 'on_time' ? 'text-emerald-400' : ($att->clock_in_status === 'late' ? 'text-amber-400' : 'text-red-400') }}">
                                    {{ $att->date->format('d') }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-white">{{ $att->date->translatedFormat('l, d M Y') }}</p>
                                <p class="text-xs text-gray-500">{{ $att->clock_in?->format('H:i') ?? '-' }} -
                                    {{ $att->clock_out?->format('H:i') ?? '-' }}</p>
                            </div>
                        </div>
                        <div>
                            @if ($att->clock_in_status === 'on_time')
                                <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-500/10 text-emerald-400">Tepat
                                    Waktu</span>
                            @elseif($att->clock_in_status === 'late')
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-amber-500/10 text-amber-400">Terlambat</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs bg-red-500/10 text-red-400">Sangat
                                    Terlambat</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-600 py-4 text-sm">Belum ada riwayat absensi</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
