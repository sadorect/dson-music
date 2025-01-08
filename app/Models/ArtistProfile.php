<?php

namespace App\Models;

use App\Models\User;
use App\Models\Album;
use App\Models\Track;
use App\Models\Follow;
use App\Models\PlayHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ArtistProfile extends Model
{
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
        'completion_percentage'
    ];

    protected $casts = [
        'social_links' => 'array',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime'
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
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take($days)
            ->get()
    ];
}

public function followers()
{
    return $this->hasMany(Follow::class);
}

public function followersCount()
{
    return $this->followers()->count();
}

}