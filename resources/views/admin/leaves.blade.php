@extends('layouts.app')

@section('title', 'Kelola Izin - Admin')

@section('header', 'Kelola Izin')

@section('content')

    <div class="space-y-4">
        {{-- Filter Bar --}}
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs text-gray-500 mb-1">Cari Karyawan</label>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Nama atau NIP..."
                        class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                </div>
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

        {{-- Pending Count Banner --}}
        @if ($pendingCount > 0)
            <div
                class="bg-gradient-to-r from-amber-600/10 to-orange-600/10 backdrop-blur-sm rounded-2xl border border-amber-500/20 p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-amber-400">{{ $pendingCount }} pengajuan menunggu persetujuan
                    </p>
                    <p class="text-xs text-gray-400">Silakan review dan proses pengajuan izin karyawan</p>
                </div>
            </div>
        @endif

        {{-- Leave Requests --}}
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5">
            <h3 class="text-sm font-semibold text-white mb-4">Daftar Pengajuan Izin ({{ $leaves->total() }})</h3>
            <div class="space-y-3">
                @forelse($leaves as $leave)
                    <div
                        class="p-4 rounded-xl border transition-colors
                        {{ $leave->status === 'pending'
                            ? 'bg-amber-500/[0.03] border-amber-500/10 hover:bg-amber-500/[0.06]'
                            : ($leave->status === 'approved'
                                ? 'bg-slate-800/30 border-white/5 hover:bg-slate-800/50'
                                : 'bg-slate-800/30 border-white/5 hover:bg-slate-800/50') }}">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            {{-- Employee Info --}}
                            <div class="flex items-start gap-3 flex-1">
                                <div
                                    class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-sm font-bold text-white shrink-0">
                                    {{ substr($leave->user->name, 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-medium text-white">{{ $leave->user->name }}</p>
                                        <span class="text-xs text-gray-600">{{ $leave->user->employee_id }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $leave->type === 'sick'
                                                ? 'bg-red-500/10 text-red-400'
                                                : ($leave->type === 'annual'
                                                    ? 'bg-indigo-500/10 text-indigo-400'
                                                    : 'bg-purple-500/10 text-purple-400') }}">
                                            {{ $leave->type_label }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ $leave->start_date->translatedFormat('d M Y') }}
                                            @if ($leave->start_date->ne($leave->end_date))
                                                — {{ $leave->end_date->translatedFormat('d M Y') }}
                                            @endif
                                            ({{ $leave->duration_days }} hari)
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">{{ $leave->reason }}</p>
                                    @if ($leave->admin_notes)
                                        <p class="text-xs text-gray-500 mt-1 italic">
                                            <span class="text-gray-600">Catatan:</span> {{ $leave->admin_notes }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Status & Actions --}}
                            <div class="flex items-center gap-3 shrink-0">
                                @if ($leave->status === 'pending')
                                    {{-- Approve Button --}}
                                    <form method="POST"
                                        action="{{ route('admin.leaves.approve', $leave->id) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg transition-colors"
                                            onclick="return confirm('Setujui izin {{ $leave->user->name }}?')">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            Setujui
                                        </button>
                                    </form>

                                    {{-- Reject Button --}}
                                    <button
                                        onclick="openRejectModal({{ $leave->id }}, '{{ addslashes($leave->user->name) }}')"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-xs font-semibold rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Tolak
                                    </button>
                                @else
                                    <span
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium
                                        {{ $leave->status === 'approved'
                                            ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                                            : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
                                        {{ $leave->status_label }}
                                    </span>
                                    @if ($leave->approver)
                                        <span class="text-xs text-gray-600">oleh {{ $leave->approver->name }}</span>
                                    @endif
                                @endif
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
                        <p class="text-gray-500 text-sm">Tidak ada pengajuan izin ditemukan</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $leaves->withQueryString()->links() }}
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div id="rejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="absolute inset-0 bg-black/70" onclick="closeRejectModal()"></div>
        <div class="relative bg-slate-900 rounded-2xl border border-white/10 p-6 max-w-md w-full">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold text-white">Tolak Pengajuan Izin</h3>
                <button onclick="closeRejectModal()" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p class="text-sm text-gray-400 mb-4">
                Tolak izin dari <strong id="rejectName" class="text-white"></strong>?
            </p>

            <form id="rejectForm" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Alasan Penolakan <span
                            class="text-red-400">*</span></label>
                    <textarea name="admin_notes" rows="3" required placeholder="Jelaskan alasan penolakan..."
                        class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500 resize-none"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeRejectModal()"
                        class="flex-1 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-gray-300 text-sm rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold rounded-xl transition-colors">
                        Tolak Izin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRejectModal(id, name) {
            document.getElementById('rejectName').textContent = name;
            document.getElementById('rejectForm').action = `/admin/leaves/${id}/reject`;
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
    </script>
@endsection
