<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public const NAME = 'Super Admin';
    public const EMAIL = 'superadmin@sarea.tld';
    public const PHONE = '+962790000000';
    public const PASSWORD = 'SuperAdmin@123';

    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $superAdminRole = Role::where('name', 'super-admin')->where('guard_name', 'web')->first();

        if (! $superAdminRole) {
            $this->command?->error('super-admin role was not created.');
            return;
        }

        $user = User::query()
            ->where('phone', self::PHONE)
            ->orWhere('email', self::EMAIL)
            ->first();

        if ($user) {
            $user->fill([
                'name' => self::NAME,
                'email' => self::EMAIL,
                'phone' => self::PHONE,
                'role' => 'admin',
                'status' => 'approved',
                'activity' => 'active',
            ]);

            if (blank($user->password)) {
                $user->password = self::PASSWORD;
            }

            $user->save();
        } else {
            $user = User::create([
                'name' => self::NAME,
                'email' => self::EMAIL,
                'phone' => self::PHONE,
                'password' => self::PASSWORD,
                'role' => 'admin',
                'status' => 'approved',
                'activity' => 'active',
            ]);
        }

        $user->syncRoles([$superAdminRole]);

        $this->command?->info('Super admin is ready.');
        $this->command?->info('Phone: '.self::PHONE);
        $this->command?->info('Email: '.self::EMAIL);
        $this->command?->info('Password: '.self::PASSWORD.' (only set on first create, or if the account had no password)');
    }
}
