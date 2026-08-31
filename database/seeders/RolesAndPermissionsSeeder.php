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

        // Sidebar items permissions (in proper Arabic with hamza)
        $permissions = [
            'إدارة المستخدمين',
            'إدارة السائقين',
            'إدارة طلبات المحفظة',
            'إدارة التقارير المالية',
            'إدارة أنواع المستندات',
            'إدارة فئات السيارات',
            'إدارة نماذج السيارات',
            'إدارة أنواع السيارات',
            'إدارة الرحلات',
            'إدارة طرق الدفع',
            'إدارة سياسات الإلغاء',
            'إدارة أسباب الإلغاء',
            'إدارة الرحلات الملغاة',
            'إدارة حدود وقت طلب الرحلة',
            'إدارة حدود OTP',
            'إدارة الإشعارات',
            'إدارة المناطق',
            'إدارة المدن',
            'إدارة إحصائيات الربح',
            'إدارة الإحالات',
            'إدارة الكوبونات',
            'إدارة الإعدادات',
            'إدارة الدردشة الدعمية',
            'إدارة الدورات',
            'إدارة المسؤولين',
            'إدارة الإعلانات',
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
        $adminRole->syncPermissions(Permission::all());

        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web'
        ]);
        $superAdminRole->syncPermissions(Permission::all());
    }
}
