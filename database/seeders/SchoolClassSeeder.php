<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::where('name', '2025 / 2026')->first();
        $primary = Grade::where('name', 'المرحلة الابتدائية')->first();
        $prep = Grade::where('name', 'المرحلة الإعدادية')->first();
        $high = Grade::where('name', 'المرحلة الثانوية')->first();

        if ($primary && $academicYear) {
            $primaryClasses = ['الصف الأول', 'الصف الثاني', 'الصف الثالث', 'الصف الرابع', 'الصف الخامس', 'الصف السادس'];
            foreach ($primaryClasses as $className) {
                SchoolClass::firstOrCreate(
                    ['name' => $className, 'grade_id' => $primary->id, 'academic_year_id' => $academicYear->id],
                    ['status' => 1]
                );
            }
        }

        if ($prep && $academicYear) {
            $prepClasses = ['الصف السابع', 'الصف الثامن', 'الصف التاسع'];
            foreach ($prepClasses as $className) {
                SchoolClass::firstOrCreate(
                    ['name' => $className, 'grade_id' => $prep->id, 'academic_year_id' => $academicYear->id],
                    ['status' => 1]
                );
            }
        }

        if ($high && $academicYear) {
            $highClasses = ['الصف العاشر', 'الصف الحادي عشر', 'الصف الثاني عشر'];
            foreach ($highClasses as $className) {
                SchoolClass::firstOrCreate(
                    ['name' => $className, 'grade_id' => $high->id, 'academic_year_id' => $academicYear->id],
                    ['status' => 1]
                );
            }
        }
    }
}
