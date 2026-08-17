<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicHistoryJsonTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_history_json()
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        
        $academicYear = AcademicYear::factory()->create(['start_date' => '2023-09-01']);
        $grade = Grade::factory()->create();
        $schoolClass = SchoolClass::factory()->create();
        $section = Section::factory()->create();

        StudentEnrollment::factory()->create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'grade_id' => $grade->id,
            'school_class_id' => $schoolClass->id,
            'section_id' => $section->id,
            'status' => 'promoted',
        ]);

        $response = $this->actingAs($user)->getJson('/student/academic-history');
        
        file_put_contents('academic_history_output.json', json_encode($response->json(), JSON_PRETTY_PRINT));
        
        $this->assertTrue(true);
    }
}
