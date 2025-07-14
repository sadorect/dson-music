<?php

namespace Database\Seeders;

use App\Models\Download;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class DownloadSeeder extends Seeder
{
    public function run(): void
    {
        $tracks = Track::where('status', 'published')->get();
        $users = User::all();
        
        // Create downloads for tracks
        foreach ($tracks as $track) {
            // Randomly select 1-10 users to download this track
            $downloaders = $users->random(rand(1, 10));
            
            foreach ($downloaders as $user) {
                Download::create([
                    'user_id' => $user->id,
                    'track_id' => $track->id,
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'created_at' => now()->subDays(rand(1, 30))
                ]);
            }
        }
    }
}
