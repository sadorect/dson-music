<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\ArtistProfile;
use App\Models\Playlist;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaylistFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_playlists_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('playlists.index'));

        $response->assertStatus(200);
    }

    public function test_user_can_create_playlist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('playlists.store'), [
            'name' => 'My Playlist',
            'description' => 'Test playlist description',
            'is_public' => true,
        ]);

        $response->assertStatus(302); // Redirect after creation
        $this->assertDatabaseHas('playlists', [
            'name' => 'My Playlist',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_view_their_playlist(): void
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Playlist',
        ]);

        $response = $this->actingAs($user)->get(route('playlists.show', $playlist));

        $response->assertStatus(200);
        $response->assertSee('Test Playlist');
    }

    public function test_user_can_view_public_playlist(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        
        $playlist = Playlist::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Public Playlist',
            'is_public' => true,
        ]);

        $response = $this->actingAs($viewer)->get(route('playlists.show', $playlist));

        $response->assertStatus(200);
        $response->assertSee('Public Playlist');
    }

    public function test_user_cannot_view_private_playlist_of_others(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        
        $playlist = Playlist::factory()->create([
            'user_id' => $owner->id,
            'is_public' => false,
        ]);

        $response = $this->actingAs($viewer)->get(route('playlists.show', $playlist));

        $response->assertStatus(403); // Forbidden
    }

    public function test_user_can_add_track_to_playlist(): void
    {
        $user = User::factory()->create();
        $artist = User::factory()->create(['user_type' => 'artist']);
        $artistProfile = ArtistProfile::factory()->create(['user_id' => $artist->id]);
        $album = Album::factory()->create(['artist_id' => $artistProfile->id]);
        
        $playlist = Playlist::factory()->create(['user_id' => $user->id]);
        $track = Track::factory()->create([
            'artist_id' => $artistProfile->id,
            'album_id' => $album->id,
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($user)->post(route('playlists.tracks.add', $playlist), [
            'track_id' => $track->id,
        ]);

        $response->assertStatus(302);
        $this->assertTrue($playlist->tracks->contains($track));
    }

    public function test_user_can_remove_track_from_playlist(): void
    {
        $user = User::factory()->create();
        $artist = User::factory()->create(['user_type' => 'artist']);
        $artistProfile = ArtistProfile::factory()->create(['user_id' => $artist->id]);
        $album = Album::factory()->create(['artist_id' => $artistProfile->id]);
        
        $playlist = Playlist::factory()->create(['user_id' => $user->id]);
        $track = Track::factory()->create([
            'artist_id' => $artistProfile->id,
            'album_id' => $album->id,
            'approval_status' => 'approved',
        ]);
        
        $playlist->tracks()->attach($track);

        $response = $this->actingAs($user)->delete(route('playlists.tracks.remove', [$playlist, $track]));

        $response->assertStatus(302);
        $this->assertFalse($playlist->fresh()->tracks->contains($track));
    }

    public function test_user_can_delete_their_own_playlist(): void
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('playlists.destroy', $playlist));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('playlists', ['id' => $playlist->id]);
    }

    public function test_user_cannot_delete_others_playlist(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $playlist = Playlist::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->delete(route('playlists.destroy', $playlist));

        $response->assertStatus(403); // Forbidden
        $this->assertDatabaseHas('playlists', ['id' => $playlist->id]);
    }
}
