@extends('layouts.app')

@section('title', 'Pengajuan Izin')

@section('header', 'Pengajuan Izin')

@section('content')

    <div class="space-y-4">
        {{-- Header with button --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-white">Pengajuan Izin</h2>
                <p class="text-sm text-gray-500">Ajukan izin sakit, cuti, atau izin lainnya</p>
            </div>
            <button onclick="openModal('createModal')"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-xl shadow-lg shadow-indigo-500/25 transition-all duration-300 transform hover:-translate-y-0.5 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajukan Izin
            </button>
        </div>

        {{-- Filter --}}
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Status</label>
                    <select name="status"
                        class="bg-slate-800 border border-white/10 rounded-lg px-3 py-2 text-sm text-white">
                        <option value="">Semua</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg transition-colors">Filter</button>
            </form>
        </div>

        {{-- Leave Requests List --}}
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5">
            <h3 class="text-sm font-semibold text-white mb-4">Riwayat Pengajuan Izin</h3>
            <div class="space-y-3">
                @forelse($leaves as $leave)
                    <div
                        class="p-4 bg-slate-800/30 rounded-xl border border-white/5 hover:bg-slate-800/50 transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0
                                    {{ $leave->status === 'pending'
                                        ? 'bg-amber-500/10'
                                        : ($leave->status === 'approved'
                                            ? 'bg-emerald-500/10'
                                            : 'bg-red-500/10') }}">
                                    @if ($leave->status === 'pending')
                                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @elseif($leave->status === 'approved')
                                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $leave->type === 'sick'
                                                ? 'bg-red-500/10 text-red-400 border border-red-500/20'
                                                : ($leave->type === 'annual'
                                                    ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20'
                                                    : 'bg-purple-500/10 text-purple-400 border border-purple-500/20') }}">
                                            {{ $leave->type_label }}
                                        </span>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $leave->status === 'pending'
                                                ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                                                : ($leave->status === 'approved'
                                                    ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                                                    : 'bg-red-500/10 text-red-400 border border-red-500/20') }}">
                                            {{ $leave->status_label }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-white mt-1">
                                        {{ $leave->start_date->translatedFormat('d M Y') }}
                                        @if ($leave->start_date->ne($leave->end_date))
                                            — {{ $leave->end_date->translatedFormat('d M Y') }}
                                        @endif
                                        <span class="text-gray-500">({{ $leave->duration_days }}
                                            hari)</span>
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $leave->reason }}</p>
                                    @if ($leave->admin_notes)
                                        <p class="text-xs text-gray-500 mt-1 italic">
                                            <span class="text-gray-600">Catatan admin:</span>
                                            {{ $leave->admin_notes }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-xs text-gray-600">Diajukan</p>
                                <p class="text-xs text-gray-400">{{ $leave->created_at->translatedFormat('d M Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-800 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="text-gray-500 text-sm">Belum ada pengajuan izin</p>
                        <button onclick="openModal('createModal')"
                            class="inline-flex items-center gap-1 text-indigo-400 hover:text-indigo-300 text-sm mt-2 transition-colors">
                            Ajukan izin sekarang →
                        </button>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $leaves->withQueryString()->links() }}
            </div>
        </div>
    </div>

    {{-- Create Leave Modal --}}
    <div id="createModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="absolute inset-0 bg-black/70" onclick="closeModal('createModal')"></div>
        <div class="relative bg-slate-900 rounded-2xl border border-white/10 p-6 max-w-md w-full">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold text-white">Ajukan Izin</h3>
                <button onclick="closeModal('createModal')" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('employee.leaves.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Jenis Izin <span
                            class="text-red-400">*</span></label>
                    <select name="type" required
                        class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Pilih jenis izin...</option>
                        <option value="sick" {{ old('type') === 'sick' ? 'selected' : '' }}>🏥 Sakit</option>
                        <option value="annual" {{ old('type') === 'annual' ? 'selected' : '' }}>🏖️ Cuti Tahunan
                        </option>
                        <option value="permission" {{ old('type') === 'permission' ? 'selected' : '' }}>📋 Izin
                        </option>
                    </select>
                    @error('type')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Tanggal Mulai <span
                                class="text-red-400">*</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" required
                            min="{{ today()->format('Y-m-d') }}"
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                        @error('start_date')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Tanggal Selesai <span
                                class="text-red-400">*</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" required
                            min="{{ today()->format('Y-m-d') }}"
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                        @error('end_date')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Alasan <span class="text-red-400">*</span></label>
                    <textarea name="reason" rows="3" required placeholder="Jelaskan alasan pengajuan izin..."
                        class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500 resize-none">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('createModal')"
                        class="flex-1 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-gray-300 text-sm rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors">
                        Kirim Pengajuan
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

        // Auto-open modal if there are validation errors
        @if ($errors->any())
            openModal('createModal');
        @endif
    </script>
@endsection
