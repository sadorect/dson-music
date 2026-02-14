<?php

namespace Tests\Feature;

use App\Models\ArtistProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiUploadThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_track_creation_is_rate_limited_after_ten_requests_per_hour(): void
    {
        $artist = User::factory()->create([
            'user_type' => 'artist',
        ]);

        ArtistProfile::factory()->create([
            'user_id' => $artist->id,
        ]);

        Sanctum::actingAs($artist);

        $payload = [
            'title' => 'Rate Limit Test Track',
            'genre' => 'hip-hop',
        ];

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->postJson('/api/tracks', $payload)
                ->assertStatus(422);
        }

        $this->postJson('/api/tracks', $payload)
            ->assertStatus(429)
            ->assertJson([
                'message' => 'Upload limit exceeded. Please try again later.',
                'retry_after' => 3600,
            ]);
    }
}
