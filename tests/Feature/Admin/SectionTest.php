<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SectionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $grade;
    protected $class;
    protected $academicYear;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'admin']);
        $this->admin->assignRole($role);
        $this->grade = Grade::factory()->create();
        $this->academicYear = \App\Models\AcademicYear::factory()->create(['status' => true]);
        $this->class = SchoolClass::factory()->create([
            'grade_id' => $this->grade->id,
            'academic_year_id' => $this->academicYear->id
        ]);
    }

    public function test_admin_can_view_sections()
    {
        Section::factory()->count(2)->create([
            'class_id' => $this->class->id
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.sections.index'));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.sections.index');
        $response->assertViewHas('sections');
    }

    public function test_admin_can_store_section()
    {
        $data = [
            'class_id' => $this->class->id,
            'name' => 'الشعبة أ',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.sections.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('sections', [
            'name' => 'الشعبة أ',
            'class_id' => $this->class->id,
            'status' => 1
        ]);
    }

    public function test_admin_can_update_section()
    {
        $section = Section::factory()->create([
            'class_id' => $this->class->id,
            'name' => 'Old Name'
        ]);

        $data = [
            'class_id' => $this->class->id,
            'name' => 'New Name',
            'status' => '0'
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.sections.update', $section), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('sections', [
            'id' => $section->id,
            'name' => 'New Name',
            'status' => 0
        ]);
    }

    public function test_admin_can_delete_section()
    {
        $section = Section::factory()->create([
            'class_id' => $this->class->id
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.sections.destroy', $section));

        $response->assertRedirect();
        $this->assertSoftDeleted('sections', ['id' => $section->id]);
    }
}
