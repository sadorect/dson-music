<?php

namespace Database\Factories;

use App\Models\ArtistProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArtistProfileFactory extends Factory
{
    protected $model = ArtistProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'artist_name' => fake()->name(),
            'genre' => fake()->randomElement(['Hip Hop', 'R&B', 'Pop', 'Rock', 'Electronic']),
            'bio' => fake()->paragraph(),
            'profile_image' => null,
            'cover_image' => null,
            'social_links' => [
                'facebook' => 'https://facebook.com/'.fake()->userName(),
                'twitter' => 'https://twitter.com/'.fake()->userName(),
                'instagram' => 'https://instagram.com/'.fake()->userName(),
            ],
            'is_verified' => fake()->boolean(50),
            'verified_at' => fake()->boolean(50) ? now() : null,
            'custom_url' => fake()->slug(),
            'completion_percentage' => fake()->numberBetween(50, 100),
        ];
    }
}
