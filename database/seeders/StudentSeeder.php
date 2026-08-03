<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\ParentModel;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $parent1 = ParentModel::firstOrCreate(
            ['email' => 'parent1@school.com'],
            [
                'full_name' => 'أحمد محمود',
                'national_id' => '123456789',
                'guardian_type' => 'Father',
                'phone_1' => '0599000001',
                'address' => 'غزة',
            ]
        );

        $sections = Section::with('schoolClass.grade')->get();
        $academicYear = \App\Models\AcademicYear::where('status', true)->first();

        $firstNames = ['محمد', 'أحمد', 'محمود', 'علي', 'عمر', 'خالد', 'سعيد', 'طارق', 'يوسف', 'إبراهيم'];
        $familyNames = ['المصري', 'الشامي', 'النجار', 'الحداد', 'العوضي', 'السالم', 'الكردي', 'الحسن'];

        $service = app(\App\Services\StudentRegistrationService::class);
        $studentCounter = 1;

        foreach ($sections as $section) {
            // Seed 2 students per section
            for ($i = 0; $i < 2; $i++) {
                $fName = $firstNames[array_rand($firstNames)];
                $lName = $familyNames[array_rand($familyNames)];
                $nationalId = '400' . str_pad($studentCounter, 6, '0', STR_PAD_LEFT);
                $email = "student{$studentCounter}@school.com";

                // Check if student exists
                if (Student::where('email', $email)->exists() || Student::where('national_id', $nationalId)->exists()) {
                    $studentCounter++;
                    continue;
                }

                $studentData = [
                    'first_name' => $fName,
                    'father_name' => 'والد',
                    'grandfather_name' => 'جد',
                    'family_name' => $lName,
                    'national_id' => $nationalId,
                    'email' => $email,
                    'gender' => 'Male',
                    'birth_date' => now()->subYears(10)->toDateString(),
                    'nationality' => 'فلسطيني',
                    'blood_type' => 'O+',
                    'religion' => 'Muslim',
                    'health_status' => 'سليم',
                ];

                $enrollmentData = [
                    'academic_year_id' => $academicYear->id ?? 1,
                    'stage_id' => $section->schoolClass->grade_id ?? 1,
                    'grade_id' => $section->class_id,
                    'section_id' => $section->id,
                    'registration_date' => now()->toDateString(),
                    'registration_type' => 'New',
                ];

                $service->registerStudent($studentData, $enrollmentData, $parent1->id);
                $studentCounter++;
            }
        }
    }
}
