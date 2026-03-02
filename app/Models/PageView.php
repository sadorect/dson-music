<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageView extends Model
{
    protected $fillable = [
        'path',
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeLastDays($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days - 1)->startOfDay());
    }
}
