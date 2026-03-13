<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Mood extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'related_genres',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'related_genres' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public static function defaultDefinitions(): array
    {
        return [
            'late-night' => ['name' => 'Late Night', 'related_genres' => ['r-b', 'electronic', 'jazz', 'indie'], 'keywords' => ['late night', 'night', 'moody', 'smooth']],
            'chill' => ['name' => 'Chill', 'related_genres' => ['r-b', 'indie', 'jazz', 'electronic'], 'keywords' => ['chill', 'calm', 'laid back', 'easy']],
            'focus' => ['name' => 'Focus', 'related_genres' => ['electronic', 'indie', 'jazz'], 'keywords' => ['focus', 'ambient', 'study', 'deep']],
            'hype' => ['name' => 'Hype', 'related_genres' => ['hip-hop', 'afrobeats', 'electronic', 'rock'], 'keywords' => ['hype', 'energetic', 'party', 'workout']],
            'romance' => ['name' => 'Romance', 'related_genres' => ['r-b', 'pop', 'afrobeats'], 'keywords' => ['romantic', 'love', 'warm', 'intimate']],
        ];
    }

    public static function supportsCatalog(): bool
    {
        return Schema::hasTable('moods');
    }

    public static function optionMap(): array
    {
        return collect(static::definitions())
            ->mapWithKeys(fn (array $definition, string $slug) => [$slug => $definition['name']])
            ->all();
    }

    public static function definitions(): array
    {
        if (static::supportsCatalog()) {
            $definitions = static::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['name', 'slug', 'related_genres'])
                ->mapWithKeys(function (Mood $mood): array {
                    return [
                        $mood->slug => [
                            'name' => $mood->name,
                            'related_genres' => $mood->related_genres ?? [],
                            'keywords' => [
                                Str::of($mood->slug)->replace('-', ' ')->replace('_', ' ')->lower()->value(),
                                Str::of($mood->name)->lower()->value(),
                            ],
                        ],
                    ];
                })
                ->all();

            if (! empty($definitions)) {
                return $definitions;
            }
        }

        return static::defaultDefinitions();
    }

    public static function suggestedSlugForGenre(?string $genreSlug): ?string
    {
        if (! $genreSlug) {
            return null;
        }

        if (static::supportsCatalog()) {
            $mood = static::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['slug', 'related_genres'])
                ->first(function (Mood $mood) use ($genreSlug): bool {
                    return in_array($genreSlug, $mood->related_genres ?? [], true);
                });

            if ($mood) {
                return $mood->slug;
            }
        }

        foreach (static::defaultDefinitions() as $slug => $definition) {
            if (in_array($genreSlug, $definition['related_genres'], true)) {
                return $slug;
            }
        }

        return null;
    }

    protected static function booted(): void
    {
        static::saving(function (Mood $mood): void {
            if (blank($mood->slug)) {
                $mood->slug = Str::slug($mood->name);
            }
        });
    }
}
