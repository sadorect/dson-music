<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ArtistProfile extends Model
{
    protected $fillable = [
        'artist_name',
        'bio',
        'profile_image',
        'cover_image',
        'genre',
        'location',
        'website'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
