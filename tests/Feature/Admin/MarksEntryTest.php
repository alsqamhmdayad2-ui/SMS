<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MarksEntryTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $academicYear;
    protected $semester;
    protected $section;
    protected $subject;
    protected $student;
    protected $exam;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'admin']);
        $this->admin->assignRole($role);

        $this->academicYear = AcademicYear::factory()->create(['status' => true]);
        $this->semester = Semester::factory()->create(['status' => true, 'academic_year_id' => $this->academicYear->id]);
        $this->section = Section::factory()->create();
        $this->subject = Subject::factory()->create();
        $this->student = Student::factory()->create(['section_id' => $this->section->id]);
        
        // Link student to section via enrollment
        \Illuminate\Support\Facades\DB::table('student_enrollments')->insert([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_id' => $this->section->schoolClass->grade_id,
            'class_id' => $this->section->class_id,
            'registration_date' => now()->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->exam = Exam::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'class_id' => $this->section->class_id,
            'subject_id' => $this->subject->id,
            'total_marks' => 100,
        ]);
    }

    public function test_admin_can_save_mark_via_ajax()
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.marks-entry.save-mark'), [
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'marks_obtained' => 85,
            'attendance_status' => 'present',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('exam_results', [
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'marks_obtained' => 85,
        ]);
    }

    public function test_admin_can_delete_mark_via_ajax()
    {
        // Create an existing mark
        \App\Models\ExamResult::factory()->create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'marks_obtained' => 90,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson(route('admin.marks-entry.delete-mark'), [
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseMissing('exam_results', [
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
        ]);
    }
}
