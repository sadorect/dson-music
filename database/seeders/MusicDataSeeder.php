<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Track;
use App\Models\Album;
use App\Models\ArtistProfile;
use Illuminate\Support\Facades\Hash;

class MusicDataSeeder extends Seeder
{
    public function run()
    {
        $genres = ['Pop', 'Hip Hop', 'R&B', 'Rock', 'Jazz', 'Classical', 'Electronic', 'Afrobeats'];
        
        // Create 20 users and artists together
        foreach (range(1, 20) as $i) {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => Hash::make('password'),
            ]);

            $artist = ArtistProfile::create([
                'user_id' => $user->id,
                'artist_name' => fake()->unique()->name() . ' ' . fake()->randomElement(['Band', 'Orchestra', 'DJ', '']),
                'genre' => fake()->randomElement($genres),
                'bio' => fake()->paragraph(),
                'profile_image' => 'artist-profiles/artist' . $i . '.jpg',
                'cover_image' => 'artist-covers/cover' . $i . '.jpg',
                'is_verified' => fake()->boolean(70)
            ]);

            $artists[] = $artist;
        }

        // Create 30 albums
        foreach (range(1, 30) as $i) {
            $album = Album::create([
                'title' => fake()->randomElement(['The', 'A', 'My']) . ' ' . fake()->word() . ' ' . fake()->word(),
                'artist_id' => fake()->randomElement($artists)->id,
                'release_date' => fake()->dateTimeBetween('-2 years', 'now'),
                'genre' => fake()->randomElement($genres),
                'cover_art' => 'albums/album' . $i . '.jpg',
                'status' => fake()->randomElement(['published', 'draft']),
                'description' => fake()->paragraph()
            ]);
        }

        // Create 50 tracks
        foreach (range(1, 50) as $i) {
            Track::create([
                'title' => fake()->sentence(3),
                'artist_id' => fake()->randomElement($artists)->id,
                'album_id' => rand(0, 1) ? Album::inRandomOrder()->first()->id : null,
                'genre' => fake()->randomElement($genres),
                'duration' => fake()->numberBetween(180, 360),
                'file_path' => 'tracks/track' . $i . '.mp3',
                'cover_art' => 'covers/track' . $i . '.jpg',
                'release_date' => fake()->dateTimeBetween('-1 year', 'now'),
                'is_featured' => fake()->boolean(20),
                'play_count' => fake()->numberBetween(100, 1000000),
                'status' => fake()->randomElement(['published', 'draft', 'private']),
            ]);
        }
    }
}
