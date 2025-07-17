<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@grinmusic.com',
            'password' => Hash::make('password'),
            'user_type' => 'admin',
            'email_verified_at' => now()
        ]);

        // Create regular users
        User::factory()
            ->count(10)
            ->create()
            ->each(function ($user) {
                $user->update(['user_type' => 'user']);
            });
    }
}
