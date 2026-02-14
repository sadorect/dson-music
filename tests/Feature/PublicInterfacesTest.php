<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\ArtistProfile;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicInterfacesTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_tracks_index_displays_approved_tracks(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);
        $album = Album::factory()->create(['artist_id' => $artist->id]);
        
        $approvedTrack = Track::factory()->create([
            'artist_id' => $artist->id,
            'album_id' => $album->id,
            'title' => 'Approved Track',
            'approval_status' => 'approved',
        ]);

        $pendingTrack = Track::factory()->create([
            'artist_id' => $artist->id,
            'album_id' => $album->id,
            'title' => 'Pending Track',
            'approval_status' => 'pending',
        ]);

        $response = $this->get(route('tracks.index'));

        $response->assertStatus(200);
        $response->assertSee('Approved Track');
        $response->assertDontSee('Pending Track');
    }

    public function test_track_show_page_displays_track_details(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);
        $album = Album::factory()->create(['artist_id' => $artist->id]);
        $track = Track::factory()->create([
            'artist_id' => $artist->id,
            'album_id' => $album->id,
            'title' => 'Test Track',
            'approval_status' => 'approved',
        ]);

        $response = $this->get(route('tracks.show', $track));

        $response->assertStatus(200);
        $response->assertSee('Test Track');
        $response->assertSee($artist->name);
    }

    public function test_artists_index_displays_artists(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Artist',
        ]);

        $response = $this->get(route('artists.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Artist');
    }

    public function test_artist_profile_page_displays_artist_details(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Artist',
        ]);

        $response = $this->get(route('artists.show', $artist));

        $response->assertStatus(200);
        $response->assertSee('Test Artist');
    }

    public function test_artist_profile_shows_their_tracks(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);
        $album = Album::factory()->create(['artist_id' => $artist->id]);
        $track = Track::factory()->create([
            'artist_id' => $artist->id,
            'album_id' => $album->id,
            'title' => 'Artist Track',
            'approval_status' => 'approved',
        ]);

        $response = $this->get(route('artists.show', $artist));

        $response->assertStatus(200);
        $response->assertSee('Artist Track');
    }

    public function test_trending_page_displays(): void
    {
        $response = $this->get(route('trending'));

        $response->assertStatus(200);
    }

    public function test_search_functionality_works(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);
        $album = Album::factory()->create(['artist_id' => $artist->id]);
        $track = Track::factory()->create([
            'artist_id' => $artist->id,
            'album_id' => $album->id,
            'title' => 'Unique Search Track',
            'approval_status' => 'approved',
        ]);

        $response = $this->get(route('search', ['q' => 'Unique']));

        $response->assertStatus(200);
    }
}
