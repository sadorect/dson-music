<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ArtistProfile;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $tracks = Track::all();
        $artists = ArtistProfile::where('is_verified', true)->get();

        $types = [
            'track_play' => ['track_id'],
            'track_download' => ['track_id'],
            'artist_follow' => ['artist_id'],
            'track_like' => ['track_id'],
            'playlist_create' => [],
            'comment_create' => ['track_id'],
        ];

        // Create activities for users
        foreach ($users as $user) {
            // Create 1-10 activities per user
            $activityCount = rand(1, 10);

            for ($i = 0; $i < $activityCount; $i++) {
                $type = array_rand($types);
                $data = [];

                foreach ($types[$type] as $field) {
                    $data[$field] = match ($field) {
                        'track_id' => $tracks->random()->id,
                        'artist_id' => $artists->random()->id
                    };
                }

                Activity::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'description' => match ($type) {
                        'track_play' => 'Listened to track',
                        'track_download' => 'Downloaded track',
                        'artist_follow' => 'Followed artist',
                        'track_like' => 'Liked track',
                        'playlist_create' => 'Created playlist',
                        'comment_create' => 'Commented on track'
                    },
                    'data' => $data,
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }
}
