<?php

namespace App\Models;

use App\Models\PlayHistory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $fillable = [
        'title',
        'artist_id',
        'album_id',
        'genre',
        'duration',
        'file_path',
        'cover_art',
        'release_date',
        'is_featured',
        'play_count',
        'status'
    ];

    protected $casts = [
        'release_date' => 'datetime',
        'is_featured' => 'boolean',
        'play_count' => 'integer'
    ];

    public function artist()
    {
        return $this->belongsTo(ArtistProfile::class, 'artist_id');
    }

    public function album()
    {
        return $this->belongsTo(Album::class);
    }


    public function scopeTrending($query)
    {
        return $query->where('status', 'published')
            ->orderBy('play_count', 'desc')
            ->take(10);
    }

    public function incrementPlayCount()
    {
        $this->increment('play_count');
    }
    
    public function plays()
{
    return $this->hasMany(PlayHistory::class);
}

}