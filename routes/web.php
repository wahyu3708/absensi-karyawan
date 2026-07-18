<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\CompanyLocationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ReportExportController;
use Illuminate\Support\Facades\Route;

// ── Public Routes ──────────────────────────────────────
Route::get('/', fn() => redirect('/login'));

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// ── Admin Routes ───────────────────────────────────────
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/qr-display', [QrCodeController::class, 'display'])->name('admin.qr-display');
    Route::get('/employees', [DashboardController::class, 'employees'])->name('admin.employees');
    Route::get('/attendances', [AttendanceController::class, 'adminIndex'])->name('admin.attendances');
    Route::get('/reports/export-pdf', [ReportExportController::class, 'exportPdf'])->name('admin.reports.export-pdf');
    Route::get('/reports/export-excel', [ReportExportController::class, 'exportExcel'])->name('admin.reports.export-excel');
    Route::get('/reports', [DashboardController::class, 'reports'])->name('admin.reports');

    // Leave Management (admin)
    Route::get('/leaves', [LeaveController::class, 'adminIndex'])->name('admin.leaves');
    Route::patch('/leaves/{id}/approve', [LeaveController::class, 'approve'])->name('admin.leaves.approve');
    Route::patch('/leaves/{id}/reject', [LeaveController::class, 'reject'])->name('admin.leaves.reject');

    // Manual Attendance (admin)
    Route::post('/attendances/manual', [AttendanceController::class, 'manualClockIn'])->name('admin.attendances.manual');

    // Employee CRUD (admin)
    Route::post('/employees', [DashboardController::class, 'storeEmployee'])->name('admin.employees.store');
    Route::put('/employees/{id}', [DashboardController::class, 'updateEmployee'])->name('admin.employees.update');
    Route::patch('/employees/{id}/toggle', [DashboardController::class, 'toggleEmployee'])->name('admin.employees.toggle');
    Route::delete('/employees/{id}', [DashboardController::class, 'destroyEmployee'])->name('admin.employees.destroy');

    // Announcements (admin)
    Route::get('/announcements', [NotificationController::class, 'announcementsPage'])->name('admin.announcements');
    Route::post('/announcements', [NotificationController::class, 'broadcastStore'])->name('admin.announcements.store');

    // Company Location Settings (admin)
    Route::get('/location', [CompanyLocationController::class, 'index'])->name('admin.location');
    Route::put('/location', [CompanyLocationController::class, 'update'])->name('admin.location.update');
});

// ── Employee Routes ────────────────────────────────────
Route::prefix('employee')->middleware(['auth', 'role:employee'])->group(function () {
    Route::get('/dashboard', [EmployeeController::class, 'dashboard'])->name('employee.dashboard');
    Route::get('/scan', [AttendanceController::class, 'scanPage'])->name('employee.scan');
    Route::get('/history', [AttendanceController::class, 'history'])->name('employee.history');

    // Leave Requests (employee)
    Route::get('/leaves', [LeaveController::class, 'employeeIndex'])->name('employee.leaves');
    Route::post('/leaves', [LeaveController::class, 'store'])->name('employee.leaves.store');
});

// ── API Routes (authenticated) ─────────────────────────
Route::prefix('api')->middleware('auth')->group(function () {
    // QR Code
    Route::get('/qr/generate', [QrCodeController::class, 'generate'])->name('api.qr.generate');
    Route::post('/qr/validate', [QrCodeController::class, 'validateToken'])->name('api.qr.validate');

    // Attendance
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('api.attendance.clockin');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('api.attendance.clockout');
    Route::get('/attendance/today', [AttendanceController::class, 'todayStatus'])->name('api.attendance.today');

    // Dashboard API
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('api.dashboard.stats');
    Route::get('/dashboard/charts', [DashboardController::class, 'charts'])->name('api.dashboard.charts');

    // Notifications API
    Route::get('/notifications', [NotificationController::class, 'index'])->name('api.notifications');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unread');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.readall');
    Route::get('/notifications/check-reminders', [NotificationController::class, 'checkReminders'])->name('api.notifications.reminders');
});
