@extends('layouts.app')

@section('title', 'Data Absensi - Admin')

@section('header', 'Data Absensi')

@section('content')

    <div class="space-y-4">
        {{-- Filter Bar --}}
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tanggal</label>
                    <input type="date" name="date" value="{{ $date }}"
                        class="bg-slate-800 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Departemen</label>
                    <select name="department"
                        class="bg-slate-800 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:ring-indigo-500">
                        <option value="">Semua</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept }}" {{ $department === $dept ? 'selected' : '' }}>
                                {{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Shift</label>
                    <select name="shift"
                        class="bg-slate-800 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:ring-indigo-500">
                        <option value="">Semua</option>
                        @foreach ($shifts as $s)
                            <option value="{{ $s->id }}" {{ $shift == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg transition-colors">Filter</button>

                {{-- Manual Attendance Button --}}
                <button type="button" onclick="openModal('manualModal')"
                    class="ml-auto px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-sm font-semibold rounded-lg transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v16m8-8H4" />
                    </svg>
                    Absensi Manual
                </button>
            </form>
        </div>

        {{-- Attendance Table --}}
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5">
            <h3 class="text-sm font-semibold text-white mb-3">Karyawan yang Hadir ({{ $attendances->count() }})</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-gray-500 text-xs uppercase tracking-wider border-b border-white/5">
                            <th class="text-left py-3 px-2">#</th>
                            <th class="text-left py-3 px-2">Karyawan</th>
                            <th class="text-left py-3 px-2">Departemen</th>
                            <th class="text-left py-3 px-2">Shift</th>
                            <th class="text-left py-3 px-2">Masuk</th>
                            <th class="text-left py-3 px-2">Keluar</th>
                            <th class="text-left py-3 px-2">Status</th>
                            <th class="text-left py-3 px-2">Lokasi</th>
                            <th class="text-left py-3 px-2">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($attendances as $i => $att)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="py-3 px-2 text-gray-500">{{ $i + 1 }}</td>
                                <td class="py-3 px-2">
                                    <p class="text-white">{{ $att->user->name }}</p>
                                    <p class="text-gray-600 text-xs">{{ $att->user->employee_id }}</p>
                                </td>
                                <td class="py-3 px-2 text-gray-400">{{ $att->user->department }}</td>
                                <td class="py-3 px-2 text-gray-400 text-xs">{{ $att->shift->name }}</td>
                                <td class="py-3 px-2 text-gray-300">{{ $att->clock_in?->format('H:i:s') ?? '-' }}</td>
                                <td class="py-3 px-2 text-gray-300">{{ $att->clock_out?->format('H:i:s') ?? '-' }}</td>
                                <td class="py-3 px-2">
                                    @if ($att->clock_in_status === 'on_time')
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Tepat
                                            Waktu</span>
                                    @elseif($att->clock_in_status === 'late')
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs bg-amber-500/10 text-amber-400 border border-amber-500/20">Terlambat</span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs bg-red-500/10 text-red-400 border border-red-500/20">Sangat
                                            Terlambat</span>
                                    @endif
                                </td>
                                <td class="py-3 px-2">
                                    @if ($att->location_valid)
                                        <span class="text-emerald-400 text-xs">✓ Valid</span>
                                    @else
                                        <span class="text-red-400 text-xs">✗ Invalid</span>
                                    @endif
                                </td>
                                <td class="py-3 px-2">
                                    @if ($att->notes)
                                        <span class="text-xs text-gray-500 italic"
                                            title="{{ $att->notes }}">{{ Str::limit($att->notes, 30) }}</span>
                                    @else
                                        <span class="text-gray-700 text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-gray-600">Tidak ada data absensi</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Absent Employees --}}
        @if ($absentEmployees->count() > 0)
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-red-500/10 p-5">
                <h3 class="text-sm font-semibold text-red-400 mb-3">Belum Absen ({{ $absentEmployees->count() }})</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach ($absentEmployees as $emp)
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-red-500/5 border border-red-500/10">
                            <div
                                class="w-7 h-7 rounded-full bg-red-500/20 flex items-center justify-center text-xs font-bold text-red-400">
                                {{ substr($emp->name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-white">{{ $emp->name }}</p>
                                <p class="text-xs text-gray-500">{{ $emp->employee_id }} · {{ $emp->department }}</p>
                            </div>
                            <button type="button"
                                onclick="prefillManual('{{ $emp->id }}', '{{ addslashes($emp->name) }}')"
                                class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors whitespace-nowrap"
                                title="Tambah absensi manual untuk {{ $emp->name }}">
                                + Manual
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Manual Attendance Modal --}}
    <div id="manualModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="absolute inset-0 bg-black/70" onclick="closeModal('manualModal')"></div>
        <div class="relative bg-slate-900 rounded-2xl border border-white/10 p-6 max-w-md w-full">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-lg font-semibold text-white">Absensi Manual</h3>
                    <p class="text-xs text-gray-500 mt-1">Untuk karyawan yang lupa absen / scan QR</p>
                </div>
                <button onclick="closeModal('manualModal')" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.attendances.manual') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Karyawan <span
                            class="text-red-400">*</span></label>
                    <select name="user_id" id="manualUserId" required
                        class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Pilih karyawan...</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">
                                {{ $emp->employee_id }} — {{ $emp->name }}
                                ({{ $emp->shift?->name ?? 'No Shift' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Tanggal <span
                            class="text-red-400">*</span></label>
                    <input type="date" name="date" id="manualDate" required
                        value="{{ today()->format('Y-m-d') }}"
                        max="{{ today()->format('Y-m-d') }}"
                        class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Jam Masuk <span
                                class="text-red-400">*</span></label>
                        <input type="time" name="clock_in_time" required
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Jam Keluar</label>
                        <input type="time" name="clock_out_time"
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Catatan / Alasan <span
                            class="text-red-400">*</span></label>
                    <textarea name="notes" rows="2" required placeholder="Contoh: Lupa scan QR, HP rusak, dll..."
                        class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500 resize-none"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('manualModal')"
                        class="flex-1 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-gray-300 text-sm rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-colors">
                        Simpan Absensi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function prefillManual(userId, userName) {
            document.getElementById('manualUserId').value = userId;
            document.getElementById('manualDate').value = '{{ $date }}';
            openModal('manualModal');
        }
    </script>
@endsection
