<?php

namespace App\Console\Commands;

use App\Models\Track;
use Illuminate\Console\Command;

class BackfillDownloadCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'downloads:sync-counts {--chunk=100 : Number of tracks to process per chunk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize tracks.downloads_count with completed downloads records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk');
        $updated = 0;

        Track::chunkById($chunkSize, function ($tracks) use (&$updated) {
            foreach ($tracks as $track) {
                $completedDownloads = $track->downloads()
                    ->where('status', 'completed')
                    ->count();

                if ($track->downloads_count !== $completedDownloads) {
                    $track->forceFill(['downloads_count' => $completedDownloads])->save();
                    $updated++;
                }
            }
        });

        $this->info("Download counts synchronized for {$updated} tracks.");

        return Command::SUCCESS;
    }
}
