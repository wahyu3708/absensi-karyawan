<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'department',
        'position',
        'shift_id',
        'role',
        'phone',
        'avatar',
        'fcm_token',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'fcm_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    // ── Role Helpers ───────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    // ── Attendance Helpers ─────────────────────────────

    /**
     * Get today's attendance record.
     */
    public function todayAttendance()
    {
        return $this->attendances()->where('date', today())->first();
    }

    /**
     * Check if user has clocked in today.
     */
    public function hasClockedInToday(): bool
    {
        return $this->attendances()
            ->where('date', today())
            ->whereNotNull('clock_in')
            ->exists();
    }

    /**
     * Check if user has clocked out today.
     */
    public function hasClockedOutToday(): bool
    {
        return $this->attendances()
            ->where('date', today())
            ->whereNotNull('clock_out')
            ->exists();
    }

    /**
     * Get attendance rate for a specific month (percentage).
     */
    public function attendanceRate(int $year, int $month): float
    {
        $workingDays = $this->getWorkingDaysInMonth($year, $month);
        if ($workingDays === 0) return 0;

        $attendedDays = $this->attendances()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereNotNull('clock_in')
            ->count();

        return round(($attendedDays / $workingDays) * 100, 1);
    }

    /**
     * Get working days in a month (Mon-Sat).
     */
    private function getWorkingDaysInMonth(int $year, int $month): int
    {
        $start = \Carbon\Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();
        $count = 0;

        while ($start->lte($end)) {
            if (!$start->isSunday()) {
                $count++;
            }
            $start->addDay();
        }

        return $count;
    }

    /**
     * Scope for active employees.
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for employees only.
     */
    public function scopeEmployees(Builder $query)
    {
        return $query->where('role', 'employee');
    }

    /**
     * Scope for admins only.
     */
    public function scopeAdmins(Builder $query)
    {
        return $query->where('role', 'admin');
    }
}
