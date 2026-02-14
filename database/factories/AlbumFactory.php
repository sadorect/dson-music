<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\ArtistProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlbumFactory extends Factory
{
    protected $model = Album::class;

    public function definition(): array
    {
        return [
            'artist_id' => ArtistProfile::factory(),
            'title' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'release_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'cover_art' => null,
            'genre' => fake()->randomElement(['Hip-Hop', 'R&B', 'Pop', 'Rock', 'Jazz', 'Electronic', 'Country', 'Classical']),
        ];
    }
}
