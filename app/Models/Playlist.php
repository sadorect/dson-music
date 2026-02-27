<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Str;

class Playlist extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_public',
        'slug',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Playlist $playlist) {
            if (empty($playlist->slug)) {
                $base = Str::slug($playlist->name);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }
                $playlist->slug = $slug;
            }
        });

        static::updating(function (Playlist $playlist) {
            if ($playlist->isDirty('name') && empty($playlist->slug)) {
                $playlist->slug = Str::slug($playlist->name);
            }
        });
    }


    // ─── Media Collections ────────────────────────────────────────────────────
    public function registerMediaCollections(): void
    {
        $disk = config('media-library.disk_name', env('MEDIA_DISK', env('FILESYSTEM_DISK', 'public')));

        $this->addMediaCollection('cover')
            ->useDisk($disk)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    // ─── Relationships ─────────────────────────────────────────────────────────
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tracks(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'playlist_track')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function getCoverUrl(): string
    {
        return $this->getFirstMediaUrl('cover') ?: '';
    }

    public function setSlug(): string
    {
       return $this->slug = Str::slug($this->name);
        
    }
}
