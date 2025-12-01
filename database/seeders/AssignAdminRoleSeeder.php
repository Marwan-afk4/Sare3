<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignAdminRoleSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('name', 'admin')->first();

        if (!$adminRole) {
            $this->command->error('Admin role not found. Please run RolesAndPermissionsSeeder first.');
            return;
        }

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $user) {
            if (!$user->hasRole('admin')) {
                $user->assignRole($adminRole);
                $this->command->info("Assigned admin role to user: {$user->email}");
            }
        }
    }
}
