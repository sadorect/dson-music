<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\ArtistProfile;
use App\Models\Comment;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_like_track(): void
    {
        $user = User::factory()->create();
        $artist = User::factory()->create(['user_type' => 'artist']);
        $artistProfile = ArtistProfile::factory()->create(['user_id' => $artist->id]);
        $album = Album::factory()->create(['artist_id' => $artistProfile->id]);
        $track = Track::factory()->create([
            'artist_id' => $artistProfile->id,
            'album_id' => $album->id,
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($user)->post(route('tracks.like', $track));

        $response->assertStatus(302);
        $this->assertTrue($track->likes()->where('user_id', $user->id)->exists());
    }

    public function test_user_can_unlike_track(): void
    {
        $user = User::factory()->create();
        $artist = User::factory()->create(['user_type' => 'artist']);
        $artistProfile = ArtistProfile::factory()->create(['user_id' => $artist->id]);
        $album = Album::factory()->create(['artist_id' => $artistProfile->id]);
        $track = Track::factory()->create([
            'artist_id' => $artistProfile->id,
            'album_id' => $album->id,
            'approval_status' => 'approved',
        ]);
        
        $track->likes()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('tracks.like', $track));

        $response->assertStatus(302);
        $this->assertFalse($track->likes()->where('user_id', $user->id)->exists());
    }

    public function test_user_can_follow_artist(): void
    {
        $user = User::factory()->create();
        $artist = User::factory()->create(['user_type' => 'artist']);
        $artistProfile = ArtistProfile::factory()->create(['user_id' => $artist->id]);

        $response = $this->actingAs($user)->post(route('artists.follow', $artistProfile));

        $response->assertStatus(302);
        $this->assertTrue($artistProfile->followers()->where('user_id', $user->id)->exists());
    }

    public function test_user_can_unfollow_artist(): void
    {
        $user = User::factory()->create();
        $artist = User::factory()->create(['user_type' => 'artist']);
        $artistProfile = ArtistProfile::factory()->create(['user_id' => $artist->id]);
        
        $artistProfile->followers()->create(['follower_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('artists.follow', $artistProfile));

        $response->assertStatus(302);
        $this->assertFalse($artistProfile->followers()->where('user_id', $user->id)->exists());
    }

    public function test_user_can_comment_on_track(): void
    {
        $user = User::factory()->create();
        $artist = User::factory()->create(['user_type' => 'artist']);
        $artistProfile = ArtistProfile::factory()->create(['user_id' => $artist->id]);
        $album = Album::factory()->create(['artist_id' => $artistProfile->id]);
        $track = Track::factory()->create([
            'artist_id' => $artistProfile->id,
            'album_id' => $album->id,
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($user)->post(route('comments.store'), [
            'commentable_type' => Track::class,
            'commentable_id' => $track->id,
            'content' => 'Great track!',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('comments', [
            'commentable_id' => $track->id,
            'commentable_type' => Track::class,
            'content' => 'Great track!',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_delete_their_own_comment(): void
    {
        $user = User::factory()->create();
        $artist = User::factory()->create(['user_type' => 'artist']);
        $artistProfile = ArtistProfile::factory()->create(['user_id' => $artist->id]);
        $album = Album::factory()->create(['artist_id' => $artistProfile->id]);
        $track = Track::factory()->create([
            'artist_id' => $artistProfile->id,
            'album_id' => $album->id,
        ]);
        
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'commentable_id' => $track->id,
            'commentable_type' => Track::class,
        ]);

        $response = $this->actingAs($user)->delete(route('comments.destroy', $comment));

        $response->assertStatus(302);
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_user_cannot_delete_others_comment(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $artist = User::factory()->create(['user_type' => 'artist']);
        $artistProfile = ArtistProfile::factory()->create(['user_id' => $artist->id]);
        $album = Album::factory()->create(['artist_id' => $artistProfile->id]);
        $track = Track::factory()->create([
            'artist_id' => $artistProfile->id,
            'album_id' => $album->id,
        ]);
        
        $comment = Comment::factory()->create([
            'user_id' => $user1->id,
            'commentable_id' => $track->id,
            'commentable_type' => Track::class,
        ]);

        $response = $this->actingAs($user2)->delete(route('comments.destroy', $comment));

        $response->assertStatus(403); // Forbidden
    }

    public function test_track_owner_can_pin_comment(): void
    {
        $artist = User::factory()->create(['user_type' => 'artist']);
        $artistProfile = ArtistProfile::factory()->create(['user_id' => $artist->id]);
        $album = Album::factory()->create(['artist_id' => $artistProfile->id]);
        $track = Track::factory()->create([
            'artist_id' => $artistProfile->id,
            'album_id' => $album->id,
        ]);
        
        $commenter = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $commenter->id,
            'commentable_id' => $track->id,
            'commentable_type' => Track::class,
            'is_pinned' => false,
        ]);

        $response = $this->actingAs($artist)->post(route('comments.pin', $comment));

        $response->assertStatus(302);
        $this->assertTrue($comment->fresh()->is_pinned);
    }
}
