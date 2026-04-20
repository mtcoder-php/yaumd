<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Rollar
        $roles = [
            'super-admin'    => 'Super Admin',
            'admin'          => 'Admin',
            'admission'      => 'Qabul xodimi',
            'teacher'        => "O'qituvchi",
            'student'        => 'Talaba',
            'librarian'      => 'Kutubxonachi',
            'finance'        => 'Moliya xodimi',
        ];

        foreach ($roles as $name => $display) {
            Role::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // Permissionlar (modul bo'yicha)
        $permissions = [
            // Foydalanuvchilar
            'user.view', 'user.create', 'user.edit', 'user.delete',
            // Qabul
            'admission.view', 'admission.create', 'admission.edit', 'admission.delete',
            // Test
            'test.view', 'test.create', 'test.edit', 'test.delete',
            // Kontrakt
            'contract.view', 'contract.create', 'contract.edit', 'contract.delete',
            // To'lov
            'payment.view', 'payment.create', 'payment.edit',
            // LMS
            'lms.view', 'lms.create', 'lms.edit', 'lms.delete',
            // Kutubxona
            'library.view', 'library.create', 'library.edit', 'library.delete',
            // Hisobot
            'report.view', 'report.export',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // Super admin — barcha permissionlar
        $superAdmin = Role::findByName('super-admin');
        $superAdmin->givePermissionTo(Permission::all());

        // Admin
        $admin = Role::findByName('admin');
        $admin->givePermissionTo([
            'user.view', 'user.create', 'user.edit',
            'admission.view', 'admission.create', 'admission.edit',
            'test.view', 'test.create', 'test.edit',
            'contract.view', 'contract.create', 'contract.edit',
            'payment.view', 'payment.create',
            'lms.view', 'lms.create', 'lms.edit',
            'library.view', 'library.create', 'library.edit',
            'report.view', 'report.export',
        ]);

        // Qabul xodimi
        $admission = Role::findByName('admission');
        $admission->givePermissionTo([
            'admission.view', 'admission.create', 'admission.edit',
            'test.view', 'contract.view', 'contract.create',
            'payment.view',
        ]);

        // O'qituvchi
        $teacher = Role::findByName('teacher');
        $teacher->givePermissionTo([
            'lms.view', 'lms.create', 'lms.edit',
            'report.view',
        ]);

        // Talaba
        $student = Role::findByName('student');
        $student->givePermissionTo([
            'lms.view', 'library.view',
        ]);

        // Kutubxonachi
        $librarian = Role::findByName('librarian');
        $librarian->givePermissionTo([
            'library.view', 'library.create', 'library.edit', 'library.delete',
        ]);

        // Moliya xodimi
        $finance = Role::findByName('finance');
        $finance->givePermissionTo([
            'payment.view', 'payment.create', 'payment.edit',
            'contract.view', 'report.view', 'report.export',
        ]);

        // Super Admin foydalanuvchi yaratish
        $user = User::firstOrCreate(
            ['email' => 'admin@yaumd.uz'],
            [
                'full_name' => 'Super Administrator',
                'password'  => bcrypt('Admin@12345'),
                'is_active' => true,
            ]
        );

        $user->assignRole('super-admin');

        $this->command->info('Rollar va permissionlar yaratildi!');
        $this->command->info('Super Admin: admin@yaumd.uz | Admin@12345');
    }
}
