<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\CompanyLocation;
use App\Models\QrToken;
use App\Models\User;
use Carbon\Carbon;

class AttendanceService
{
    protected float $companyLat;
    protected float $companyLng;
    protected int $geofenceRadius;
    protected int $lateTolerance;
    protected int $veryLateMinutes;

    public function __construct()
    {
        // Read GPS coordinates from database
        $location = CompanyLocation::getSettings();
        $this->companyLat = (float) $location['latitude'];
        $this->companyLng = (float) $location['longitude'];
        $this->geofenceRadius = (int) $location['radius'];
        $this->lateTolerance = (int) config('app.late_tolerance_minutes', 10);
        $this->veryLateMinutes = (int) config('app.very_late_minutes', 30);
    }

    /**
     * Process clock-in for an employee.
     */
    public function clockIn(User $user, QrToken $qrToken, ?float $latitude = null, ?float $longitude = null): array
    {
        // Check if already clocked in today
        $existing = Attendance::where('user_id', $user->id)
            ->where('date', today())
            ->whereNotNull('clock_in')
            ->first();

        if ($existing) {
            return [
                'success' => false,
                'message' => 'Anda sudah melakukan absensi masuk hari ini pada ' . $existing->clock_in->format('H:i'),
            ];
        }

        // Validate location — GPS is MANDATORY
        if ($latitude === null || $longitude === null) {
            return [
                'success' => false,
                'message' => 'Lokasi GPS wajib diaktifkan untuk absensi. Pastikan GPS pada perangkat Anda sudah dinyalakan dan izinkan akses lokasi.',
            ];
        }

        $distance = $this->calculateDistance($latitude, $longitude);
        $locationValid = $distance <= $this->geofenceRadius;

        if (!$locationValid) {
            return [
                'success' => false,
                'message' => 'Anda berada di luar area toko. Jarak Anda: ' . round($distance) . ' meter dari toko (maksimal ' . $this->geofenceRadius . ' meter). Silakan mendekat ke area toko untuk melakukan absensi.',
            ];
        }

        // Determine clock-in status
        $shift = $user->shift;
        $shiftStart = today()->setTimeFromTimeString($shift->getRawOriginal('start_time'));
        $now = now();
        $diffMinutes = $shiftStart->diffInMinutes($now, false);

        $status = 'on_time';
        if ($diffMinutes > $this->veryLateMinutes) {
            $status = 'very_late';
        } elseif ($diffMinutes > $this->lateTolerance) {
            $status = 'late';
        }

        // Mark QR token as used
        $qrToken->markAsUsed($user->id);

        // Create attendance record
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => $now,
            'clock_in_status' => $status,
            'shift_id' => $user->shift_id,
            'qr_token_id' => $qrToken->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'location_valid' => $locationValid,
        ]);

        $statusLabels = [
            'on_time' => 'Tepat Waktu',
            'late' => 'Terlambat (' . $diffMinutes . ' menit)',
            'very_late' => 'Sangat Terlambat (' . $diffMinutes . ' menit)',
        ];

        return [
            'success' => true,
            'message' => 'Absensi masuk berhasil! Status: ' . $statusLabels[$status],
            'attendance' => $attendance,
            'status' => $status,
            'late_minutes' => max(0, $diffMinutes),
        ];
    }

    /**
     * Process clock-out for an employee.
     */
    public function clockOut(User $user, ?float $latitude = null, ?float $longitude = null): array
    {
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', today())
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->first();

        if (!$attendance) {
            return [
                'success' => false,
                'message' => 'Anda belum melakukan absensi masuk hari ini, atau sudah melakukan absensi keluar.',
            ];
        }

        // Validate location — GPS is MANDATORY
        if ($latitude === null || $longitude === null) {
            return [
                'success' => false,
                'message' => 'Lokasi GPS wajib diaktifkan untuk absensi keluar. Pastikan GPS pada perangkat Anda sudah dinyalakan.',
            ];
        }

        $distance = $this->calculateDistance($latitude, $longitude);
        $locationValid = $distance <= $this->geofenceRadius;

        if (!$locationValid) {
            return [
                'success' => false,
                'message' => 'Anda berada di luar area toko. Jarak Anda: ' . round($distance) . ' meter dari toko (maksimal ' . $this->geofenceRadius . ' meter).',
            ];
        }

        // Determine clock-out status
        $shift = $user->shift;
        $shiftEnd = today()->setTimeFromTimeString($shift->getRawOriginal('end_time'));
        $now = now();
        $diffMinutes = $now->diffInMinutes($shiftEnd, false);

        $clockOutStatus = $diffMinutes > 15 ? 'early_leave' : 'on_time';

        $attendance->update([
            'clock_out' => $now,
            'clock_out_status' => $clockOutStatus,
        ]);

        $workMinutes = $attendance->clock_in->diffInMinutes($now);
        $hours = intdiv($workMinutes, 60);
        $mins = $workMinutes % 60;

        return [
            'success' => true,
            'message' => "Absensi keluar berhasil! Durasi kerja: {$hours} jam {$mins} menit.",
            'attendance' => $attendance->fresh(),
        ];
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     * Returns distance in meters.
     */
    public function calculateDistance(float $lat, float $lng): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat - $this->companyLat);
        $dLng = deg2rad($lng - $this->companyLng);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($this->companyLat)) * cos(deg2rad($lat)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get the company location and geofence settings.
     */
    public function getGeofenceSettings(): array
    {
        return [
            'latitude' => $this->companyLat,
            'longitude' => $this->companyLng,
            'radius' => $this->geofenceRadius,
        ];
    }

    /**
     * Get dashboard statistics.
     */
    public function getDashboardStats(): array
    {
        $today = today();
        $totalEmployees = User::where('role', 'employee')->where('is_active', true)->count();

        $todayAttendances = Attendance::where('date', $today)->get();
        $presentToday = $todayAttendances->whereNotNull('clock_in')->count();
        $lateToday = $todayAttendances->whereIn('clock_in_status', ['late', 'very_late'])->count();
        $onTimeToday = $todayAttendances->where('clock_in_status', 'on_time')->count();
        $absentToday = $totalEmployees - $presentToday;

        // Monthly stats
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $monthAttendances = Attendance::whereBetween('date', [$monthStart, $monthEnd])->get();

        // Average late minutes this month
        $avgLateMinutes = 0;
        $lateRecords = $monthAttendances->whereIn('clock_in_status', ['late', 'very_late']);
        if ($lateRecords->count() > 0) {
            $totalLateMinutes = 0;
            foreach ($lateRecords as $record) {
                $totalLateMinutes += $record->late_minutes;
            }
            $avgLateMinutes = round($totalLateMinutes / $lateRecords->count(), 1);
        }

        // Weekly trend (last 7 days)
        $weeklyTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $dayAttendance = Attendance::where('date', $date)->count();
            $weeklyTrend[] = [
                'date' => $date->format('D, d/m'),
                'count' => $dayAttendance,
                'rate' => $totalEmployees > 0 ? round(($dayAttendance / $totalEmployees) * 100, 1) : 0,
            ];
        }

        return [
            'total_employees' => $totalEmployees,
            'present_today' => $presentToday,
            'absent_today' => $absentToday,
            'late_today' => $lateToday,
            'on_time_today' => $onTimeToday,
            'attendance_rate' => $totalEmployees > 0 ? round(($presentToday / $totalEmployees) * 100, 1) : 0,
            'avg_late_minutes' => $avgLateMinutes,
            'monthly_attendance_count' => $monthAttendances->count(),
            'weekly_trend' => $weeklyTrend,
        ];
    }

    /**
     * Get chart data for dashboard.
     */
    public function getChartData(): array
    {
        $today = today();
        $totalEmployees = User::where('role', 'employee')->where('is_active', true)->count();

        // 30-day attendance trend
        $thirtyDayTrend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            if ($date->isSunday()) continue;

            $dayStats = Attendance::where('date', $date)->get();
            $thirtyDayTrend[] = [
                'date' => $date->format('d/m'),
                'present' => $dayStats->whereNotNull('clock_in')->count(),
                'late' => $dayStats->whereIn('clock_in_status', ['late', 'very_late'])->count(),
                'absent' => $totalEmployees - $dayStats->whereNotNull('clock_in')->count(),
            ];
        }

        // Status distribution (this month)
        $monthAttendances = Attendance::inMonth($today->year, $today->month)->get();
        $statusDistribution = [
            'on_time' => $monthAttendances->where('clock_in_status', 'on_time')->count(),
            'late' => $monthAttendances->where('clock_in_status', 'late')->count(),
            'very_late' => $monthAttendances->where('clock_in_status', 'very_late')->count(),
        ];

        // Department attendance rates
        $departments = User::where('role', 'employee')
            ->where('is_active', true)
            ->pluck('department')
            ->unique();

        $departmentStats = [];
        foreach ($departments as $dept) {
            $deptUsers = User::where('department', $dept)->where('role', 'employee')->pluck('id');
            $deptTotal = $deptUsers->count();
            $deptPresent = Attendance::where('date', $today)
                ->whereIn('user_id', $deptUsers)
                ->whereNotNull('clock_in')
                ->count();

            $departmentStats[] = [
                'department' => $dept,
                'total' => $deptTotal,
                'present' => $deptPresent,
                'rate' => $deptTotal > 0 ? round(($deptPresent / $deptTotal) * 100, 1) : 0,
            ];
        }

        // Shift distribution
        $shift1Present = Attendance::where('date', $today)->where('shift_id', 1)->whereNotNull('clock_in')->count();
        $shift2Present = Attendance::where('date', $today)->where('shift_id', 2)->whereNotNull('clock_in')->count();

        // Top late employees (this month)
        $topLate = Attendance::inMonth($today->year, $today->month)
            ->whereIn('clock_in_status', ['late', 'very_late'])
            ->selectRaw('user_id, COUNT(*) as late_count')
            ->groupBy('user_id')
            ->orderByDesc('late_count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $user = User::find($item->user_id);
                return [
                    'name' => $user->name ?? 'Unknown',
                    'department' => $user->department ?? '-',
                    'late_count' => $item->late_count,
                ];
            });

        return [
            'thirty_day_trend' => $thirtyDayTrend,
            'status_distribution' => $statusDistribution,
            'department_stats' => $departmentStats,
            'shift_distribution' => [
                'shift_1' => $shift1Present,
                'shift_2' => $shift2Present,
            ],
            'top_late' => $topLate,
        ];
    }
}
