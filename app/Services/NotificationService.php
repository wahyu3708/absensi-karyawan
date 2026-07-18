<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Send a notification to a single user.
     */
    public function sendToUser(int $userId, string $title, string $body, string $type = 'announcement', ?array $data = null): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'data' => $data,
        ]);
    }

    /**
     * Broadcast a notification to all active employees.
     */
    public function broadcast(string $title, string $body, string $type = 'announcement', ?array $data = null): int
    {
        $employees = User::where('role', 'employee')
            ->where('is_active', true)
            ->pluck('id');

        $notifications = [];
        $now = now();

        foreach ($employees as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'title' => $title,
                'body' => $body,
                'type' => $type,
                'data' => $data ? json_encode($data) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Bulk insert for efficiency
        Notification::insert($notifications);

        return count($notifications);
    }

    /**
     * Send shift info notification when employee logs in.
     */
    public function sendShiftInfo(User $user): ?Notification
    {
        $shift = $user->shift;
        if (!$shift) return null;

        // Check if already sent today
        $alreadySent = Notification::where('user_id', $user->id)
            ->where('type', 'shift_info')
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadySent) return null;

        return $this->sendToUser(
            $user->id,
            '📋 Info Shift Hari Ini',
            "Shift Anda hari ini: {$shift->name} ({$shift->start_time} - {$shift->end_time}). Selamat bekerja!",
            'shift_info',
            ['url' => '/employee/scan']
        );
    }

    /**
     * Check and send reminders to employees who haven't clocked in.
     * Should be called periodically (e.g., via polling from frontend).
     */
    public function checkAndSendAbsentReminders(): int
    {
        $now = now();
        $today = today();
        $sentCount = 0;

        // Get all active employees with shifts
        $employees = User::where('role', 'employee')
            ->where('is_active', true)
            ->whereNotNull('shift_id')
            ->with('shift')
            ->get();

        foreach ($employees as $employee) {
            $shift = $employee->shift;
            if (!$shift) continue;

            // Calculate shift start time
            $shiftStart = $today->copy()->setTimeFromTimeString($shift->getRawOriginal('start_time'));
            $reminderTime = $shiftStart->copy()->addMinutes(30);

            // Only send reminder if:
            // 1. Current time is past 30 minutes after shift start
            // 2. Current time is not past shift end (don't nag after hours)
            // 3. Employee hasn't clocked in today
            // 4. Reminder not already sent today
            $shiftEnd = $today->copy()->setTimeFromTimeString($shift->getRawOriginal('end_time'));

            if ($now->lt($reminderTime) || $now->gt($shiftEnd)) {
                continue;
            }

            // Check if already clocked in
            $hasAttendance = Attendance::where('user_id', $employee->id)
                ->where('date', $today)
                ->whereNotNull('clock_in')
                ->exists();

            if ($hasAttendance) continue;

            // Check if reminder already sent today
            $reminderSent = Notification::where('user_id', $employee->id)
                ->where('type', 'reminder')
                ->whereDate('created_at', $today)
                ->exists();

            if ($reminderSent) continue;

            // Send reminder
            $lateMinutes = $now->diffInMinutes($shiftStart);
            $this->sendToUser(
                $employee->id,
                '⏰ Pengingat Absensi',
                "Anda belum melakukan absensi masuk hari ini. Shift Anda ({$shift->name}) dimulai pukul {$shift->start_time}. Anda sudah terlambat {$lateMinutes} menit.",
                'reminder',
                ['url' => '/employee/scan']
            );
            $sentCount++;
        }

        return $sentCount;
    }

    /**
     * Get unread count for a user.
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::forUser($userId)->unread()->count();
    }

    /**
     * Get recent notifications for a user.
     */
    public function getRecent(int $userId, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return Notification::forUser($userId)
            ->recent($limit)
            ->get();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::forUser($userId)
            ->unread()
            ->update(['read_at' => now()]);
    }
}
