<?php

namespace Database\Seeders;

use App\Models\ArtistProfile;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Database\Seeder;

class FollowSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $artists = ArtistProfile::where('is_verified', true)->get();

        // Create follows for users
        foreach ($users as $user) {
            // Randomly select 1-5 artists to follow
            $artistsToFollow = $artists->random(rand(1, 5));

            foreach ($artistsToFollow as $artist) {
                Follow::create([
                    'user_id' => $user->id,
                    'artist_profile_id' => $artist->id,
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }
}
