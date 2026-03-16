<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RestoreAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:restore-admin-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore admin role and assign it to the specific user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = '01111679168';

        $this->info("Starting restoration process...");

        // 1. Create the admin role if it doesn't exist
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);

        $this->info("Admin role ensured.");

        // 2. Assign all permissions to it (matching RolesAndPermissionsSeeder behavior)
        $permissions = Permission::all();
        $adminRole->syncPermissions($permissions);
        
        $this->info("Synchronized " . $permissions->count() . " permissions to admin role.");

        // 3. Find the user
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            $this->error("User with phone {$phone} not found!");
            return 1;
        }

        // 4. Assign the role to the user
        $user->assignRole($adminRole);

        $this->info("Successfully assigned 'admin' role to user: {$user->name} ({$phone})");

        return 0;
    }
}
