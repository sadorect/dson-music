<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'artist_profile_id',
        'track_id',
        'amount',
        'stripe_payment_intent_id',
        'type',
        'status',
        'message',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function artist(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ArtistProfile::class, 'artist_profile_id');
    }

    public function track(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUnlocks($query)
    {
        return $query->where('type', 'unlock');
    }

    public function scopeTips($query)
    {
        return $query->where('type', 'tip');
    }
}
