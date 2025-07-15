<?php

namespace Database\Seeders;

use App\Models\Like;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class LikeSeeder extends Seeder
{
    public function run(): void
    {
        $tracks = Track::all();
        $users = User::all();
        
        // Create likes for tracks
        foreach ($tracks as $track) {
            // Randomly select 1-10 users to like this track
            $likers = $users->random(rand(1, 10));
            
            foreach ($likers as $user) {
                Like::create([
                    'user_id' => $user->id,
                    'likeable_id' => $track->id,
                    'likeable_type' => Track::class,
                    'created_at' => now()->subDays(rand(1, 30))
                ]);
            }
        }
    }
}
