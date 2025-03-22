<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:make-super {email}';

   
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make an existing user a super admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }
        
        $user->user_type = 'admin';
        $user->is_super_admin = true;
        $user->admin_permissions = null;
        $user->save();
        
        $this->info("User {$user->name} ({$user->email}) is now a super admin.");
        
        return 0;
    }
}
