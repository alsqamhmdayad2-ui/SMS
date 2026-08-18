<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentResultTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $student;
    protected $academicYear;
    protected $semester;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'admin']);
        $this->admin->assignRole($role);

        $this->academicYear = AcademicYear::factory()->create(['status' => true]);
        $this->semester = Semester::factory()->create(['status' => true, 'academic_year_id' => $this->academicYear->id]);
        
        $class = SchoolClass::factory()->create();
        $section = Section::factory()->create(['class_id' => $class->id]);
        $this->student = Student::factory()->create(['section_id' => $section->id]);
    }

    public function test_admin_can_view_students_result_list()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.students.result.index'));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.exams.student-results.index');
    }

    public function test_admin_can_view_specific_student_result()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.students.result.show', $this->student));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.exams.student-results.show');
        $response->assertViewHas('student');
    }
}
