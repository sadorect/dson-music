<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $fillable = [
        'title',
        'artist_id',
        'cover_art',
        'release_date',
        'description',
        'type', // album, EP, single
        'status',
        'play_count',
    ];

    protected $casts = [
        'release_date' => 'datetime',
        'play_count' => 'integer',
    ];

    public function artist()
    {
        return $this->belongsTo(ArtistProfile::class, 'artist_id');
    }

    public function tracks()
    {
        return $this->hasMany(Track::class);
    }
}
