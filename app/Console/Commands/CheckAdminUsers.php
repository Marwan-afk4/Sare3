<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckAdminUsers extends Command
{
    protected $signature = 'admin:check';
    protected $description = 'Check admin users';

    public function handle()
    {
        $this->info('Checking admin users...');
        
        $adminUsers = User::where('role', 'admin')->get(['id', 'name', 'email']);
        
        if ($adminUsers->isEmpty()) {
            $this->warn('No admin users found');
            
            // Check if user ID 1 exists and can be made admin
            $user1 = User::find(1);
            if ($user1) {
                $this->info("Found user ID 1: {$user1->name} ({$user1->email})");
                $this->info("You can make this user admin by running: php artisan tinker");
                $this->info("Then: User::find(1)->assignRole('admin')");
            }
        } else {
            $this->info('Admin users found:');
            foreach ($adminUsers as $user) {
                $this->line("{$user->id}: {$user->name} ({$user->email})");
            }
        }
        
        return 0;
    }
}