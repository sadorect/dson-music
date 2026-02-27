<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\ArtistProfile;
use App\Models\Genre;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Disable Scout search syncing during seeding to avoid index failures
        Track::withoutSyncingToSearch(function () {
            ArtistProfile::withoutSyncingToSearch(function () {
                $this->seedData();
            });
        });
    }

    private function seedData(): void
    {
        // ── Genres ─────────────────────────────────────────────────────────
        $genreData = [
            ['name' => 'Hip-Hop',     'color' => '#f59e0b', 'sort_order' => 1],
            ['name' => 'R&B',         'color' => '#8b5cf6', 'sort_order' => 2],
            ['name' => 'Pop',         'color' => '#ec4899', 'sort_order' => 3],
            ['name' => 'Rock',        'color' => '#ef4444', 'sort_order' => 4],
            ['name' => 'Electronic',  'color' => '#06b6d4', 'sort_order' => 5],
            ['name' => 'Jazz',        'color' => '#f97316', 'sort_order' => 6],
            ['name' => 'Afrobeats',   'color' => '#10b981', 'sort_order' => 7],
            ['name' => 'Indie',       'color' => '#6366f1', 'sort_order' => 8],
        ];

        $genres = [];
        foreach ($genreData as $data) {
            $genres[$data['name']] = Genre::firstOrCreate(
                ['name' => $data['name']],
                array_merge($data, ['is_active' => true])
            );
        }

        // ── Listener users ─────────────────────────────────────────────────
        $listeners = [
            ['name' => 'Alex Rivera',  'email' => 'alex@demo.com'],
            ['name' => 'Jordan Smith', 'email' => 'jordan@demo.com'],
        ];
        foreach ($listeners as $l) {
            $user = User::firstOrCreate(
                ['email' => $l['email']],
                [
                    'name'              => $l['name'],
                    'email_verified_at' => now(),
                    'password'          => Hash::make('password'),
                ]
            );
            if (!$user->hasRole('listener')) {
                $user->assignRole('listener');
            }
        }

        // ── Artist profiles data ────────────────────────────────────────────
        $artists = [
            [
                'name'       => 'Maya Solano',
                'email'      => 'maya@demo.com',
                'stage_name' => 'MAYA',
                'bio'        => 'Soulful R&B artist blending smooth vocals with modern production. Based in Atlanta.',
                'genre'      => 'R&B',
                'albums'     => [
                    ['title' => 'Warm Nights',   'type' => 'album', 'year' => 2023],
                    ['title' => 'Glow EP',       'type' => 'ep',    'year' => 2024],
                ],
                'tracks' => [
                    ['title' => 'Golden Hour',     'duration' => 218, 'plays' => 4200],
                    ['title' => 'Let Me In',       'duration' => 195, 'plays' => 3100],
                    ['title' => 'Still Here',      'duration' => 241, 'plays' => 2800],
                    ['title' => 'Night Drive',     'duration' => 207, 'plays' => 5600, 'donation' => true, 'price' => 2.00],
                    ['title' => 'Breathe',         'duration' => 183, 'plays' => 1900],
                ],
            ],
            [
                'name'       => 'Deon Blake',
                'email'      => 'deon@demo.com',
                'stage_name' => 'DEON BLK',
                'bio'        => 'Hip-hop lyricist. Telling real stories from real streets. No filter, all soul.',
                'genre'      => 'Hip-Hop',
                'albums'     => [
                    ['title' => 'Street Sermon',  'type' => 'album', 'year' => 2024],
                ],
                'tracks' => [
                    ['title' => 'Paper Route',     'duration' => 198, 'plays' => 7800],
                    ['title' => 'Real Talk',       'duration' => 224, 'plays' => 6200],
                    ['title' => 'No Cap',          'duration' => 187, 'plays' => 4400],
                    ['title' => 'Blood Money',     'duration' => 210, 'plays' => 9100, 'donation' => true, 'price' => 1.50],
                    ['title' => 'Legacy',          'duration' => 256, 'plays' => 3300],
                    ['title' => 'Freestyle 001',   'duration' => 163, 'plays' => 2100],
                ],
            ],
            [
                'name'       => 'Ella Voss',
                'email'      => 'ella@demo.com',
                'stage_name' => 'Ella Voss',
                'bio'        => 'Electronic producer and vocalist. Crafting soundscapes for restless minds.',
                'genre'      => 'Electronic',
                'albums'     => [
                    ['title' => 'Signal & Noise',  'type' => 'album', 'year' => 2023],
                    ['title' => 'Phase 2',         'type' => 'single', 'year' => 2025],
                ],
                'tracks' => [
                    ['title' => 'Afterglow',       'duration' => 267, 'plays' => 3400],
                    ['title' => 'Coded',           'duration' => 312, 'plays' => 2900],
                    ['title' => 'Static Dream',    'duration' => 289, 'plays' => 4100],
                    ['title' => 'Phase 2',         'duration' => 228, 'plays' => 1800, 'donation' => true, 'price' => 3.00],
                ],
            ],
            [
                'name'       => 'Kwame Asante',
                'email'      => 'kwame@demo.com',
                'stage_name' => 'KwaméA',
                'bio'        => 'Afrobeats artist bringing Lagos vibes to the world. Certified party starter.',
                'genre'      => 'Afrobeats',
                'albums'     => [
                    ['title' => 'Eko Summer',      'type' => 'ep', 'year' => 2024],
                ],
                'tracks' => [
                    ['title' => 'Celebrate',       'duration' => 234, 'plays' => 12400],
                    ['title' => 'Lagos Nights',    'duration' => 246, 'plays' => 9800],
                    ['title' => 'Move Your Body',  'duration' => 201, 'plays' => 15200],
                    ['title' => 'Afro Love',       'duration' => 219, 'plays' => 7600],
                    ['title' => 'Eko 2 the World', 'duration' => 258, 'plays' => 4900, 'donation' => true, 'price' => 1.00],
                ],
            ],
        ];

        foreach ($artists as $i => $data) {
            // Create user (idempotent)
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'email_verified_at' => now(),
                    'password'          => Hash::make('password'),
                ]
            );
            if (!$user->hasRole('artist')) {
                $user->assignRole('artist');
            }

            // Skip if profile already exists
            if ($user->artistProfile) {
                continue;
            }

            // Create artist profile
            $profile = ArtistProfile::create([
                'user_id'          => $user->id,
                'stage_name'       => $data['stage_name'],
                'slug'             => Str::slug($data['stage_name']),
                'bio'              => $data['bio'],
                'is_approved'      => true,
                'is_verified'      => $i < 2, // first two are verified
                'is_active'        => true,
                'followers_count'  => random_int(100, 5000),
                'total_plays'      => 0, // will tally below
                'total_donations'  => 0,
            ]);

            // Attach genre (BelongsToMany on ArtistProfile)
            if (isset($genres[$data['genre']])) {
                $profile->genres()->attach($genres[$data['genre']]->id);
            }

            $genreModel = $genres[$data['genre']] ?? null;
            $totalPlays = 0;

            // Create albums
            $albumMap = [];
            foreach ($data['albums'] as $ai => $albumData) {
                $album = Album::create([
                    'user_id'           => $user->id,
                    'artist_profile_id' => $profile->id,
                    'title'             => $albumData['title'],
                    'type'              => $albumData['type'],
                    'release_date'      => ($albumData['year'] . '-06-01'),
                    'genre_id'          => $genreModel?->id,
                    'is_published'      => true,
                ]);
                $albumMap[$ai] = $album;
            }

            // Create tracks
            foreach ($data['tracks'] as $ti => $trackData) {
                $play_count = $trackData['plays'];
                $totalPlays += $play_count;

                Track::create([
                    'user_id'           => $user->id,
                    'artist_profile_id' => $profile->id,
                    'album_id'          => isset($albumMap[0]) ? $albumMap[0]->id : null,
                    'genre_id'          => $genreModel?->id,
                    'title'             => $trackData['title'],
                    'slug'              => Str::slug($trackData['title'] . '-' . Str::random(4)),
                    'duration'          => $trackData['duration'],
                    'is_published'      => true,
                    'is_featured'       => false,
                    'is_free'           => !($trackData['donation'] ?? false),
                    'donation_amount'   => $trackData['price'] ?? 1.00,
                    'play_count'        => $play_count,
                    'track_number'      => $ti + 1,
                ]);
            }

            // Update total_plays on profile
            $profile->update(['total_plays' => $totalPlays]);
        }

        $this->command->info('Demo data seeded: 8 genres, 4 artists, albums & tracks.');
    }
}
