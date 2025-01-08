<?php

namespace App\Models;

use App\Models\User;
use App\Models\Album;
use App\Models\Track;
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



}