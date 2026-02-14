<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ArtistProfileSeeder extends Seeder
{
    public function run(): void
    {
        // Create verified artists
        for ($i = 0; $i < 5; $i++) {
            $user = \App\Models\User::inRandomOrder()->first();
            \App\Models\ArtistProfile::create([
                'user_id' => $user->id,
                'artist_name' => fake()->name(),
                'genre' => fake()->randomElement(['Hip Hop', 'R&B', 'Pop', 'Rock', 'Electronic']),
                'bio' => fake()->paragraph(),
                'profile_image' => null,
                'cover_image' => null,
                'location' => fake()->city().', '.fake()->state(),
                'website' => fake()->url(),
                'is_verified' => true,
            ]);
        }

        // Create additional non-verified artists
        for ($i = 0; $i < 5; $i++) {
            $user = \App\Models\User::inRandomOrder()->first();
            \App\Models\ArtistProfile::create([
                'user_id' => $user->id,
                'artist_name' => fake()->name(),
                'genre' => fake()->randomElement(['Hip Hop', 'R&B', 'Pop', 'Rock', 'Electronic']),
                'bio' => fake()->paragraph(),
                'profile_image' => null,
                'cover_image' => null,
                'location' => fake()->city().', '.fake()->state(),
                'website' => fake()->url(),
                'is_verified' => false,
            ]);
        }
    }
}
