<?php

namespace Tests\Feature;

use App\Models\Download;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackfillDownloadCountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_syncs_download_counts_from_completed_records(): void
    {
        $track = Track::factory()->create(['downloads_count' => 0]);
        $user = User::factory()->create();

        Download::create([
            'user_id' => $user->id,
            'track_id' => $track->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'status' => 'completed',
        ]);

        Download::create([
            'user_id' => $user->id,
            'track_id' => $track->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'status' => 'completed',
        ]);

        Download::create([
            'user_id' => $user->id,
            'track_id' => $track->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'status' => 'completed',
        ]);

        Download::create([
            'user_id' => $user->id,
            'track_id' => $track->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'status' => 'failed',
        ]);

        $this->artisan('downloads:sync-counts')
            ->expectsOutput('Download counts synchronized for 1 tracks.')
            ->assertExitCode(0);

        $this->assertEquals(3, $track->fresh()->downloads_count);
    }
}
