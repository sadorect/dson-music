<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    protected $fillable = ['user_id', 'artist_profile_id', 'follower_id'];

    public function setFollowerIdAttribute($value): void
    {
        $this->attributes['user_id'] = $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function artist()
    {
        return $this->belongsTo(ArtistProfile::class, 'artist_profile_id');
    }
}
