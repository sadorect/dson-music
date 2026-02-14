<?php

namespace Database\Seeders;

use App\Models\ArtistProfile;
use App\Models\Track;
use Illuminate\Database\Seeder;

class GradientSeeder extends Seeder
{
    public function run()
    {
        $this->seedGradients(Track::class);
        $this->seedGradients(ArtistProfile::class);
    }

    private function seedGradients($model)
    {
        $records = $model::whereNull('gradient_start_color')->get();

        foreach ($records as $record) {
            // Generate consistent colors based on the record's ID
            $startColor = $this->generateColor($record->id);
            $endColor = $this->generateColor($record->id + 1);

            $record->update([
                'gradient_start_color' => $startColor,
                'gradient_end_color' => $endColor,
            ]);
        }
    }

    private function generateColor($seed)
    {
        // Generate consistent colors based on seed
        $hash = hash('sha256', $seed.config('app.key'));
        $r = hexdec(substr($hash, 0, 2));
        $g = hexdec(substr($hash, 2, 2));
        $b = hexdec(substr($hash, 4, 2));

        // Ensure colors are not too dark
        $r = min(200, $r + 55);
        $g = min(200, $g + 55);
        $b = min(200, $b + 55);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
