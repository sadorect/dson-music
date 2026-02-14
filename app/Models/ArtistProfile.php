<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class ArtistProfile extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'artist_name',
        'genre',
        'bio',
        'profile_image',
        'cover_image',
        'social_links',
        'is_verified',
        'verified_at',
        'custom_url',
        'completion_percentage',
    ];

    protected $casts = [
        'social_links' => 'array',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function albums()
    {
        return $this->hasMany(Album::class, 'artist_id');
    }

    public function tracks()
    {
        return $this->hasMany(Track::class, 'artist_id');

    }

    public function plays()
    {
        return $this->hasManyThrough(PlayHistory::class, Track::class, 'artist_id', 'track_id');
    }

    public function getAnalytics($days = 30)
    {
        return [
            'total_plays' => $this->plays()->count(),
            'unique_listeners' => $this->plays()->distinct('user_id')->count(),
            'plays_by_day' => $this->plays()
                ->selectRaw('DATE(created_at) as date')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->take($days)
                ->get(),
        ];
    }

    public function followers()
    {
        // return $this->belongsToMany(User::class, 'follows', 'artist_id', 'user_id');
        return $this->hasMany(Follow::class);
    }

    public function followersCount()
    {
        return $this->followers()->count();
    }

    public function getNameAttribute()
    {
        return $this->artist_name;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['artist_name'] = $value;
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'stage_name' => $this->stage_name ?? $this->artist_name,
            'artist_name' => $this->artist_name,
            'genre' => $this->genre,
            'bio' => $this->bio,
        ];
    }

    /**
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'artists_index';
    }
}
