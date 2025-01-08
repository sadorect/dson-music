<?php

namespace App\Models;

use App\Models\User;
use App\Models\ArtistProfile;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    protected $fillable = ['user_id', 'artist_profile_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function artist()
    {
        return $this->belongsTo(ArtistProfile::class, 'artist_profile_id');
    }
}
