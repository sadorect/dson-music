<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Playlist extends Model
{
    use HasFactory;

    protected static ?bool $hasSlugColumn = null;

    protected $fillable = [
        'name',
        'slug',
        'user_id',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Playlist $playlist) {
            if (! static::usesSlugColumn()) {
                return;
            }

            if (blank($playlist->slug)) {
                $playlist->slug = static::generateUniqueSlug($playlist->name);
            }
        });

        static::updating(function (Playlist $playlist) {
            if (! static::usesSlugColumn()) {
                return;
            }

            if ($playlist->isDirty('name') && blank($playlist->slug)) {
                $playlist->slug = static::generateUniqueSlug($playlist->name, $playlist->id);
            }
        });
    }

    protected static function usesSlugColumn(): bool
    {
        if (static::$hasSlugColumn === null) {
            static::$hasSlugColumn = Schema::hasColumn('playlists', 'slug');
        }

        return static::$hasSlugColumn;
    }

    protected static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'playlist';
        $slug = $base;
        $counter = 2;

        while (true) {
            $query = static::query()->where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }

            if (! $query->exists()) {
                return $slug;
            }

            $slug = "{$base}-{$counter}";
            $counter++;
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tracks()
    {
        return $this->belongsToMany(Track::class, 'playlist_track');
    }
}
