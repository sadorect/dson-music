<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create super admin user
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@grinmusic.com',
            'password' => Hash::make('password'),
            'user_type' => 'admin',
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Create regular admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@grinmusic.com',
            'password' => Hash::make('password'),
            'user_type' => 'admin',
            'is_super_admin' => false,
            'email_verified_at' => now(),
        ]);

        // Create artist users
        User::factory()
            ->count(5)
            ->create()
            ->each(function ($user) {
                $user->update(['user_type' => 'artist']);
            });

        // Create regular users
        User::factory()
            ->count(10)
            ->create()
            ->each(function ($user) {
                $user->update(['user_type' => 'user']);
            });
    }
}
