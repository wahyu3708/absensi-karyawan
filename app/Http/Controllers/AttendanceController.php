<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CompanyLocation;
use App\Models\Shift;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\NotificationService;
use App\Services\QrTokenService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;
    protected QrTokenService $qrTokenService;
    protected NotificationService $notificationService;

    public function __construct(AttendanceService $attendanceService, QrTokenService $qrTokenService, NotificationService $notificationService)
    {
        $this->attendanceService = $attendanceService;
        $this->qrTokenService = $qrTokenService;
        $this->notificationService = $notificationService;
    }

    /**
     * Show the QR scanner page (for employees).
     */
    public function scanPage()
    {
        $user = $this->currentUser();
        $todayAttendance = $user->todayAttendance();
        $companyLocation = CompanyLocation::getSettings();

        return view('employee.scan', compact('user', 'todayAttendance', 'companyLocation'));
    }

    /**
     * Process clock-in via QR code scan.
     */
    public function clockIn(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $user = $this->currentUser();

        // Validate QR token
        $qrToken = $this->qrTokenService->validate($request->qr_data);
        if (!$qrToken) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau sudah kadaluarsa. Silakan scan ulang.',
            ], 422);
        }

        // Process clock-in
        $result = $this->attendanceService->clockIn(
            $user,
            $qrToken,
            $request->latitude,
            $request->longitude
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Process clock-out.
     */
    public function clockOut(Request $request)
    {
        $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $user = $this->currentUser();

        $result = $this->attendanceService->clockOut(
            $user,
            $request->latitude,
            $request->longitude
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Get today's attendance status for the logged-in user.
     */
    public function todayStatus()
    {
        $user = $this->currentUser();
        $attendance = $user->todayAttendance();

        return response()->json([
            'has_clocked_in' => $attendance && $attendance->clock_in !== null,
            'has_clocked_out' => $attendance && $attendance->clock_out !== null,
            'attendance' => $attendance,
            'shift' => $user->shift,
        ]);
    }

    /**
     * Show attendance history for the logged-in employee.
     */
    public function history(Request $request)
    {
        $user = $this->currentUser();
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date', 'desc')
            ->get();

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = \Carbon\Carbon::create()->month($i)->translatedFormat('F');
        }

        return view('employee.history', compact('attendances', 'month', 'year', 'months', 'user'));
    }

    /**
     * Show all attendances for admin.
     */
    public function adminIndex(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));
        $department = $request->get('department');
        $shift = $request->get('shift');

        $query = Attendance::with(['user', 'shift'])
            ->where('date', $date)
            ->orderBy('clock_in', 'desc');

        if ($department) {
            $query->whereHas('user', fn($q) => $q->where('department', $department));
        }

        if ($shift) {
            $query->where('shift_id', $shift);
        }

        $attendances = $query->get();

        // Get employees who haven't clocked in
        $attendedUserIds = $attendances->pluck('user_id');
        $absentEmployees = User::where('role', 'employee')
            ->where('is_active', true)
            ->whereNotIn('id', $attendedUserIds)
            ->get();

        $departments = User::where('role', 'employee')->pluck('department')->unique()->sort();
        $employees = User::where('role', 'employee')->where('is_active', true)->with('shift')->orderBy('name')->get();
        $shifts = Shift::all();

        return view('admin.attendances', compact('attendances', 'absentEmployees', 'date', 'department', 'shift', 'departments', 'employees', 'shifts'));
    }

    /**
     * Manual clock-in by admin (for employees who forgot to scan QR).
     */
    public function manualClockIn(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date|before_or_equal:today',
            'clock_in_time' => 'required|date_format:H:i',
            'clock_out_time' => 'nullable|date_format:H:i|after:clock_in_time',
            'notes' => 'required|string|min:5|max:500',
        ], [
            'user_id.required' => 'Karyawan harus dipilih.',
            'user_id.exists' => 'Karyawan tidak ditemukan.',
            'date.required' => 'Tanggal harus diisi.',
            'date.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
            'clock_in_time.required' => 'Jam masuk harus diisi.',
            'clock_in_time.date_format' => 'Format jam masuk tidak valid (HH:MM).',
            'clock_out_time.date_format' => 'Format jam keluar tidak valid (HH:MM).',
            'clock_out_time.after' => 'Jam keluar harus setelah jam masuk.',
            'notes.required' => 'Catatan/alasan harus diisi.',
            'notes.min' => 'Catatan minimal 5 karakter.',
        ]);

        $employee = User::findOrFail($request->user_id);

        // Check if attendance already exists for this date
        $existing = Attendance::where('user_id', $employee->id)
            ->where('date', $request->date)
            ->first();

        if ($existing) {
            return back()->with('error', "Karyawan {$employee->name} sudah memiliki data absensi pada tanggal tersebut.");
        }

        // Calculate clock-in status
        $shift = $employee->shift;
        if (!$shift) {
            return back()->with('error', "Karyawan {$employee->name} belum memiliki shift yang ditentukan.");
        }

        $date = Carbon::parse($request->date);
        $clockIn = $date->copy()->setTimeFromTimeString($request->clock_in_time . ':00');
        $shiftStart = $date->copy()->setTimeFromTimeString($shift->getRawOriginal('start_time'));
        $diffMinutes = $shiftStart->diffInMinutes($clockIn, false);

        $lateTolerance = (int) config('app.late_tolerance_minutes', 10);
        $veryLateMinutes = (int) config('app.very_late_minutes', 30);

        $clockInStatus = 'on_time';
        if ($diffMinutes > $veryLateMinutes) {
            $clockInStatus = 'very_late';
        } elseif ($diffMinutes > $lateTolerance) {
            $clockInStatus = 'late';
        }

        // Calculate clock-out status
        $clockOut = null;
        $clockOutStatus = null;
        if ($request->clock_out_time) {
            $clockOut = $date->copy()->setTimeFromTimeString($request->clock_out_time . ':00');
            $shiftEnd = $date->copy()->setTimeFromTimeString($shift->getRawOriginal('end_time'));
            $outDiff = $clockOut->diffInMinutes($shiftEnd, false);
            $clockOutStatus = $outDiff > 15 ? 'early_leave' : 'on_time';
        }

        Attendance::create([
            'user_id' => $employee->id,
            'date' => $request->date,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'clock_in_status' => $clockInStatus,
            'clock_out_status' => $clockOutStatus,
            'shift_id' => $shift->id,
            'notes' => '[Manual oleh Admin] ' . $request->notes,
            'ip_address' => request()->ip(),
            'location_valid' => true,
        ]);

        // Send notification to employee
        $this->notificationService->sendToUser(
            $employee->id,
            '✍️ Absensi Manual Ditambahkan',
            "Admin telah menambahkan absensi Anda untuk tanggal " . Carbon::parse($request->date)->translatedFormat('d M Y') . ". Jam masuk: {$request->clock_in_time}" . ($request->clock_out_time ? ", Jam keluar: {$request->clock_out_time}" : '') . ". Catatan: {$request->notes}",
            'manual_attendance',
            ['url' => '/employee/history']
        );

        return back()->with('success', "Absensi manual untuk {$employee->name} berhasil ditambahkan.");
    }

    /**
     * Get the currently authenticated user.
     */
    private function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();
        return $user;
    }
}
