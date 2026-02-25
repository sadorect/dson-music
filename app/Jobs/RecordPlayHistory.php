<?php

namespace App\Jobs;

use App\Models\PlayHistory;
use App\Models\Track;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecordPlayHistory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The track ID.
     */
    public int $trackId;

    /**
     * The user ID (nullable for guests).
     */
    public ?int $userId;

    /**
     * Additional metadata.
     */
    public array $metadata;

    /**
     * Create a new job instance.
     */
    public function __construct(int $trackId, ?int $userId = null, array $metadata = [])
    {
        $this->trackId = $trackId;
        $this->userId = $userId;
        $this->metadata = $metadata;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Record the play history
            PlayHistory::create([
                'track_id' => $this->trackId,
                'user_id' => $this->userId,
                'played_at' => now(),
                'ip_address' => $this->metadata['ip_address'] ?? null,
                'user_agent' => $this->metadata['user_agent'] ?? null,
            ]);

            // Increment the track play count
            Track::where('id', $this->trackId)->increment('play_count');

            Log::info('Play history recorded', [
                'track_id' => $this->trackId,
                'user_id' => $this->userId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record play history', [
                'track_id' => $this->trackId,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
