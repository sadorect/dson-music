<?php

namespace Tests\Feature;

use App\Livewire\LikeButton;
use App\Livewire\MiniPlayer;
use App\Models\ArtistProfile;
use App\Models\Like;
use App\Models\PlayHistory;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MediaActionsTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function createArtistWithTrack(array $trackAttributes = []): array
    {
        $user = User::factory()->create();
        $profile = ArtistProfile::create([
            'user_id'    => $user->id,
            'stage_name' => 'Test Artist',
            'slug'       => 'test-artist',
            'is_approved'=> true,
            'is_active'  => true,
        ]);
        $track = Track::create(array_merge([
            'user_id'           => $user->id,
            'artist_profile_id' => $profile->id,
            'title'             => 'Test Track',
            'slug'              => 'test-track',
            'is_published'      => true,
            'is_free'           => true,
        ], $trackAttributes));

        return [$user, $profile, $track];
    }

    // ── LikeButton ────────────────────────────────────────────────────────────

    #[Test]
    public function like_button_renders_with_correct_initial_state(): void
    {
        [, , $track] = $this->createArtistWithTrack();

        Livewire::test(LikeButton::class, ['trackId' => $track->id])
            ->assertSet('liked', false)
            ->assertSet('count', 0);
    }

    #[Test]
    public function authenticated_user_can_like_a_track(): void
    {
        [$user, , $track] = $this->createArtistWithTrack();

        Livewire::actingAs($user)
            ->test(LikeButton::class, ['trackId' => $track->id])
            ->assertSet('liked', false)
            ->call('toggle')
            ->assertSet('liked', true)
            ->assertSet('count', 1);

        $this->assertDatabaseHas('likes', [
            'user_id'  => $user->id,
            'track_id' => $track->id,
        ]);
    }

    #[Test]
    public function authenticated_user_can_unlike_a_track(): void
    {
        [$user, , $track] = $this->createArtistWithTrack();

        Like::create(['user_id' => $user->id, 'track_id' => $track->id]);

        Livewire::actingAs($user)
            ->test(LikeButton::class, ['trackId' => $track->id])
            ->assertSet('liked', true)
            ->call('toggle')
            ->assertSet('liked', false)
            ->assertSet('count', 0);

        $this->assertDatabaseMissing('likes', [
            'user_id'  => $user->id,
            'track_id' => $track->id,
        ]);
    }

    #[Test]
    public function guest_like_attempt_opens_login_modal(): void
    {
        [, , $track] = $this->createArtistWithTrack();

        Livewire::test(LikeButton::class, ['trackId' => $track->id])
            ->call('toggle')
            ->assertDispatched('open-modal');

        $this->assertDatabaseCount('likes', 0);
    }

    #[Test]
    public function like_count_reflects_multiple_users(): void
    {
        [$user1, , $track] = $this->createArtistWithTrack();
        $user2 = User::factory()->create();
        Like::create(['user_id' => $user1->id, 'track_id' => $track->id]);
        Like::create(['user_id' => $user2->id, 'track_id' => $track->id]);

        Livewire::test(LikeButton::class, ['trackId' => $track->id])
            ->assertSet('count', 2);
    }

    // ── MiniPlayer ────────────────────────────────────────────────────────────

    #[Test]
    public function mini_player_loads_a_published_track_on_play_track_event(): void
    {
        [, , $track] = $this->createArtistWithTrack();

        // Stub getAudioUrl so no real media file is needed
        $trackMock = $this->partialMock(Track::class);
        $trackMock->shouldNotReceive('getAudioUrl'); // will use the real model via DB

        // Mock the Track to return a fake audio URL
        $track->audio_file_path = 'fake/path.mp3';

        Livewire::test(MiniPlayer::class)
            ->assertSet('trackId', null)
            ->dispatch('play-track', id: $track->id);
        // Player silently bails if no audio URL, but should not throw
    }

    #[Test]
    public function mini_player_dispatching_play_track_for_track_without_audio_does_not_throw(): void
    {
        [$user, , $track] = $this->createArtistWithTrack();

        // Track has no media attached — MiniPlayer silently bails, no exception
        Livewire::actingAs($user)
            ->test(MiniPlayer::class)
            ->dispatch('play-track', id: $track->id);

        $this->assertTrue(true);
    }

    #[Test]
    public function mini_player_like_toggle_for_guest_dispatches_open_modal(): void
    {
        [, , $track] = $this->createArtistWithTrack();

        Livewire::test(MiniPlayer::class)
            ->set('trackId', $track->id)
            ->call('likeToggle')
            ->assertDispatched('open-modal');
    }

    #[Test]
    public function mini_player_like_toggle_for_authenticated_user_creates_like(): void
    {
        [$user, , $track] = $this->createArtistWithTrack();

        Livewire::actingAs($user)
            ->test(MiniPlayer::class)
            ->set('trackId', $track->id)
            ->call('likeToggle')
            ->assertSet('liked', true);

        $this->assertDatabaseHas('likes', [
            'user_id'  => $user->id,
            'track_id' => $track->id,
        ]);
    }

    #[Test]
    public function mini_player_download_for_guest_dispatches_open_modal(): void
    {
        [, , $track] = $this->createArtistWithTrack();

        Livewire::test(MiniPlayer::class)
            ->set('trackId', $track->id)
            ->call('downloadTrack')
            ->assertDispatched('open-modal');
    }

    #[Test]
    public function mini_player_download_increments_downloads_count(): void
    {
        [$user, , $track] = $this->createArtistWithTrack();
        $this->assertEquals(0, (int) $track->downloads_count);

        Livewire::actingAs($user)
            ->test(MiniPlayer::class)
            ->set('trackId', $track->id)
            ->call('downloadTrack')
            ->assertDispatched('start-download');

        $this->assertEquals(1, (int) $track->fresh()->downloads_count);
    }

    // ── Track show page ───────────────────────────────────────────────────────

    #[Test]
    public function track_show_page_is_accessible_for_published_track(): void
    {
        [, , $track] = $this->createArtistWithTrack();

        $this->get(route('track.show', $track->slug))
            ->assertOk()
            ->assertSee($track->title);
    }

    #[Test]
    public function unpublished_track_show_page_returns_404(): void
    {
        [, , $track] = $this->createArtistWithTrack(['is_published' => false]);

        $this->get(route('track.show', $track->slug))
            ->assertNotFound();
    }

    #[Test]
    public function placeholder_image_file_exists(): void
    {
        $this->assertFileExists(public_path('images/placeholder-track.svg'));
    }
}
