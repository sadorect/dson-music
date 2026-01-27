<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TrackSeeder extends Seeder
{
    public function run(): void
    {
        $genres = ['Hip Hop', 'R&B', 'Pop', 'Rock', 'Electronic'];
        $statuses = ['draft', 'published', 'private'];

        // Create trending tracks
        for ($i = 0; $i < 8; $i++) {
            $artist = \App\Models\ArtistProfile::inRandomOrder()->first();
            \App\Models\Track::create([
                'artist_id' => $artist->id,
                'title' => fake()->words(2, true),
                'genre' => $genres[array_rand($genres)],
                'duration' => rand(180, 300), // 3-5 minutes in seconds
                'file_path' => 'default_track.mp3',
                'cover_art' => 'default_track_cover.jpg',
                'release_date' => now()->subDays(rand(1, 30)),
                'is_featured' => true,
                'play_count' => rand(1000, 10000),
                'status' => $statuses[array_rand($statuses)],
            ]);
        }

        // Create new releases
        for ($i = 0; $i < 8; $i++) {
            $artist = \App\Models\ArtistProfile::inRandomOrder()->first();
            \App\Models\Track::create([
                'artist_id' => $artist->id,
                'title' => fake()->words(2, true),
                'genre' => $genres[array_rand($genres)],
                'duration' => rand(180, 300), // 3-5 minutes in seconds
                'file_path' => 'default_track.mp3',
                'cover_art' => 'default_track_cover.jpg',
                'release_date' => now(),
                'is_featured' => false,
                'play_count' => rand(100, 500),
                'status' => $statuses[array_rand($statuses)],
            ]);
        }
    }
}
