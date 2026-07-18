<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'clock_in_status',
        'clock_out_status',
        'shift_id',
        'qr_token_id',
        'notes',
        'ip_address',
        'user_agent',
        'latitude',
        'longitude',
        'location_valid',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'location_valid' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get working duration in minutes.
     */
    public function getWorkingMinutesAttribute(): ?int
    {
        if (!$this->clock_in || !$this->clock_out) {
            return null;
        }
        return $this->clock_in->diffInMinutes($this->clock_out);
    }

    /**
     * Get formatted working duration.
     */
    public function getWorkingDurationAttribute(): ?string
    {
        $minutes = $this->working_minutes;
        if ($minutes === null) return null;

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        return "{$hours}j {$mins}m";
    }

    /**
     * Get late minutes (0 if on time).
     */
    public function getLateMinutesAttribute(): int
    {
        if (!$this->clock_in || !$this->shift) return 0;

        $shiftStart = $this->date->copy()->setTimeFromTimeString($this->shift->getRawOriginal('start_time'));
        $diff = $this->clock_in->diffInMinutes($shiftStart, false);

        return $diff < 0 ? abs($diff) : 0;
    }

    /**
     * Scope for today's attendance.
     */
    public function scopeToday(Builder $query)
    {
        return $query->where('date', today());
    }

    /**
     * Scope for a specific month.
     */
    public function scopeInMonth(Builder $query, int $year, int $month)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    /**
     * Scope for date range.
     */
    public function scopeBetweenDates(Builder $query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}
