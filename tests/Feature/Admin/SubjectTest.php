<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubjectTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $academicYear;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'admin']);
        $this->admin->assignRole($role);

        $this->academicYear = AcademicYear::factory()->create(['status' => true]);
    }

    public function test_admin_can_view_subjects()
    {
        Subject::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.subjects.index'));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.subjects.index');
        $response->assertViewHas('subjects');
    }

    public function test_admin_can_store_subject()
    {
        $class = SchoolClass::factory()->create();

        $data = [
            'name' => 'Physics',
            'code' => 'PHY-101',
            'description' => 'Physics subject',
            'class_ids' => [$class->id],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.subjects.store'), $data);

        $response->assertRedirect(route('admin.subjects.index'));
        $this->assertDatabaseHas('subjects', [
            'name' => 'Physics',
            'code' => 'PHY-101',
        ]);
        
        // Assert pivot table
        $this->assertDatabaseHas('class_subject_teacher', [
            'class_id' => $class->id,
        ]);
    }

    public function test_admin_can_update_subject()
    {
        $subject = Subject::factory()->create(['name' => 'Old Name']);
        $class = SchoolClass::factory()->create();

        $data = [
            'name' => 'New Name',
            'code' => $subject->code,
            'description' => 'Updated Description',
            'status' => '1',
            'class_ids' => [$class->id]
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.subjects.update', $subject), $data);

        $response->assertRedirect(route('admin.subjects.index'));
        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'name' => 'New Name',
        ]);
    }

    public function test_admin_can_assign_teacher_to_subject_section()
    {
        $subject = Subject::factory()->create();
        $class = SchoolClass::factory()->create();
        $section = Section::factory()->create(['class_id' => $class->id]);
        $teacher = Teacher::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.subjects.assignTeacher', $subject), [
            'section_id' => $section->id,
            'teacher_id' => $teacher->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('subject_section_teacher', [
            'academic_year_id' => $this->academicYear->id,
            'subject_id' => $subject->id,
            'section_id' => $section->id,
            'teacher_id' => $teacher->id,
        ]);
    }

    public function test_admin_can_delete_subject()
    {
        $subject = Subject::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.subjects.destroy', $subject));

        $response->assertRedirect(route('admin.subjects.index'));
        $this->assertSoftDeleted('subjects', ['id' => $subject->id]);
    }
}
