<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'is_super_admin',
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
            'is_super_admin' => 'boolean',
        ];
    }

    public function userType()
    {
        return $this->user_type;
    }

    public function isArtist()
    {
        return $this->user_type === 'artist';
    }

    /**
     * Check if the user is an admin (any type)
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }

    /**
     * Check if the user is a super admin
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->user_type === 'admin' && $this->is_super_admin;
    }

    /**
     * Check if the user has a specific admin permission
     *
     * @param  string  $permission
     * @return bool
     */
    public function hasAdminPermission($permission)
    {
        // Super admins have all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Regular admins need to check their permissions
        if ($this->isAdmin() && $this->admin_permissions) {
            return in_array($permission, $this->admin_permissions);
        }

        return false;
    }

    /**
     * Get the admin permissions as an array
     *
     * @return array
     */
    public function getAdminPermissionsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Set the admin permissions
     *
     * @param  array  $value
     * @return void
     */
    public function setAdminPermissionsAttribute($value)
    {
        $this->attributes['admin_permissions'] = $value ? json_encode($value) : null;
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

    public function follows()
    {
        return $this->hasMany(Follow::class);
    }

    public function following()
    {
        return $this->belongsToMany(ArtistProfile::class, 'follows', 'user_id', 'artist_profile_id');
    }

    // Add this to your existing User model

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function followers()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    /*public function notifications()
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
    }*/

}
