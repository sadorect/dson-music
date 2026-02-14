<?php

namespace Database\Seeders;

use App\Models\Playlist;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlaylistSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        // Create playlists for users
        foreach ($users as $user) {
            // Create 1-3 playlists per user
            $playlistCount = rand(1, 3);

            for ($i = 0; $i < $playlistCount; $i++) {
                Playlist::create([
                    'user_id' => $user->id,
                    'name' => fake()->words(2, true),
                    'description' => fake()->sentence(),
                    'is_public' => fake()->boolean(70),
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }
}
