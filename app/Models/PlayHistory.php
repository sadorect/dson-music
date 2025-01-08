<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayHistory extends Model
{
    protected $fillable = [
        'user_id',
        'track_id',
        'played_at',
        'ip_address',
        'user_agent',
        'location'
    ];

    protected $casts = [
        'played_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function track()
    {
        return $this->belongsTo(Track::class);
    }
}
