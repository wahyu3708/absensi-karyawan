@extends('layouts.app')

@section('title', 'Laporan - Admin')

@section('header', 'Laporan Absensi')

@section('content')

    <div class="space-y-4">
        <!-- Filter -->
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end" id="reportFilterForm">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Bulan</label>
                    <select name="month" id="filterMonth"
                        class="bg-slate-800 border border-white/10 rounded-lg px-3 py-2 text-sm text-white">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tahun</label>
                    <select name="year" id="filterYear"
                        class="bg-slate-800 border border-white/10 rounded-lg px-3 py-2 text-sm text-white">
                        @for ($y = now()->year; $y >= now()->year - 2; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg transition-colors">Tampilkan</button>

                <!-- Separator -->
                <div class="hidden sm:block w-px h-8 bg-white/10 mx-1"></div>

                <!-- Download PDF -->
                <a href="#" id="btnExportPdf"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-red-600 to-rose-500 hover:from-red-500 hover:to-rose-400 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-lg shadow-red-500/20 hover:shadow-red-500/30 hover:scale-[1.02]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    PDF
                </a>

                <!-- Download Excel -->
                <a href="#" id="btnExportExcel"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-600 to-green-500 hover:from-emerald-500 hover:to-green-400 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 hover:scale-[1.02]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Excel
                </a>
            </form>
        </div>

        <!-- Report Table -->
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5">
            <h3 class="text-sm font-semibold text-white mb-4">Rekap Absensi -
                {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-gray-500 text-xs uppercase tracking-wider border-b border-white/5">
                            <th class="text-left py-3 px-2">#</th>
                            <th class="text-left py-3 px-2">NIP</th>
                            <th class="text-left py-3 px-2">Nama</th>
                            <th class="text-left py-3 px-2">Departemen</th>
                            <th class="text-left py-3 px-2">Shift</th>
                            <th class="text-center py-3 px-2">Hadir</th>
                            <th class="text-center py-3 px-2">Tepat Waktu</th>
                            <th class="text-center py-3 px-2">Terlambat</th>
                            <th class="text-center py-3 px-2">Pulang Awal</th>
                            <th class="text-center py-3 px-2">Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($reportData as $i => $row)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="py-3 px-2 text-gray-500">{{ $i + 1 }}</td>
                                <td class="py-3 px-2 text-indigo-400 font-mono text-xs">{{ $row['employee']->employee_id }}
                                </td>
                                <td class="py-3 px-2 text-white">{{ $row['employee']->name }}</td>
                                <td class="py-3 px-2 text-gray-400 text-xs">{{ $row['employee']->department }}</td>
                                <td class="py-3 px-2 text-gray-400 text-xs">{{ $row['employee']->shift?->name ?? '-' }}
                                </td>
                                <td class="py-3 px-2 text-center text-white">{{ $row['total_present'] }}</td>
                                <td class="py-3 px-2 text-center text-emerald-400">{{ $row['total_on_time'] }}</td>
                                <td class="py-3 px-2 text-center text-amber-400">{{ $row['total_late'] }}</td>
                                <td class="py-3 px-2 text-center text-orange-400">{{ $row['total_early_leave'] }}</td>
                                <td class="py-3 px-2 text-center">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs font-medium {{ $row['attendance_rate'] >= 90 ? 'bg-emerald-500/10 text-emerald-400' : ($row['attendance_rate'] >= 75 ? 'bg-amber-500/10 text-amber-400' : 'bg-red-500/10 text-red-400') }}">
                                        {{ $row['attendance_rate'] }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function updateExportUrls() {
            const month = document.getElementById('filterMonth').value;
            const year = document.getElementById('filterYear').value;

            document.getElementById('btnExportPdf').href =
                `{{ route('admin.reports.export-pdf') }}?month=${month}&year=${year}`;
            document.getElementById('btnExportExcel').href =
                `{{ route('admin.reports.export-excel') }}?month=${month}&year=${year}`;
        }

        // Update URLs on page load and when filters change
        document.addEventListener('DOMContentLoaded', updateExportUrls);
        document.getElementById('filterMonth').addEventListener('change', updateExportUrls);
        document.getElementById('filterYear').addEventListener('change', updateExportUrls);
    </script>
@endpush

