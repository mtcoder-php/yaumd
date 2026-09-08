<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            RegionDistrictSeeder::class,
            FacultyDirectionSeeder::class,
            AcademicStructureSeeder::class,
            AdmissionSeeder::class,
            CourseSeeder::class,
        ]);
    }
}
