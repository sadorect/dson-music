<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\ArtistProfile;
use Illuminate\Database\Seeder;

class AlbumSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['album', 'EP', 'single'];
        $statuses = ['draft', 'published', 'private'];
        $genres = ['Hip Hop', 'R&B', 'Pop', 'Rock', 'Electronic'];

        // Create albums for verified artists
        $verifiedArtists = ArtistProfile::where('is_verified', true)->get();

        foreach ($verifiedArtists as $artist) {
            // Create 1-3 albums per artist
            $albumCount = rand(1, 3);

            for ($i = 0; $i < $albumCount; $i++) {
                Album::create([
                    'artist_id' => $artist->id,
                    'title' => fake()->words(2, true),
                    'cover_art' => 'default_album_cover.jpg',
                    'release_date' => now()->subMonths(rand(1, 12)),
                    'description' => fake()->paragraph(),
                    'type' => $types[array_rand($types)],
                    'status' => $statuses[array_rand($statuses)],
                    'play_count' => rand(100, 10000),
                    'genre' => $genres[array_rand($genres)],
                ]);
            }
        }
    }
}
