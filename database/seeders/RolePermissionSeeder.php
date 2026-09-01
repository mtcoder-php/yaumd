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
            'payment.view', 'payment.create', 'payment.edit', 'payment.delete',
            // LMS
            'lms.view', 'lms.create', 'lms.edit', 'lms.delete',
            // Kutubxona
            'library.view', 'library.create', 'library.edit', 'library.delete',
            // Hisobot
            'report.view', 'report.export',
            // O'quv tuzilmasi/katalogi (fakultet, yo'nalish, kafedra, o'quv yili)
            'academic.view', 'academic.create', 'academic.edit', 'academic.delete',
            // Talabalar
            'student.view', 'student.create', 'student.edit', 'student.delete',
            // Guruhlar
            'group.view', 'group.create', 'group.edit', 'group.delete',
            // Audit log
            'audit.view',
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
            'academic.view', 'academic.create', 'academic.edit',
            'student.view', 'student.create', 'student.edit',
            'group.view', 'group.create', 'group.edit',
            'audit.view',
        ]);

        // Qabul xodimi
        $admission = Role::findByName('admission');
        $admission->givePermissionTo([
            'admission.view', 'admission.create', 'admission.edit',
            'test.view', 'contract.view', 'contract.create',
            'payment.view',
            // Ariza jarayonida yo'nalish/fakultet/o'quv yilini ko'rish va
            // "Ro'yxatga olindi" statusiga o'tgach avtomatik yaratilgan
            // talaba yozuvini ko'rish uchun (faqat ko'rish — CRUD emas)
            'academic.view', 'student.view',
        ]);

        // O'qituvchi
        $teacher = Role::findByName('teacher');
        $teacher->givePermissionTo([
            'lms.view', 'lms.create', 'lms.edit',
            'report.view',
            // O'z kursiga biriktirilgan talaba/guruhlarni ko'rish uchun
            'student.view', 'group.view',
        ]);

        // Talaba
        // MUHIM: bu yerda syncPermissions() ishlatilgan (givePermissionTo()
        // emas) — chunki avval talabaga 'lms.view' berilgan edi, bu esa
        // /admin/courses/{id} (kurs QURUVCHI — admin/o'qituvchi sahifasi)
        // ni ham talabaga ochib qo'ygan edi ("Kurslarim" o'zining
        // /admin/my-courses sahifasi uchun endi umuman permission talab
        // qilmaydi, qarang routes/admin.php). givePermissionTo() faqat
        // QO'SHADI, hech qachon olib tashlamaydi — shuning uchun 'lms.view'ni
        // shu ro'yxatdan shunchaki o'chirish, seederni qayta ishga
        // tushirsangiz ham, eskisini bazadan olib tashlamas edi.
        // syncPermissions() esa ro'yxatni ANIQ shunga TENGLASHTIRADI.
        $student = Role::findByName('student');
        $student->syncPermissions([
            'library.view',
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

        // Super Admin foydalanuvchi yaratish/yangilash.
        // updateOrCreate ishlatilgan — chunki firstOrCreate faqat email
        // TOPILMAGANDA yangi qator yaratadi; agar shu email bilan qator
        // allaqachon bazada bo'lsa, ikkinchi argumentdagi (parol va h.k.)
        // qiymatlarni butunlay E'TIBORGA OLMAYDI va eskisini o'zgartirmasdan
        // qaytaradi. Aynan shu sabab bilan email/parolni o'zgartirib
        // seederni qayta ishga tushirganingizda o'zgarish kuchga kirmagan.
        $user = User::updateOrCreate(
            ['email' => 'mukhtorturdiyev@gmail.com'],
            [
                'full_name' => 'Mukhtor Turdiyev',
                'password'  => bcrypt('Muxtor_2026'),
                'is_active' => true,
            ]
        );

        $user->assignRole('super-admin');

        $this->command->info('Rollar va permissionlar yaratildi!');
        $this->command->info("Super Admin: {$user->email} (parol — yuqoridagi bcrypt() qatorida ko'rsatilgan)");
    }
}
