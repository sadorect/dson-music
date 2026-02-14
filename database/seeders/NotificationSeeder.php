<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $types = ['like', 'comment', 'follow', 'playlist_add'];

        // Create notifications for users
        foreach ($users as $user) {
            // Create 1-5 notifications per user
            $notificationCount = rand(1, 5);

            for ($i = 0; $i < $notificationCount; $i++) {
                $type = $types[array_rand($types)];
                $data = match ($type) {
                    'like' => ['track_title' => fake()->words(2, true)],
                    'comment' => ['track_title' => fake()->words(2, true), 'comment_content' => fake()->sentence()],
                    'follow' => ['artist_name' => fake()->name()],
                    'playlist_add' => ['playlist_name' => fake()->words(2, true), 'track_title' => fake()->words(2, true)]
                };

                Notification::create([
                    'id' => Str::uuid(),
                    'notifiable_id' => $user->id,
                    'notifiable_type' => User::class,
                    'type' => $type,
                    'data' => $data,
                    'read_at' => fake()->boolean(50) ? now() : null,
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }
}
