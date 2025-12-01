<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Sidebar items permissions
        $permissions = [
            __('manage users'),
            __('manage drivers'),
            __('manage wallet requests'),
            __('manage document types'),
            __('manage car categories'),
            __('manage car models'),
            __('manage car types'),
            __('manage rides'),
            __('manage payment methods'),
            __('manage cancellation policies'),
            __('manage cancellation reasons'),
            __('manage cancellation rides'),
            __('manage ride request time limits'),
            __('manage otp limits'),
            __('manage notifications'),
            __('manage zones'),
            __('manage profit statistics'),
            __('manage referrals'),
            __('manage coupons'),
            __('manage settings'),
            __('manage support chat'),
            __('manage roles'),
            __('manage admins'),
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Create Admin Role and assign all permissions
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);
        $adminRole->givePermissionTo(Permission::all());

        ///// Create a Super Admin Role (optional, but good practice)
        // $superAdminRole = Role::firstOrCreate([
        //     'name' => 'super-admin',
        //     'guard_name' => 'web'
        // ]);
        //// Super admin gets all permissions via Gate::before rule usually, but we can also assign them
        // $superAdminRole->givePermissionTo(Permission::all());
    }
}
