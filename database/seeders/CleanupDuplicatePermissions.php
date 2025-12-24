<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class CleanupDuplicatePermissions extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // English permission names that should be removed
        $englishPermissions = [
            'manage users',
            'manage drivers',
            'manage wallet requests',
            'manage document types',
            'manage car categories',
            'manage car models',
            'manage car types',
            'manage rides',
            'manage payment methods',
            'manage cancellation policies',
            'manage cancellation reasons',
            'manage cancellation rides',
            'manage ride request time limits',
            'manage otp limits',
            'manage notifications',
            'manage zones',
            'manage profit statistics',
            'manage referrals',
            'manage coupons',
            'manage settings',
            'manage support chat',
            'manage roles',
            'manage admins',
            'manage ads',
        ];

        // Arabic translations (what they should be)
        $arabicPermissions = [
            'اداره المستخدمين',
            'اداره السائقين',
            'اداره طلبات المحفظه',
            'اداره انواع المستندات',
            'اداره فئات السيارات',
            'اداره موديلات السيارات',
            'اداره انواع السيارات',
            'اداره الرحلات',
            'اداره طرق الدفع',
            'اداره سياسات الالغاء',
            'اداره اسباب الالغاء',
            'اداره الرحلات الملغاه',
            'اداره حدود وقت طلب الرحله',
            'اداره حدود OTP',
            'اداره الاشعارات',
            'اداره المناطق',
            'اداره احصائيات الارباح',
            'اداره الاحالات',
            'اداره الكوبونات',
            'اداره الاعدادات',
            'اداره دردشه الدعم',
            'اداره الادوار',
            'اداره المشرفين',
            'اداره الاعلانات',
        ];

        echo "Starting cleanup of duplicate permissions...\n";

        foreach ($englishPermissions as $index => $englishName) {
            $arabicName = $arabicPermissions[$index];

            // Find English permission
            $englishPermission = Permission::where('name', $englishName)->where('guard_name', 'web')->first();

            // Find or create Arabic permission
            $arabicPermission = Permission::firstOrCreate([
                'name' => $arabicName,
                'guard_name' => 'web'
            ]);

            if ($englishPermission) {
                echo "Found English permission: {$englishName}\n";

                // Get all roles that have this English permission
                $rolesWithEnglish = $englishPermission->roles;

                // Assign Arabic permission to those roles
                foreach ($rolesWithEnglish as $role) {
                    if (!$role->hasPermissionTo($arabicPermission)) {
                        $role->givePermissionTo($arabicPermission);
                        echo "  - Assigned '{$arabicName}' to role '{$role->name}'\n";
                    }
                }

                // Delete the English permission
                $englishPermission->delete();
                echo "  - Deleted English permission: {$englishName}\n";
            } else {
                echo "English permission not found: {$englishName} (Arabic already exists: {$arabicName})\n";
            }
        }

        // Reset cache again
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        echo "\nCleanup completed! All permissions are now in Arabic only.\n";
        echo "Total Arabic permissions: " . Permission::count() . "\n";
    }
}
