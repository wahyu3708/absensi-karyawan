<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $notifications = $this->notificationService->getRecent($user->id, 20);

        return response()->json([
            'notifications' => $notifications->map(fn(Notification $n) => [
                'id' => $n->id,
                'title' => $n->title,
                'body' => $n->body,
                'type' => $n->type,
                'icon' => $n->icon,
                'data' => $n->data,
                'is_read' => $n->is_read,
                'time_ago' => $n->time_ago,
                'created_at' => $n->created_at->toIso8601String(),
            ]),
            'unread_count' => $this->notificationService->getUnreadCount($user->id),
        ]);
    }

    /**
     * Get unread count (lightweight polling endpoint).
     */
    public function unreadCount()
    {
        /** @var User $user */
        $user = Auth::user();

        return response()->json([
            'count' => $this->notificationService->getUnreadCount($user->id),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead($id)
    {
        /** @var User $user */
        $user = Auth::user();

        $notification = Notification::where('user_id', $user->id)->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        /** @var User $user */
        $user = Auth::user();

        $count = $this->notificationService->markAllAsRead($user->id);

        return response()->json([
            'success' => true,
            'marked_count' => $count,
        ]);
    }

    /**
     * Check for reminders (called periodically from frontend).
     * Also triggers absent reminders check.
     */
    public function checkReminders()
    {
        /** @var User $user */
        $user = Auth::user();

        // Trigger absent reminder check for this user specifically
        if ($user->role === 'employee') {
            $this->notificationService->checkAndSendAbsentReminders();
        }

        // Return latest unread notifications (new since last check)
        $newNotifications = Notification::forUser($user->id)
            ->unread()
            ->where('created_at', '>=', now()->subMinutes(5))
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'has_new' => $newNotifications->count() > 0,
            'notifications' => $newNotifications->map(fn(Notification $n) => [
                'id' => $n->id,
                'title' => $n->title,
                'body' => $n->body,
                'type' => $n->type,
                'icon' => $n->icon,
            ]),
            'unread_count' => $this->notificationService->getUnreadCount($user->id),
        ]);
    }

    /**
     * Show announcements page (admin).
     */
    public function announcementsPage()
    {
        // Get unique announcements (by title + created_at minute)
        $uniqueAnnouncements = Notification::where('type', 'announcement')
            ->selectRaw('title, body, MIN(created_at) as sent_at, COUNT(*) as recipient_count')
            ->groupBy('title', 'body')
            ->orderByDesc('sent_at')
            ->paginate(15);

        return view('admin.announcements', compact('uniqueAnnouncements'));
    }

    /**
     * Send a broadcast announcement (admin).
     */
    public function broadcastStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|min:5|max:1000',
        ], [
            'title.required' => 'Judul pengumuman harus diisi.',
            'title.max' => 'Judul maksimal 255 karakter.',
            'body.required' => 'Isi pengumuman harus diisi.',
            'body.min' => 'Isi pengumuman minimal 5 karakter.',
            'body.max' => 'Isi pengumuman maksimal 1000 karakter.',
        ]);

        $count = $this->notificationService->broadcast(
            '📢 ' . $request->title,
            $request->body,
            'announcement'
        );

        return back()->with('success', "Pengumuman berhasil dikirim ke {$count} karyawan.");
    }
}
