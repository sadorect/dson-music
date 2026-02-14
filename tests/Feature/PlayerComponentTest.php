<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\ArtistProfile;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_component_is_present_on_track_page(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);
        $album = Album::factory()->create(['artist_id' => $artist->id]);
        $track = Track::factory()->create([
            'artist_id' => $artist->id,
            'album_id' => $album->id,
            'approval_status' => 'approved',
        ]);

        $response = $this->get(route('tracks.show', $track));

        $response->assertStatus(200);
        $response->assertSee('dson-player');
    }

    public function test_player_displays_track_information(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);
        $album = Album::factory()->create(['artist_id' => $artist->id]);
        $track = Track::factory()->create([
            'artist_id' => $artist->id,
            'album_id' => $album->id,
            'title' => 'Test Track Title',
            'approval_status' => 'approved',
        ]);

        $response = $this->get(route('tracks.show', $track));

        $response->assertStatus(200);
        $response->assertSee('Test Track Title');
    }

    public function test_player_has_play_pause_controls(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);
        $album = Album::factory()->create(['artist_id' => $artist->id]);
        $track = Track::factory()->create([
            'artist_id' => $artist->id,
            'album_id' => $album->id,
            'approval_status' => 'approved',
        ]);

        $response = $this->get(route('tracks.show', $track));

        $response->assertStatus(200);
        $response->assertSee('togglePlay', false); // Alpine.js function
    }

    public function test_player_has_volume_controls(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);
        $album = Album::factory()->create(['artist_id' => $artist->id]);
        $track = Track::factory()->create([
            'artist_id' => $artist->id,
            'album_id' => $album->id,
            'approval_status' => 'approved',
        ]);

        $response = $this->get(route('tracks.show', $track));

        $response->assertStatus(200);
        $response->assertSee('toggleMute', false);
    }

    public function test_player_has_shuffle_and_repeat_controls(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);
        $album = Album::factory()->create(['artist_id' => $artist->id]);
        $track = Track::factory()->create([
            'artist_id' => $artist->id,
            'album_id' => $album->id,
            'approval_status' => 'approved',
        ]);

        $response = $this->get(route('tracks.show', $track));

        $response->assertStatus(200);
        $response->assertSee('toggleShuffle', false);
        $response->assertSee('toggleRepeat', false);
    }

    public function test_player_has_next_previous_controls(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);
        $album = Album::factory()->create(['artist_id' => $artist->id]);
        $track = Track::factory()->create([
            'artist_id' => $artist->id,
            'album_id' => $album->id,
            'approval_status' => 'approved',
        ]);

        $response = $this->get(route('tracks.show', $track));

        $response->assertStatus(200);
        $response->assertSee('nextTrack', false);
        $response->assertSee('previousTrack', false);
    }
}
