<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\MusicDataSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
/*
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'sitemanager@dsonmusic.com',
            'password' => bcrypt('password'),
            'user_type' => 'admin',

        ]);
*/
        $this->call([
           // MusicDataSeeder::class
           GeneralSettingsSeeder::class,
        ]);
    }
}
