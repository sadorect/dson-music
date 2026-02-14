<?php

namespace Tests\Feature;

use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadDonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_donation_track_download_is_blocked_when_amount_is_below_minimum(): void
    {
        $user = User::factory()->create();
        $track = Track::factory()->create([
            'download_type' => 'donate',
            'minimum_donation' => 5.00,
            'file_path' => 'grinmuzik/tracks/test-track.mp3',
        ]);

        $response = $this->actingAs($user)
            ->from(route('tracks.show', $track))
            ->get(route('tracks.download', [
                'track' => $track,
                'donation_amount' => 2.00,
            ]));

        $response->assertRedirect(route('tracks.show', $track));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('downloads', [
            'user_id' => $user->id,
            'track_id' => $track->id,
            'status' => 'failed',
        ]);
    }

    public function test_donation_track_download_succeeds_when_amount_meets_minimum(): void
    {
        $user = User::factory()->create();
        $track = Track::factory()->create([
            'download_type' => 'donate',
            'minimum_donation' => 5.00,
            'file_path' => 'grinmuzik/tracks/test-track.mp3',
            'downloads_count' => 0,
        ]);

        Storage::shouldReceive('disk')
            ->once()
            ->with('s3')
            ->andReturnSelf();

        Storage::shouldReceive('exists')
            ->once()
            ->with('grinmuzik/tracks/test-track.mp3')
            ->andReturn(true);

        Storage::shouldReceive('temporaryUrl')
            ->once()
            ->andReturn('https://example.test/download.mp3');

        $response = $this->actingAs($user)->get(route('tracks.download', [
            'track' => $track,
            'donation_amount' => 5.00,
        ]));

        $response->assertRedirect('https://example.test/download.mp3');

        $this->assertDatabaseHas('downloads', [
            'user_id' => $user->id,
            'track_id' => $track->id,
            'status' => 'completed',
        ]);

        $this->assertSame(1, $track->fresh()->downloads_count);
    }
}
