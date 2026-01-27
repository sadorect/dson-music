<?php

namespace Database\Seeders;

use App\Models\PlayHistory;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlayHistorySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $tracks = Track::all();

        // Create play histories for users
        foreach ($users as $user) {
            // Create 10-50 play histories per user
            $playCount = rand(10, 50);

            for ($i = 0; $i < $playCount; $i++) {
                $track = $tracks->random();

                PlayHistory::create([
                    'user_id' => $user->id,
                    'track_id' => $track->id,
                    'played_at' => now()->subDays(rand(1, 30))->subMinutes(rand(0, 1440)), // Random time in last 30 days
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'location' => fake()->city().', '.fake()->state(),
                ]);
            }
        }
    }
}
