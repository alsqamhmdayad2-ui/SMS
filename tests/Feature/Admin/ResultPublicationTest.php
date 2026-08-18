<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\ResultPublication;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ResultPublicationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $academicYear;
    protected $semester;
    protected $grade;
    protected $class;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'admin']);
        $this->admin->assignRole($role);

        $this->academicYear = AcademicYear::factory()->create(['status' => true]);
        $this->semester = Semester::factory()->create(['status' => true, 'academic_year_id' => $this->academicYear->id]);
        $this->grade = Grade::factory()->create();
        $this->class = SchoolClass::factory()->create(['grade_id' => $this->grade->id]);
        $this->section = \App\Models\Section::factory()->create(['class_id' => $this->class->id]);
        $this->student = \App\Models\Student::factory()->create(['section_id' => $this->section->id]);
        $this->subject = \App\Models\Subject::factory()->create();
        
        \Illuminate\Support\Facades\DB::table('class_subject_teacher')->insert([
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
        ]);
        
        \App\Models\StudentSemesterMark::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'class_id' => $this->class->id,
            'section_id' => $this->section->id,
            'subject_id' => $this->subject->id,
            'total' => 85,
        ]);
    }

    public function test_admin_can_view_result_publications()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.result-publications.index'));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.exams.result-publications.index');
    }

    public function test_admin_can_publish_results()
    {
        $data = [
            'publish_scope' => 'section',
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'grade_id' => $this->grade->id,
            'section_id' => $this->section->id,
            'notes' => 'Test notes',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.result-publications.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('result_publications', [
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'status' => 'published',
        ]);
    }

    public function test_admin_can_unpublish_results()
    {
        $publication = ResultPublication::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'grade_id' => $this->grade->id,
            'section_id' => $this->section->id,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.result-publications.update-status', $publication), [
            'status' => 'draft',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('result_publications', [
            'id' => $publication->id,
            'status' => 'draft',
        ]);
    }

    public function test_admin_can_delete_result_publication()
    {
        $publication = ResultPublication::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'grade_id' => $this->grade->id,
            'section_id' => \App\Models\Section::factory()->create(['class_id' => $this->class->id])->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.result-publications.destroy', $publication));

        $response->assertRedirect();
        $this->assertSoftDeleted('result_publications', ['id' => $publication->id]);
    }
}
