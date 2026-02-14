<?php

namespace App\Models;

use App\Traits\HasComments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Track extends Model
{
    use HasComments, HasFactory, Searchable;

    protected $fillable = [
        'title',
        'artist_id',
        'album_id',
        'genre',
        'duration',
        'gradient_start_color',
        'gradient_end_color',
        'file_path',
        'cover_art',
        'release_date',
        'is_featured',
        'play_count',
        'status',
        'approval_status',
        'rejection_reason',
        'download_type',
        'minimum_donation',
        'approved_at',
        'approved_by',
        'downloads_count',
    ];

    protected $casts = [
        'release_date' => 'datetime',
        'is_featured' => 'boolean',
        'play_count' => 'integer',
        'downloads_count' => 'integer',
        'approved_at' => 'datetime',
        'minimum_donation' => 'decimal:2',
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

    public function incrementDownloadCount()
    {
        $this->increment('downloads_count');
    }

    public function plays()
    {
        return $this->hasMany(PlayHistory::class);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function likesCount()
    {
        return $this->likes()->count();
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    public function downloadsCount()
    {
        return $this->downloads()->count();
    }

    // New status checking methods
    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }

    public function isPending()
    {
        return $this->approval_status === 'pending';
    }

    public function isRejected()
    {
        return $this->approval_status === 'rejected';
    }

    public function requiresDonation()
    {
        return $this->download_type === 'donate';
    }

    // Admin relationship
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
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
            'title' => $this->title,
            'genre' => $this->genre,
            'artist_name' => $this->artist ? $this->artist->stage_name : null,
            'album_title' => $this->album ? $this->album->title : null,
        ];
    }

    /**
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'tracks_index';
    }
}
