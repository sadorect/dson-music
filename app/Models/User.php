<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Playlist;
use App\Models\PlayHistory;
use App\Models\ArtistProfile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isArtist()
    { 
        return $this->user_type === 'artist';
    }

    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }

    public function artistProfile()
    {
        return $this->hasOne(ArtistProfile::class);
    }

    public function tracks()
    {
        return $this->hasManyThrough(
            Track::class,
            ArtistProfile::class,
            'user_id',
            'artist_id'
        );
    }

    public function playlists()
    {
        return $this->hasMany(Playlist::class);
    }

    public function plays()
    {
        return $this->hasMany(PlayHistory::class);
    }

    public function isFollowing(ArtistProfile $artist)
    {
        return $this->follows()->where('artist_profile_id', $artist->id)->exists();
    }

/*
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function follows()
    {
        return $this->hasMany(Follow::class);
    }

    public function followers()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function following()
    {
        return $this->hasMany(Follow::class, 'following_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
*/


}

