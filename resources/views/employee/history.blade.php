@extends('layouts.app')

@section('title', 'Riwayat Absensi')

@section('header', 'Riwayat Absensi')

@section('content')

    <div class="space-y-4">
        <!-- Filter -->
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Bulan</label>
                    <select name="month"
                        class="bg-slate-800 border border-white/10 rounded-lg px-3 py-2 text-sm text-white">
                        @foreach ($months as $num => $name)
                            <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tahun</label>
                    <select name="year"
                        class="bg-slate-800 border border-white/10 rounded-lg px-3 py-2 text-sm text-white">
                        @for ($y = now()->year; $y >= now()->year - 2; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg transition-colors">Tampilkan</button>
            </form>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-slate-900/60 rounded-xl border border-white/5 p-3 text-center">
                <p class="text-xl font-bold text-white">{{ $attendances->whereNotNull('clock_in')->count() }}</p>
                <p class="text-xs text-gray-500">Hadir</p>
            </div>
            <div class="bg-slate-900/60 rounded-xl border border-white/5 p-3 text-center">
                <p class="text-xl font-bold text-emerald-400">
                    {{ $attendances->where('clock_in_status', 'on_time')->count() }}</p>
                <p class="text-xs text-gray-500">Tepat Waktu</p>
            </div>
            <div class="bg-slate-900/60 rounded-xl border border-white/5 p-3 text-center">
                <p class="text-xl font-bold text-amber-400">
                    {{ $attendances->whereIn('clock_in_status', ['late', 'very_late'])->count() }}</p>
                <p class="text-xs text-gray-500">Terlambat</p>
            </div>
            <div class="bg-slate-900/60 rounded-xl border border-white/5 p-3 text-center">
                <p class="text-xl font-bold text-indigo-400">{{ $user->attendanceRate($year, $month) }}%</p>
                <p class="text-xs text-gray-500">Rate</p>
            </div>
        </div>

        <!-- History List -->
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5">
            <h3 class="text-sm font-semibold text-white mb-4">Detail Absensi</h3>
            <div class="space-y-2">
                @forelse($attendances as $att)
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
                                <p class="text-sm text-white">{{ $att->date->translatedFormat('l, d F Y') }}</p>
                                <p class="text-xs text-gray-500">
                                    Masuk: {{ $att->clock_in?->format('H:i') ?? '-' }} ·
                                    Keluar: {{ $att->clock_out?->format('H:i') ?? '-' }}
                                    @if ($att->working_duration)
                                        · Durasi: {{ $att->working_duration }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
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
                            @if ($att->location_valid)
                                <p class="text-xs text-gray-600 mt-1">📍 Lokasi valid</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-600 py-8 text-sm">Tidak ada data absensi untuk periode ini</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
