<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class QrToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'scan_token',
        'encrypted_payload',
        'expires_at',
        'is_used',
        'used_by',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'is_used' => 'boolean',
        ];
    }

    public function usedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    /**
     * Check if the token is expired.
     */
    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }

    /**
     * Check if the token is valid (not used and not expired).
     */
    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired();
    }

    /**
     * Mark the token as used by a specific user.
     */
    public function markAsUsed(int $userId): void
    {
        $this->update([
            'is_used' => true,
            'used_by' => $userId,
            'used_at' => now(),
        ]);
    }

    /**
     * Scope for valid tokens.
     */
    public function scopeValid(Builder $query)
    {
        return $query->where('is_used', false)->where('expires_at', '>', now());
    }

    /**
     * Scope for expired tokens.
     */
    public function scopeExpired(Builder $query)
    {
        return $query->where('expires_at', '<=', now());
    }
}
