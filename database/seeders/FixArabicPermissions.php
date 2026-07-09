<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FixArabicPermissions extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        echo "Fixing Arabic permissions to use proper spelling with hamza...\n\n";

        // Map incorrect spelling (without hamza) to correct spelling (with hamza)
        $permissionMap = [
            'اداره المستخدمين' => 'إدارة المستخدمين',
            'اداره السائقين' => 'إدارة السائقين',
            'اداره طلبات المحفظه' => 'إدارة طلبات المحفظة',
            'اداره انواع المستندات' => 'إدارة أنواع المستندات',
            'اداره فئات السيارات' => 'إدارة فئات السيارات',
            'اداره موديلات السيارات' => 'إدارة نماذج السيارات',
            'اداره انواع السيارات' => 'إدارة أنواع السيارات',
            'اداره الرحلات' => 'إدارة الرحلات',
            'اداره طرق الدفع' => 'إدارة طرق الدفع',
            'اداره سياسات الالغاء' => 'إدارة سياسات الإلغاء',
            'اداره اسباب الالغاء' => 'إدارة أسباب الإلغاء',
            'اداره الرحلات الملغاه' => 'إدارة الرحلات الملغاة',
            'اداره حدود وقت طلب الرحله' => 'إدارة حدود وقت طلب الرحلة',
            'اداره حدود OTP' => 'إدارة حدود OTP',
            'اداره الاشعارات' => 'إدارة الإشعارات',
            'اداره المناطق' => 'إدارة المناطق',
            'اداره المدن' => 'إدارة المدن',
            'اداره احصائيات الارباح' => 'إدارة إحصائيات الربح',
            'اداره الاحالات' => 'إدارة الإحالات',
            'اداره الكوبونات' => 'إدارة الكوبونات',
            'اداره الاعدادات' => 'إدارة الإعدادات',
            'اداره دردشه الدعم' => 'إدارة الدردشة الدعمية',
            'اداره الادوار' => 'إدارة الدورات',
            'اداره المشرفين' => 'إدارة المسؤولين',
            'اداره الاعلانات' => 'إدارة الإعلانات',
        ];

        foreach ($permissionMap as $incorrect => $correct) {
            $incorrectPerm = Permission::where('name', $incorrect)->where('guard_name', 'web')->first();

            if ($incorrectPerm) {
                echo "Deleting incorrect permission: {$incorrect}\n";
                $incorrectPerm->delete();
            }

            // Ensure correct permission exists
            Permission::firstOrCreate([
                'name' => $correct,
                'guard_name' => 'web'
            ]);
            echo "  ✓ Correct permission ensured: {$correct}\n";
        }

        // Ensure admin role has all correct permissions
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::all());
            echo "\n✓ Admin role synced with all permissions\n";
        }

        // Reset cache again
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        echo "\n✅ Cleanup completed!\n";
        echo "Total permissions: " . Permission::count() . "\n";
    }
}
