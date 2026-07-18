@extends('layouts.app')

@section('title', 'Pengumuman - Admin')

@section('header', 'Pengumuman')

@section('content')

    <div class="space-y-4">
        {{-- Send Announcement --}}
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Kirim Pengumuman</h3>
                    <p class="text-xs text-gray-500">Pengumuman akan dikirim ke semua karyawan aktif</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.announcements.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Judul <span class="text-red-400">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        placeholder="Contoh: Jadwal Libur Lebaran 2025"
                        class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
                    @error('title')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Isi Pengumuman <span
                            class="text-red-400">*</span></label>
                    <textarea name="body" rows="4" required
                        placeholder="Tulis isi pengumuman yang ingin disampaikan ke seluruh karyawan..."
                        class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-indigo-500 focus:border-indigo-500 resize-none">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-xl shadow-lg shadow-indigo-500/25 transition-all duration-300 text-sm"
                        onclick="return confirm('Kirim pengumuman ini ke semua karyawan?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Kirim Pengumuman
                    </button>
                </div>
            </form>
        </div>

        {{-- Announcement History --}}
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5">
            <h3 class="text-sm font-semibold text-white mb-4">Riwayat Pengumuman</h3>

            <div class="space-y-3">
                @forelse($uniqueAnnouncements as $ann)
                    <div class="p-4 bg-slate-800/30 rounded-xl border border-white/5 hover:bg-slate-800/50 transition-colors">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                <span class="text-lg shrink-0 mt-0.5">📢</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white">{{ $ann->title }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $ann->body }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                    {{ $ann->recipient_count }} penerima
                                </span>
                                <p class="text-xs text-gray-600 mt-1">
                                    {{ \Carbon\Carbon::parse($ann->sent_at)->translatedFormat('d M Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <p class="text-gray-600 text-sm">Belum ada pengumuman yang dikirim</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $uniqueAnnouncements->links() }}
            </div>
        </div>
    </div>
@endsection
