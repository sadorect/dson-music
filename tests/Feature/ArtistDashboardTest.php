<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\ArtistProfile;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtistDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_artist_dashboard_displays_for_authenticated_artist(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('artist.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Artist Dashboard');
        $response->assertSee($artist->is_verified ? '✓ Verified Artist' : 'Pending Verification');
    }

    public function test_artist_dashboard_shows_stats(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);
        
        // Create album and tracks for the artist
        $album = Album::factory()->create(['artist_id' => $artist->id]);
        Track::factory()->count(3)->create([
            'artist_id' => $artist->id,
            'album_id' => $album->id,
            'play_count' => 100,
        ]);

        $response = $this->actingAs($user)->get(route('artist.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Total Plays');
        $response->assertSee('Followers');
        $response->assertSee('Total Likes');
        $response->assertSee('Downloads');
    }

    public function test_non_artist_cannot_access_artist_dashboard(): void
    {
        $user = User::factory()->create(['user_type' => 'user']);

        $response = $this->actingAs($user)->get(route('artist.dashboard'));

        $response->assertStatus(302); // Redirect
    }

    public function test_guest_cannot_access_artist_dashboard(): void
    {
        $response = $this->get(route('artist.dashboard'));

        $response->assertStatus(302); // Redirect to login
    }

    public function test_artist_dashboard_displays_popular_tracks(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);
        $album = Album::factory()->create(['artist_id' => $artist->id]);
        
        $track = Track::factory()->create([
            'artist_id' => $artist->id,
            'album_id' => $album->id,
            'title' => 'Popular Track',
            'play_count' => 500,
        ]);

        $response = $this->actingAs($user)->get(route('artist.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Popular Tracks');
        $response->assertSee('Popular Track');
    }

    public function test_artist_dashboard_handles_artist_with_no_tracks(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('artist.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Artist Dashboard');
    }
}
