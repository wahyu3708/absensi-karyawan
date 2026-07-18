<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    /**
     * Employee dashboard.
     */
    public function dashboard()
    {
        /** @var User $user */
        $user = Auth::user();
        $todayAttendance = $user->todayAttendance();
        $shift = $user->shift;

        // This month's stats
        $month = now()->month;
        $year = now()->year;
        $monthAttendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $monthStats = [
            'total_present' => $monthAttendances->whereNotNull('clock_in')->count(),
            'total_late' => $monthAttendances->whereIn('clock_in_status', ['late', 'very_late'])->count(),
            'total_on_time' => $monthAttendances->where('clock_in_status', 'on_time')->count(),
            'attendance_rate' => $user->attendanceRate($year, $month),
        ];

        // Last 7 attendances
        $recentAttendances = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        return view('employee.dashboard', compact('user', 'todayAttendance', 'shift', 'monthStats', 'recentAttendances'));
    }
}
