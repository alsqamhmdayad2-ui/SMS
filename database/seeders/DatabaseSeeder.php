<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            GradeScaleSeeder::class,
            AssessmentComponentSeeder::class,
            AcademicYearSeeder::class,
            SemesterSeeder::class,
            GradeSeeder::class,
            SchoolClassSeeder::class,
            SectionSeeder::class,
            SubjectSeeder::class,
            ParentSeeder::class,
            TeacherSeeder::class,
            StudentSeeder::class,
            TimetableSeeder::class,
        ]);
    }
}
