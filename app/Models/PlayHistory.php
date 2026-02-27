<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'track_id',
        'ip_address',
        'seconds_played',
        'source',
    ];

    protected $casts = [
        'seconds_played' => 'integer',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function track(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}
