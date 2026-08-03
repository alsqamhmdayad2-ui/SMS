<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentRegistrationService
{
    /**
     * Register a new student in the system.
     *
     * @param array $studentData
     * @param array $enrollmentData
     * @param int|null $parentId
     * @return Student
     */
    public function registerStudent(array $studentData, array $enrollmentData, ?int $parentId = null): Student
    {
        return DB::transaction(function () use ($studentData, $enrollmentData, $parentId) {
            
            // 1. Generate unique student number
            if (!empty($studentData['student_number'])) {
                // If a legacy student number is provided (e.g. via Excel import), use it
                $studentNumber = $studentData['student_number'];
            } else {
                // Otherwise generate based on registration date year (or current year)
                $year = isset($enrollmentData['registration_date']) 
                        ? date('Y', strtotime($enrollmentData['registration_date'])) 
                        : date('Y');
                
                $latestStudent = Student::where('student_number', 'like', "STU-{$year}-%")->latest('id')->first();
                $sequence = $latestStudent ? intval(substr($latestStudent->student_number, -4)) + 1 : 1;
                $studentNumber = sprintf("STU-%s-%04d", $year, $sequence);
            }
            
            // 2. Create the student record
            $student = Student::create(array_merge($studentData, [
                'student_number' => $studentNumber,
                'parent_id' => $parentId,
                'grade_id' => $enrollmentData['stage_id'] ?? null, // Map to stage
                'class_id' => $enrollmentData['grade_id'] ?? null, // Map to grade
                'section_id' => $enrollmentData['section_id'] ?? null,
            ]));

            // 3. Create the enrollment record
            if (isset($enrollmentData['academic_year_id'])) {
                DB::table('student_enrollments')->insert([
                    'student_id' => $student->id,
                    'academic_year_id' => $enrollmentData['academic_year_id'],
                    'grade_id' => $enrollmentData['stage_id'] ?? null, // Stage
                    'class_id' => $enrollmentData['grade_id'] ?? null, // Grade
                    'section_id' => $enrollmentData['section_id'] ?? null,
                    'registration_date' => $enrollmentData['registration_date'] ?? now()->toDateString(),
                    'registration_type' => $enrollmentData['registration_type'] ?? 'New',
                    'previous_school' => $enrollmentData['previous_school'] ?? null,
                    'transfer_reason' => $enrollmentData['transfer_reason'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 4. Create User login account for the student
            $loginUser = User::create([
                'name'        => $student->first_name . ' ' . $student->family_name,
                'email'       => $student->email ?? ($studentNumber . '@school.internal'),
                'national_id' => $student->national_id,
                'password'    => Hash::make($student->national_id ?? '12345678'), // Default password = national_id
            ]);
            
            // Assign student role
            $loginUser->assignRole('student');

            // 5. Link the user to the student record
            $student->update(['user_id' => $loginUser->id]);

            return $student;
        });
    }
}
