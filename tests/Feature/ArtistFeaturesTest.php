<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\ArtistProfile;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtistFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_artist_can_view_their_albums(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);
        $album = Album::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'My Album',
        ]);

        $response = $this->actingAs($user)->get(route('artist.albums.index'));

        $response->assertStatus(200);
        $response->assertSee('My Album');
    }

    public function test_artist_can_create_album_page(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('artist.albums.create'));

        $response->assertStatus(200);
    }

    public function test_artist_can_view_their_tracks(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);
        $album = Album::factory()->create(['artist_id' => $artist->id]);
        $track = Track::factory()->create([
            'artist_id' => $artist->id,
            'album_id' => $album->id,
            'title' => 'My Track',
        ]);

        $response = $this->actingAs($user)->get(route('artist.tracks.index'));

        $response->assertStatus(200);
        $response->assertSee('My Track');
    }

    public function test_artist_can_view_track_upload_page(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('artist.tracks.create'));

        $response->assertStatus(200);
    }

    public function test_artist_can_edit_their_track(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);
        $album = Album::factory()->create(['artist_id' => $artist->id]);
        $track = Track::factory()->create([
            'artist_id' => $artist->id,
            'album_id' => $album->id,
        ]);

        $response = $this->actingAs($user)->get(route('artist.tracks.edit', $track));

        $response->assertStatus(200);
    }

    public function test_artist_cannot_edit_other_artists_track(): void
    {
        $user1 = User::factory()->create(['user_type' => 'artist']);
        $artist1 = ArtistProfile::factory()->create(['user_id' => $user1->id]);
        
        $user2 = User::factory()->create(['user_type' => 'artist']);
        $artist2 = ArtistProfile::factory()->create(['user_id' => $user2->id]);
        
        $album = Album::factory()->create(['artist_id' => $artist2->id]);
        $track = Track::factory()->create([
            'artist_id' => $artist2->id,
            'album_id' => $album->id,
        ]);

        $response = $this->actingAs($user1)->get(route('artist.tracks.edit', $track));

        $response->assertStatus(403); // Forbidden
    }

    public function test_artist_profile_edit_page_accessible(): void
    {
        $user = User::factory()->create(['user_type' => 'artist']);
        $artist = ArtistProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('artist.profile.edit'));

        $response->assertStatus(200);
    }

    public function test_non_artist_cannot_access_artist_features(): void
    {
        $user = User::factory()->create(['user_type' => 'user']);

        $response = $this->actingAs($user)->get(route('artist.tracks.index'));

        $response->assertStatus(302); // Redirect
    }
}
