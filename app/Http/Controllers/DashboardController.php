<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Shift;
use App\Models\LeaveRequest;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Admin dashboard with metrics and visualizations.
     */
    public function index()
    {
        $stats = $this->attendanceService->getDashboardStats();
        $shifts = Shift::all();
        $pendingLeaves = LeaveRequest::with('user')->pending()->latest()->limit(5)->get();

        // Recent attendance (last 10)
        $recentAttendances = Attendance::with(['user', 'shift'])
            ->where('date', today())
            ->latest('clock_in')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'shifts', 'pendingLeaves', 'recentAttendances'));
    }

    /**
     * API endpoint for real-time dashboard stats.
     */
    public function stats()
    {
        return response()->json($this->attendanceService->getDashboardStats());
    }

    /**
     * API endpoint for chart data.
     */
    public function charts()
    {
        return response()->json($this->attendanceService->getChartData());
    }

    /**
     * Employee management page.
     */
    public function employees(Request $request)
    {
        $query = User::where('role', 'employee')->with('shift');

        $showInactive = $request->get('show_inactive', false);
        if (!$showInactive) {
            $query->where('is_active', true);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('shift')) {
            $query->where('shift_id', $request->shift);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('employee_id')->paginate(15);
        $departments = User::where('role', 'employee')->pluck('department')->unique()->sort();
        $shifts = Shift::all();

        return view('admin.employees', compact('employees', 'departments', 'shifts', 'showInactive'));
    }

    /**
     * Store a new employee.
     */
    public function storeEmployee(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'employee_id' => 'required|string|unique:users,employee_id',
            'department' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'shift_id' => 'required|exists:shifts,id',
            'phone' => 'nullable|string|max:20',
        ], [
            'name.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'employee_id.required' => 'NIP harus diisi.',
            'employee_id.unique' => 'NIP sudah digunakan.',
            'department.required' => 'Departemen harus diisi.',
            'position.required' => 'Jabatan harus diisi.',
            'shift_id.required' => 'Shift harus dipilih.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'employee_id' => $request->employee_id,
            'department' => $request->department,
            'position' => $request->position,
            'shift_id' => $request->shift_id,
            'phone' => $request->phone,
            'role' => 'employee',
            'is_active' => true,
        ]);

        return back()->with('success', "Karyawan {$request->name} berhasil ditambahkan.");
    }

    /**
     * Update an existing employee.
     */
    public function updateEmployee(Request $request, $id)
    {
        $employee = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($employee->id)],
            'password' => 'nullable|string|min:6',
            'employee_id' => ['required', 'string', Rule::unique('users', 'employee_id')->ignore($employee->id)],
            'department' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'shift_id' => 'required|exists:shifts,id',
            'phone' => 'nullable|string|max:20',
        ], [
            'name.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'password.min' => 'Password minimal 6 karakter.',
            'employee_id.required' => 'NIP harus diisi.',
            'employee_id.unique' => 'NIP sudah digunakan.',
            'department.required' => 'Departemen harus diisi.',
            'position.required' => 'Jabatan harus diisi.',
            'shift_id.required' => 'Shift harus dipilih.',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'employee_id' => $request->employee_id,
            'department' => $request->department,
            'position' => $request->position,
            'shift_id' => $request->shift_id,
            'phone' => $request->phone,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);

        return back()->with('success', "Data karyawan {$employee->name} berhasil diperbarui.");
    }

    /**
     * Toggle employee active status (activate/deactivate).
     */
    public function toggleEmployee($id)
    {
        $employee = User::findOrFail($id);
        $employee->update(['is_active' => !$employee->is_active]);

        $status = $employee->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Karyawan {$employee->name} berhasil {$status}.");
    }

    /**
     * Permanently delete an employee.
     */
    public function destroyEmployee($id)
    {
        $employee = User::findOrFail($id);
        $name = $employee->name;

        // Check for attendance records
        $attendanceCount = Attendance::where('user_id', $id)->count();
        if ($attendanceCount > 0) {
            // Soft-deactivate instead of hard delete if there are attendance records
            $employee->update(['is_active' => false]);
            return back()->with('success', "Karyawan {$name} memiliki {$attendanceCount} data absensi, sehingga dinonaktifkan (bukan dihapus) untuk menjaga integritas data.");
        }

        $employee->delete();
        return back()->with('success', "Karyawan {$name} berhasil dihapus permanen.");
    }

    /**
     * Reports page.
     */
    public function reports(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $employees = User::where('role', 'employee')
            ->where('is_active', true)
            ->with('shift')
            ->orderBy('employee_id')
            ->get();

        $reportData = [];
        foreach ($employees as $employee) {
            $attendances = Attendance::where('user_id', $employee->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get();

            $reportData[] = [
                'employee' => $employee,
                'total_present' => $attendances->whereNotNull('clock_in')->count(),
                'total_late' => $attendances->whereIn('clock_in_status', ['late', 'very_late'])->count(),
                'total_on_time' => $attendances->where('clock_in_status', 'on_time')->count(),
                'total_early_leave' => $attendances->where('clock_out_status', 'early_leave')->count(),
                'attendance_rate' => $employee->attendanceRate($year, $month),
            ];
        }

        return view('admin.reports', compact('reportData', 'month', 'year'));
    }
}
