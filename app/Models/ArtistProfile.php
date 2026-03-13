<?php

namespace App\Models;

use App\Support\UploadLimits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ArtistProfile extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Searchable;

    protected static ?bool $supportsFeaturedCurationCache = null;

    protected $fillable = [
        'user_id',
        'stage_name',
        'slug',
        'bio',
        'website',
        'twitter',
        'instagram',
        'youtube',
        'spotify',
        'is_verified',
        'is_approved',
        'is_active',
        'is_featured',
        'total_plays',
        'followers_count',
        'total_donations',
    ];

    protected $casts = [
        'is_verified'   => 'boolean',
        'is_approved'   => 'boolean',
        'is_active'     => 'boolean',
        'is_featured'   => 'boolean',
        'total_plays'   => 'integer',
        'followers_count' => 'integer',
        'total_donations' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (ArtistProfile $profile) {
            if (empty($profile->slug)) {
                $profile->slug = Str::slug($profile->stage_name);
            }
        });
    }

    // ─── Media Collections ────────────────────────────────────────────────────
    public function registerMediaCollections(): void
    {
        $disk = config('media-library.disk_name', env('MEDIA_DISK', env('FILESYSTEM_DISK', 'public')));

        $this->addMediaCollection('avatar')
            ->useDisk($disk)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->acceptsFile(fn (File $file) => $file->size <= UploadLimits::imageBytes())
            ->withResponsiveImages();

        $this->addMediaCollection('banner')
            ->useDisk($disk)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->sharpen(10)
            ->format('webp')
            ->performOnCollections('avatar');

        $this->addMediaConversion('preview')
            ->width(600)
            ->height(400)
            ->format('webp')
            ->performOnCollections('banner');
    }

    // ─── Relationships ─────────────────────────────────────────────────────────
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function genres(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'artist_genre');
    }

    public function tracks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Track::class);
    }

    public function albums(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Album::class);
    }

    public function followers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Follow::class);
    }

    public function donations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Donation::class);
    }

    // ─── Scout ────────────────────────────────────────────────────────────────
    public function toSearchableArray(): array
    {
        return [
            'id'         => $this->id,
            'stage_name' => $this->stage_name,
            'bio'        => $this->bio,
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────
    public function getAvatarUrl(): string
    {
        return $this->getFirstMediaUrl('avatar', 'thumb')
            ?: 'https://ui-avatars.com/api/?name=' . urlencode($this->stage_name) . '&background=ef4444&color=fff&size=200';
    }

    public function getBannerUrl(): string
    {
        return $this->getFirstMediaUrl('banner', 'preview')
            ?: '';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->stage_name ?: ($this->user?->name ?? 'Artist');
    }

    public function getAvatarAltAttribute(): string
    {
        return "{$this->display_name} profile image";
    }

    public function getBannerAltAttribute(): string
    {
        return "{$this->display_name} artist banner";
    }

    public static function supportsFeaturedCuration(): bool
    {
        if (static::$supportsFeaturedCurationCache !== null) {
            return static::$supportsFeaturedCurationCache;
        }

        return static::$supportsFeaturedCurationCache = Schema::hasColumn('artist_profiles', 'is_featured');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true)->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeFeatured($query)
    {
        if (!static::supportsFeaturedCuration()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('is_featured', true);
    }
}
