<?php

namespace App\Console\Commands;

use App\Models\Track;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateFilesToS3 extends Command
{
    protected $signature = 'files:migrate-to-s3';

    protected $description = 'Migrate existing files from local storage to S3';

    public function handle()
    {
        $tracks = Track::all();
        $bar = $this->output->createProgressBar(count($tracks));

        foreach ($tracks as $track) {
            // Migrate track file
            if ($track->file_path && Storage::disk('public')->exists($track->file_path)) {
                $fileContent = Storage::disk('public')->get($track->file_path);
                $newPath = 'grinmuzik/tracks/'.basename($track->file_path);
                Storage::disk('s3')->put($newPath, $fileContent);

                // Update database record
                $track->file_path = $newPath;
            }

            // Migrate cover art
            if ($track->cover_art && Storage::disk('public')->exists($track->cover_art)) {
                $coverContent = Storage::disk('public')->get($track->cover_art);
                $newCoverPath = 'grinmuzik/covers/'.basename($track->cover_art);
                Storage::disk('s3')->put($newCoverPath, $coverContent);

                // Update database record
                $track->cover_art = $newCoverPath;
            }

            $track->save();
            $bar->advance();
        }

        $bar->finish();
        $this->info("\nMigration completed successfully!");
    }
}
