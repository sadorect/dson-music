<?php

namespace App\Jobs;

use App\Models\Track;
use getID3;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTrackUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The track instance.
     */
    public Track $track;

    /**
     * Create a new job instance.
     */
    public function __construct(Track $track)
    {
        $this->track = $track;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Extract metadata from audio file
            $filePath = storage_path('app/'.$this->track->file_path);

            if (! file_exists($filePath)) {
                Log::error('Track file not found for metadata extraction', [
                    'track_id' => $this->track->id,
                    'file_path' => $filePath,
                ]);

                return;
            }

            // Use getID3 library if available, otherwise skip metadata extraction
            if (class_exists('getID3')) {
                $getID3 = new getID3;
                $fileInfo = $getID3->analyze($filePath);

                // Update track with metadata
                $this->track->update([
                    'duration' => isset($fileInfo['playtime_string']) ? $fileInfo['playtime_string'] : null,
                ]);

                Log::info('Track metadata extracted successfully', [
                    'track_id' => $this->track->id,
                    'duration' => $this->track->duration,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to process track upload', [
                'track_id' => $this->track->id,
                'error' => $e->getMessage(),
            ]);

            // Optionally, you can rethrow the exception to retry the job
            // throw $e;
        }
    }
}
