<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update super admin role
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        
        // Create or update admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        
        // Create or update artist role
        $artistRole = Role::firstOrCreate(['name' => 'artist']);
        
        // Create or update listener role
        $listenerRole = Role::firstOrCreate(['name' => 'listener']);

        // Define permissions
        $permissions = [
            // User management
            'manage users',
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Artist management
            'manage artists',
            'view artists',
            'approve artists',
            'reject artists',
            
            // Track management
            'manage tracks',
            'view tracks',
            'approve tracks',
            'reject tracks',
            
            // Album management
            'manage albums',
            'view albums',
            
            // Playlist management
            'manage playlists',
            'view playlists',
            
            // Reports
            'view reports',
            'manage reports',
            
            // Analytics
            'view analytics',
            
            // Settings
            'manage settings',
            
            // Donations
            'manage donations',
            'view donations',
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Give all permissions to super admin
        $superAdminRole->givePermissionTo(Permission::all());

        // Give limited permissions to admin
        $adminPermissions = [
            'view users', 'edit users',
            'view artists', 'approve artists', 'reject artists',
            'view tracks', 'approve tracks', 'reject tracks',
            'view albums',
            'view playlists',
            'view reports', 'manage reports',
            'view analytics',
            'view donations',
        ];
        $adminRole->givePermissionTo($adminPermissions);

        // Give permissions to artist
        $artistPermissions = [
            'view tracks',
            'manage tracks',
            'view albums',
            'manage albums',
            'view playlists',
            'manage playlists',
            'view donations',
        ];
        $artistRole->givePermissionTo($artistPermissions);

        // Give permissions to listener
        $listenerPermissions = [
            'view tracks',
            'view albums',
            'view playlists',
            'manage playlists',
        ];
        $listenerRole->givePermissionTo($listenerPermissions);

        // Create super admin user
        $superAdmin = User::firstOrCreate([
            'email' => 'admin@grinmusic.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('admin123456'),
            'email_verified_at' => now(),
        ]);

        // Assign super admin role to the user
        if (!$superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole('super_admin');
        }

        $this->command->info('✅ Super Admin seeded successfully!');
        $this->command->info('📧 Email: admin@grinmusic.com');
        $this->command->info('🔑 Password: admin123456');
        $this->command->info('🎭 Roles and permissions created!');
    }
}