<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Track extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Searchable, SoftDeletes;

    protected $fillable = [
        'user_id',
        'artist_profile_id',
        'album_id',
        'genre_id',
        'title',
        'slug',
        'description',
        'duration',
        'audio_file_path',
        'waveform_data',
        'is_published',
        'is_featured',
        'is_free',
        'donation_amount',
        'play_count',
        'downloads_count',
        'track_number',
    ];

    protected $casts = [
        'is_published'    => 'boolean',
        'is_featured'     => 'boolean',
        'is_free'         => 'boolean',
        'donation_amount' => 'decimal:2',
        'play_count'      => 'integer',
        'downloads_count' => 'integer',
        'track_number'    => 'integer',
        'duration'        => 'integer',
        'waveform_data'   => 'array',
    ];

    /**
     * Backward-compatible accessor: requires_donation === !is_free
     */
    public function getRequiresDonationAttribute(): bool
    {
        return !($this->is_free ?? true);
    }

    public function setRequiresDonationAttribute(bool $value): void
    {
        $this->attributes['is_free'] = !$value;
    }

    /**
     * Backward-compatible accessor: is_demo === is_featured
     */
    public function getIsDemoAttribute(): bool
    {
        return (bool) ($this->is_featured ?? false);
    }

    public function setIsDemoAttribute(bool $value): void
    {
        $this->attributes['is_featured'] = $value;
    }

    protected static function booted(): void
    {
        static::creating(function (Track $track) {
            if (empty($track->slug)) {
                $track->slug = Str::slug($track->title);
            }
        });
    }

    // ─── Media Collections ────────────────────────────────────────────────────
    public function registerMediaCollections(): void
    {
        $disk = config('media-library.disk_name', env('MEDIA_DISK', env('FILESYSTEM_DISK', 'public')));

        $this->addMediaCollection('audio')
            ->useDisk($disk)
            ->singleFile()
            ->acceptsMimeTypes(['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/flac', 'audio/ogg']);

        $this->addMediaCollection('cover')
            ->useDisk($disk)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->withResponsiveImages();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->format('webp')
            ->performOnCollections('cover');
    }

    // ─── Relationships ─────────────────────────────────────────────────────────
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function artistProfile(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ArtistProfile::class, 'artist_profile_id');
    }

    /** @deprecated Use artistProfile() */
    public function artist(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->artistProfile();
    }

    public function album(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function genre(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function playlists(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Playlist::class, 'playlist_track')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function likedByUsers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'likes')
            ->withTimestamps();
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id')->orderBy('created_at', 'desc');
    }

    public function allComments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function playHistories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PlayHistory::class);
    }

    public function donations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Donation::class);
    }

    // ─── Scout ────────────────────────────────────────────────────────────────
    public function toSearchableArray(): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
        ];
    }

    // ─── Accessors ────────────────────────────────────────────────────────────
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration) {
            return '0:00';
        }
        $minutes = intdiv($this->duration, 60);
        $seconds = $this->duration % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getCoverUrl(string $conversion = 'thumb'): string
    {
        return $this->getFirstMediaUrl('cover', $conversion)
            ?: ($this->album?->getCoverUrl($conversion) ?? '');
    }

    public function getAudioUrl(): string
    {
        // First try Spatie Media Library
        $url = $this->getFirstMediaUrl('audio');
        if ($url) {
            return $url;
        }

        // Fall back to direct file path stored in audio_file_path column
        if ($this->audio_file_path) {
            return \Illuminate\Support\Facades\Storage::url($this->audio_file_path);
        }

        return '';
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeDemo($query)
    {
        return $query->where('is_featured', true);
    }

    public function incrementPlayCount(): void
    {
        $this->increment('play_count');
        $this->artist?->increment('total_plays');
    }
}
