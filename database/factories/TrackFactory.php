<?php

namespace Database\Factories;

use App\Models\Track;
use App\Models\ArtistProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Track>
 */
class TrackFactory extends Factory
{
    protected $model = Track::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'artist_id' => ArtistProfile::factory(),
            'album_id' => null,
            'genre' => $this->faker->randomElement(['Afrobeat', 'Pop', 'R&B', 'Hip Hop']),
            'duration' => $this->faker->numberBetween(120, 360),
            'gradient_start_color' => null,
            'gradient_end_color' => null,
            'file_path' => 'tracks/'.$this->faker->uuid.'.mp3',
            'cover_art' => null,
            'release_date' => now(),
            'is_featured' => false,
            'play_count' => 0,
            'status' => 'published',
            'approval_status' => 'approved',
            'rejection_reason' => null,
            'download_type' => 'free',
            'minimum_donation' => null,
            'approved_at' => now(),
            'approved_by' => null,
            'downloads_count' => 0,
        ];
    }
}
