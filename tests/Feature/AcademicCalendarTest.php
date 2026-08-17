<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\SchoolEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_academic_calendar()
    {
        // 1. Create a student user
        $user = User::factory()->create(['role' => 'student']);
        $student = Student::factory()->create(['user_id' => $user->id]);

        // 2. Create active academic year and related events
        $academicYear = AcademicYear::factory()->create(['status' => true]);
        Semester::factory()->create(['academic_year_id' => $academicYear->id]);
        SchoolEvent::factory()->create(['academic_year_id' => $academicYear->id]);

        // 3. Act & Assert
        $response = $this->actingAs($user)->get(route('student.academic-calendar'));

        $response->assertStatus(200);
        $response->assertViewIs('panels.student.academic-calendar');
        $response->assertViewHas('events');
    }

    public function test_non_student_cannot_access_academic_calendar()
    {
        $user = User::factory()->create(['role' => 'parent']);

        $response = $this->actingAs($user)->get(route('student.academic-calendar'));

        // Assuming middleware redirects unauthorized users
        $response->assertStatus(403);
    }
}
