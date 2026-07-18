@extends('layouts.app')

@section('title', 'Kelola Karyawan - Admin')

@section('header', 'Kelola Karyawan')

@section('content')

    <div class="space-y-4">
        {{-- Search & Filter --}}
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs text-gray-500 mb-1">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Nama, NIP, atau departemen..."
                        class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Departemen</label>
                    <select name="department"
                        class="bg-slate-800 border border-white/10 rounded-lg px-3 py-2 text-sm text-white">
                        <option value="">Semua</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>
                                {{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Shift</label>
                    <select name="shift"
                        class="bg-slate-800 border border-white/10 rounded-lg px-3 py-2 text-sm text-white">
                        <option value="">Semua</option>
                        @foreach ($shifts as $s)
                            <option value="{{ $s->id }}" {{ request('shift') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="show_inactive" value="1"
                            {{ $showInactive ? 'checked' : '' }}
                            class="rounded bg-slate-800 border-white/10 text-indigo-500 focus:ring-indigo-500"
                            onchange="this.form.submit()">
                        <span class="text-xs text-gray-400">Tampilkan nonaktif</span>
                    </label>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg transition-colors">Cari</button>

                {{-- Add Employee Button --}}
                <button type="button" onclick="openModal('createModal')"
                    class="ml-auto px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-sm font-semibold rounded-lg transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Tambah Karyawan
                </button>
            </form>
        </div>

        {{-- Employees Table --}}
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-white">Daftar Karyawan ({{ $employees->total() }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-gray-500 text-xs uppercase tracking-wider border-b border-white/5">
                            <th class="text-left py-3 px-2">NIP</th>
                            <th class="text-left py-3 px-2">Nama</th>
                            <th class="text-left py-3 px-2">Departemen</th>
                            <th class="text-left py-3 px-2">Jabatan</th>
                            <th class="text-left py-3 px-2">Shift</th>
                            <th class="text-left py-3 px-2">Telepon</th>
                            <th class="text-left py-3 px-2">Status</th>
                            <th class="text-left py-3 px-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($employees as $emp)
                            <tr class="hover:bg-white/[0.02] transition-colors {{ !$emp->is_active ? 'opacity-50' : '' }}">
                                <td class="py-3 px-2 text-indigo-400 font-mono text-xs">{{ $emp->employee_id }}</td>
                                <td class="py-3 px-2">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-xs font-bold text-white">
                                            {{ substr($emp->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-white">{{ $emp->name }}</p>
                                            <p class="text-gray-600 text-xs">{{ $emp->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-2 text-gray-400">{{ $emp->department }}</td>
                                <td class="py-3 px-2 text-gray-400">{{ $emp->position }}</td>
                                <td class="py-3 px-2 text-gray-400 text-xs">{{ $emp->shift?->name ?? '-' }}</td>
                                <td class="py-3 px-2 text-gray-400 text-xs">{{ $emp->phone ?? '-' }}</td>
                                <td class="py-3 px-2">
                                    @if ($emp->is_active)
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Aktif</span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs bg-red-500/10 text-red-400 border border-red-500/20">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="py-3 px-2">
                                    <div class="flex items-center gap-1">
                                        {{-- Edit --}}
                                        <button
                                            onclick="openEditModal({{ json_encode([
                                                'id' => $emp->id,
                                                'name' => $emp->name,
                                                'email' => $emp->email,
                                                'employee_id' => $emp->employee_id,
                                                'department' => $emp->department,
                                                'position' => $emp->position,
                                                'shift_id' => $emp->shift_id,
                                                'phone' => $emp->phone,
                                            ]) }})"
                                            class="p-1.5 rounded-lg hover:bg-indigo-500/10 text-gray-500 hover:text-indigo-400 transition-colors"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        {{-- Toggle Active --}}
                                        <form method="POST"
                                            action="{{ route('admin.employees.toggle', $emp->id) }}"
                                            class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="p-1.5 rounded-lg transition-colors
                                                {{ $emp->is_active
                                                    ? 'hover:bg-amber-500/10 text-gray-500 hover:text-amber-400'
                                                    : 'hover:bg-emerald-500/10 text-gray-500 hover:text-emerald-400' }}"
                                                title="{{ $emp->is_active ? 'Nonaktifkan (Resign)' : 'Aktifkan Kembali' }}"
                                                onclick="return confirm('{{ $emp->is_active ? 'Nonaktifkan karyawan ' . addslashes($emp->name) . '? (untuk karyawan resign)' : 'Aktifkan kembali karyawan ' . addslashes($emp->name) . '?' }}')">
                                                @if ($emp->is_active)
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                @endif
                                            </button>
                                        </form>

                                        {{-- Delete --}}
                                        <form method="POST"
                                            action="{{ route('admin.employees.destroy', $emp->id) }}"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-1.5 rounded-lg hover:bg-red-500/10 text-gray-500 hover:text-red-400 transition-colors"
                                                title="Hapus Permanen"
                                                onclick="return confirm('Hapus karyawan {{ addslashes($emp->name) }}? Tindakan ini tidak dapat dibatalkan!')">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-gray-600">Tidak ada karyawan ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $employees->withQueryString()->links() }}
            </div>
        </div>
    </div>

    {{-- Create Employee Modal --}}
    <div id="createModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="absolute inset-0 bg-black/70" onclick="closeModal('createModal')"></div>
        <div class="relative bg-slate-900 rounded-2xl border border-white/10 p-6 max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-lg font-semibold text-white">Tambah Karyawan Baru</h3>
                    <p class="text-xs text-gray-500 mt-1">Isi data karyawan yang akan ditambahkan</p>
                </div>
                <button onclick="closeModal('createModal')" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.employees.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-400 mb-1">Nama Lengkap <span
                                class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            placeholder="Nama lengkap karyawan"
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">NIP <span
                                class="text-red-400">*</span></label>
                        <input type="text" name="employee_id" value="{{ old('employee_id') }}" required
                            placeholder="Contoh: EMP001"
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Email <span
                                class="text-red-400">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            placeholder="email@contoh.com"
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Password <span
                                class="text-red-400">*</span></label>
                        <input type="password" name="password" required placeholder="Minimal 6 karakter"
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                            placeholder="08xxxxxxxxxx"
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Departemen <span
                                class="text-red-400">*</span></label>
                        <input type="text" name="department" value="{{ old('department') }}" required
                            placeholder="Contoh: Kasir"
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500"
                            list="departmentList">
                        <datalist id="departmentList">
                            @foreach ($departments as $dept)
                                <option value="{{ $dept }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Jabatan <span
                                class="text-red-400">*</span></label>
                        <input type="text" name="position" value="{{ old('position') }}" required
                            placeholder="Contoh: Staff"
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Shift <span
                                class="text-red-400">*</span></label>
                        <select name="shift_id" required
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Pilih shift...</option>
                            @foreach ($shifts as $s)
                                <option value="{{ $s->id }}" {{ old('shift_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('createModal')"
                        class="flex-1 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-gray-300 text-sm rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-colors">
                        Tambah Karyawan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Employee Modal --}}
    <div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="absolute inset-0 bg-black/70" onclick="closeModal('editModal')"></div>
        <div class="relative bg-slate-900 rounded-2xl border border-white/10 p-6 max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-lg font-semibold text-white">Edit Data Karyawan</h3>
                    <p class="text-xs text-gray-500 mt-1">Perbarui informasi karyawan</p>
                </div>
                <button onclick="closeModal('editModal')" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="editForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-400 mb-1">Nama Lengkap <span
                                class="text-red-400">*</span></label>
                        <input type="text" name="name" id="editName" required
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">NIP <span
                                class="text-red-400">*</span></label>
                        <input type="text" name="employee_id" id="editEmployeeId" required
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Email <span
                                class="text-red-400">*</span></label>
                        <input type="email" name="email" id="editEmail" required
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Password Baru</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak diubah"
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Telepon</label>
                        <input type="text" name="phone" id="editPhone"
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Departemen <span
                                class="text-red-400">*</span></label>
                        <input type="text" name="department" id="editDepartment" required
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500"
                            list="departmentListEdit">
                        <datalist id="departmentListEdit">
                            @foreach ($departments as $dept)
                                <option value="{{ $dept }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Jabatan <span
                                class="text-red-400">*</span></label>
                        <input type="text" name="position" id="editPosition" required
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Shift <span
                                class="text-red-400">*</span></label>
                        <select name="shift_id" id="editShiftId" required
                            class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach ($shifts as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('editModal')"
                        class="flex-1 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-gray-300 text-sm rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors">
                        Simpan Perubahan
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

        function openEditModal(data) {
            document.getElementById('editForm').action = `/admin/employees/${data.id}`;
            document.getElementById('editName').value = data.name;
            document.getElementById('editEmail').value = data.email;
            document.getElementById('editEmployeeId').value = data.employee_id;
            document.getElementById('editDepartment').value = data.department;
            document.getElementById('editPosition').value = data.position;
            document.getElementById('editShiftId').value = data.shift_id;
            document.getElementById('editPhone').value = data.phone || '';
            openModal('editModal');
        }

        // Auto-open create modal if there are validation errors for store
        @if ($errors->any() && old('_method') !== 'PUT')
            openModal('createModal');
        @endif
    </script>
@endsection
