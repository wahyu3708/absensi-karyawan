<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Show leave requests for the logged-in employee.
     */
    public function employeeIndex(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $status = $request->get('status');

        $query = LeaveRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $leaves = $query->paginate(10);

        return view('employee.leaves', compact('leaves', 'user', 'status'));
    }

    /**
     * Store a new leave request from an employee.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:sick,annual,permission',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
        ], [
            'type.required' => 'Jenis izin harus dipilih.',
            'type.in' => 'Jenis izin tidak valid.',
            'start_date.required' => 'Tanggal mulai harus diisi.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh di masa lalu.',
            'end_date.required' => 'Tanggal selesai harus diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'reason.required' => 'Alasan harus diisi.',
            'reason.min' => 'Alasan minimal 10 karakter.',
            'reason.max' => 'Alasan maksimal 500 karakter.',
        ]);

        /** @var User $user */
        $user = Auth::user();

        // Check for overlapping leave requests
        $overlapping = LeaveRequest::where('user_id', $user->id)
            ->where('status', '!=', 'rejected')
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($q2) use ($request) {
                        $q2->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                    });
            })
            ->exists();

        if ($overlapping) {
            return back()->with('error', 'Anda sudah memiliki pengajuan izin pada tanggal tersebut.')->withInput();
        }

        LeaveRequest::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Pengajuan izin berhasil dikirim. Menunggu persetujuan admin.');
    }

    /**
     * Show all leave requests for admin.
     */
    public function adminIndex(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');

        $query = LeaveRequest::with(['user', 'approver'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $leaves = $query->paginate(15);
        $pendingCount = LeaveRequest::where('status', 'pending')->count();

        return view('admin.leaves', compact('leaves', 'status', 'search', 'pendingCount'));
    }

    /**
     * Approve a leave request.
     */
    public function approve(Request $request, $id)
    {
        $leave = LeaveRequest::findOrFail($id);

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Pengajuan izin ini sudah diproses sebelumnya.');
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'admin_notes' => $request->get('admin_notes'),
        ]);

        // Send notification to employee
        $this->notificationService->sendToUser(
            $leave->user_id,
            '✅ Izin Disetujui',
            "Pengajuan {$leave->type_label} Anda ({$leave->start_date->format('d/m/Y')} - {$leave->end_date->format('d/m/Y')}) telah disetujui.",
            'leave_update',
            ['url' => '/employee/leaves']
        );

        return back()->with('success', "Izin {$leave->user->name} telah disetujui.");
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string|min:5|max:500',
        ], [
            'admin_notes.required' => 'Alasan penolakan harus diisi.',
            'admin_notes.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $leave = LeaveRequest::findOrFail($id);

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Pengajuan izin ini sudah diproses sebelumnya.');
        }

        $leave->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'admin_notes' => $request->admin_notes,
        ]);

        // Send notification to employee
        $this->notificationService->sendToUser(
            $leave->user_id,
            '❌ Izin Ditolak',
            "Pengajuan {$leave->type_label} Anda ({$leave->start_date->format('d/m/Y')} - {$leave->end_date->format('d/m/Y')}) ditolak. Alasan: {$request->admin_notes}",
            'leave_update',
            ['url' => '/employee/leaves']
        );

        return back()->with('success', "Izin {$leave->user->name} telah ditolak.");
    }
}
