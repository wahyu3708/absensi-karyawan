@extends('layouts.app')

@section('title', 'Dashboard Admin - Absensi Karyawan')

@section('header', 'Dashboard')

@section('content')

    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <!-- Total Karyawan -->
            <div
                class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5 hover:border-indigo-500/20 transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Total Karyawan</p>
                        <p class="text-3xl font-bold text-white mt-1" id="stat-total">{{ $stats['total_employees'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Aktif</p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-indigo-500/10 flex items-center justify-center group-hover:bg-indigo-500/20 transition-colors">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Hadir Hari Ini -->
            <div
                class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5 hover:border-emerald-500/20 transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Hadir Hari Ini</p>
                        <p class="text-3xl font-bold text-emerald-400 mt-1" id="stat-present">{{ $stats['present_today'] }}
                        </p>
                        <p class="text-xs text-emerald-500/80 mt-1">{{ $stats['attendance_rate'] }}% kehadiran</p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center group-hover:bg-emerald-500/20 transition-colors">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <!-- Progress bar -->
                <div class="mt-3 w-full bg-slate-800 rounded-full h-1.5">
                    <div class="bg-gradient-to-r from-emerald-500 to-emerald-400 h-1.5 rounded-full transition-all duration-1000"
                        style="width: {{ $stats['attendance_rate'] }}%"></div>
                </div>
            </div>

            <!-- Terlambat -->
            <div
                class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5 hover:border-amber-500/20 transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Terlambat</p>
                        <p class="text-3xl font-bold text-amber-400 mt-1" id="stat-late">{{ $stats['late_today'] }}</p>
                        <p class="text-xs text-amber-500/80 mt-1">Rata-rata {{ $stats['avg_late_minutes'] }} menit</p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center group-hover:bg-amber-500/20 transition-colors">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Tidak Hadir -->
            <div
                class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5 hover:border-red-500/20 transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Tidak Hadir</p>
                        <p class="text-3xl font-bold text-red-400 mt-1" id="stat-absent">{{ $stats['absent_today'] }}</p>
                        <p class="text-xs text-red-500/80 mt-1">Dari {{ $stats['total_employees'] }} karyawan</p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-red-500/10 flex items-center justify-center group-hover:bg-red-500/20 transition-colors">
                        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Attendance Trend (30 days) -->
            <div class="lg:col-span-2 bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-white">Tren Kehadiran 30 Hari</h3>
                    <span class="text-xs text-gray-500">{{ now()->subDays(29)->format('d M') }} -
                        {{ now()->format('d M Y') }}</span>
                </div>
                <div class="h-64">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <!-- Status Distribution -->
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5">
                <h3 class="text-sm font-semibold text-white mb-4">Distribusi Status Bulan Ini</h3>
                <div class="h-64 flex items-center justify-center">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Second Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Department Stats -->
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5">
                <h3 class="text-sm font-semibold text-white mb-4">Kehadiran per Departemen Hari Ini</h3>
                <div class="h-64">
                    <canvas id="departmentChart"></canvas>
                </div>
            </div>

            <!-- Shift Distribution -->
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5">
                <h3 class="text-sm font-semibold text-white mb-4">Top 10 Karyawan Terlambat (Bulan Ini)</h3>
                <div class="h-64">
                    <canvas id="topLateChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Attendance Table -->
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/5 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-white">Absensi Terbaru Hari Ini</h3>
                <a href="{{ route('admin.attendances') }}"
                    class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">Lihat Semua →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-gray-500 text-xs uppercase tracking-wider">
                            <th class="text-left py-3 px-2">Karyawan</th>
                            <th class="text-left py-3 px-2">Departemen</th>
                            <th class="text-left py-3 px-2">Shift</th>
                            <th class="text-left py-3 px-2">Masuk</th>
                            <th class="text-left py-3 px-2">Keluar</th>
                            <th class="text-left py-3 px-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($recentAttendances as $attendance)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="py-3 px-2">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-xs font-bold text-white">
                                            {{ substr($attendance->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-white text-sm">{{ $attendance->user->name }}</p>
                                            <p class="text-gray-600 text-xs">{{ $attendance->user->employee_id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-2 text-gray-400">{{ $attendance->user->department }}</td>
                                <td class="py-3 px-2 text-gray-400">{{ $attendance->shift->name }}</td>
                                <td class="py-3 px-2 text-gray-300">{{ $attendance->clock_in?->format('H:i') ?? '-' }}
                                </td>
                                <td class="py-3 px-2 text-gray-300">{{ $attendance->clock_out?->format('H:i') ?? '-' }}
                                </td>
                                <td class="py-3 px-2">
                                    @if ($attendance->clock_in_status === 'on_time')
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Tepat
                                            Waktu</span>
                                    @elseif($attendance->clock_in_status === 'late')
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">Terlambat</span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">Sangat
                                            Terlambat</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-600">Belum ada data absensi hari ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        // Fetch chart data
        fetch('/api/dashboard/charts')
            .then(r => r.json())
            .then(data => {
                initTrendChart(data.thirty_day_trend);
                initStatusChart(data.status_distribution);
                initDepartmentChart(data.department_stats);
                initTopLateChart(data.top_late);
            });

        const chartColors = {
            indigo: {
                bg: 'rgba(99, 102, 241, 0.15)',
                border: 'rgba(99, 102, 241, 1)'
            },
            emerald: {
                bg: 'rgba(16, 185, 129, 0.15)',
                border: 'rgba(16, 185, 129, 1)'
            },
            amber: {
                bg: 'rgba(245, 158, 11, 0.15)',
                border: 'rgba(245, 158, 11, 1)'
            },
            red: {
                bg: 'rgba(239, 68, 68, 0.15)',
                border: 'rgba(239, 68, 68, 1)'
            },
            purple: {
                bg: 'rgba(168, 85, 247, 0.15)',
                border: 'rgba(168, 85, 247, 1)'
            },
        };

        const defaultOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#9ca3af',
                        font: {
                            size: 11
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#6b7280',
                        font: {
                            size: 10
                        }
                    },
                    grid: {
                        color: 'rgba(255,255,255,0.03)'
                    }
                },
                y: {
                    ticks: {
                        color: '#6b7280',
                        font: {
                            size: 10
                        }
                    },
                    grid: {
                        color: 'rgba(255,255,255,0.03)'
                    }
                }
            }
        };

        function initTrendChart(data) {
            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: data.map(d => d.date),
                    datasets: [{
                            label: 'Hadir',
                            data: data.map(d => d.present),
                            borderColor: chartColors.emerald.border,
                            backgroundColor: chartColors.emerald.bg,
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                        },
                        {
                            label: 'Terlambat',
                            data: data.map(d => d.late),
                            borderColor: chartColors.amber.border,
                            backgroundColor: chartColors.amber.bg,
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                        },
                        {
                            label: 'Tidak Hadir',
                            data: data.map(d => d.absent),
                            borderColor: chartColors.red.border,
                            backgroundColor: chartColors.red.bg,
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                        }
                    ]
                },
                options: defaultOptions
            });
        }

        function initStatusChart(data) {
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Tepat Waktu', 'Terlambat', 'Sangat Terlambat'],
                    datasets: [{
                        data: [data.on_time, data.late, data.very_late],
                        backgroundColor: [chartColors.emerald.border, chartColors.amber.border, chartColors
                            .red.border
                        ],
                        borderWidth: 0,
                        hoverOffset: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#9ca3af',
                                font: {
                                    size: 11
                                },
                                padding: 12
                            }
                        }
                    }
                }
            });
        }

        function initDepartmentChart(data) {
            new Chart(document.getElementById('departmentChart'), {
                type: 'bar',
                data: {
                    labels: data.map(d => d.department),
                    datasets: [{
                        label: 'Hadir',
                        data: data.map(d => d.present),
                        backgroundColor: chartColors.indigo.border,
                        borderRadius: 6,
                        barThickness: 20,
                    }, {
                        label: 'Total',
                        data: data.map(d => d.total),
                        backgroundColor: 'rgba(255,255,255,0.05)',
                        borderRadius: 6,
                        barThickness: 20,
                    }]
                },
                options: {
                    ...defaultOptions,
                    indexAxis: 'y',
                }
            });
        }

        function initTopLateChart(data) {
            new Chart(document.getElementById('topLateChart'), {
                type: 'bar',
                data: {
                    labels: data.map(d => d.name),
                    datasets: [{
                        label: 'Jumlah Terlambat',
                        data: data.map(d => d.late_count),
                        backgroundColor: chartColors.amber.border,
                        borderRadius: 6,
                        barThickness: 16,
                    }]
                },
                options: {
                    ...defaultOptions,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Auto-refresh stats every 30 seconds
        setInterval(() => {
            fetch('/api/dashboard/stats')
                .then(r => r.json())
                .then(s => {
                    document.getElementById('stat-total').textContent = s.total_employees;
                    document.getElementById('stat-present').textContent = s.present_today;
                    document.getElementById('stat-late').textContent = s.late_today;
                    document.getElementById('stat-absent').textContent = s.absent_today;
                });
        }, 30000);
    </script>
@endpush
