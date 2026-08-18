<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Grade;
use App\Models\SchoolClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SchoolClassTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $grade;
    protected $academicYear;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'admin']);
        $this->admin->assignRole($role);
        $this->grade = Grade::factory()->create();
        $this->academicYear = \App\Models\AcademicYear::factory()->create(['status' => true]);
    }

    public function test_admin_can_view_classes()
    {
        SchoolClass::factory()->count(2)->create([
            'grade_id' => $this->grade->id,
            'academic_year_id' => $this->academicYear->id
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.classes.index'));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.classes.index');
        $response->assertViewHas('classes');
    }

    public function test_admin_can_store_class()
    {
        $data = [
            'grade_id' => $this->grade->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => 'الصف الأول',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.classes.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('classes', [
            'name' => 'الصف الأول',
            'grade_id' => $this->grade->id
        ]);
    }

    public function test_admin_can_update_class()
    {
        $class = SchoolClass::factory()->create([
            'grade_id' => $this->grade->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Old Name'
        ]);

        $data = [
            'grade_id' => $this->grade->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => 'New Name',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.classes.update', $class), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('classes', [
            'id' => $class->id,
            'name' => 'New Name',
        ]);
    }

    public function test_admin_can_delete_class()
    {
        $class = SchoolClass::factory()->create(['grade_id' => $this->grade->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.classes.destroy', $class));

        $response->assertRedirect();
        $this->assertSoftDeleted('classes', ['id' => $class->id]);
    }
}
