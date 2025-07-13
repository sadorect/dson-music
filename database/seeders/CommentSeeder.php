<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $tracks = Track::all();
        $users = User::all();
        
        // Create comments for tracks
        foreach ($tracks as $track) {
            // Randomly create 0-5 comments per track
            $commentCount = rand(0, 5);
            
            for ($i = 0; $i < $commentCount; $i++) {
                $user = $users->random();
                Comment::create([
                    'user_id' => $user->id,
                    'commentable_id' => $track->id,
                    'commentable_type' => Track::class,
                    'content' => fake()->paragraph(),
                    'created_at' => now()->subDays(rand(1, 30))
                ]);
            }
        }
    }
}
