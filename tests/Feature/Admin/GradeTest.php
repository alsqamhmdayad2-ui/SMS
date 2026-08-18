<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Grade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GradeTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'admin']);
        $this->admin->assignRole($role);
    }

    public function test_admin_can_view_grades()
    {
        Grade::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.grades.index'));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.grades.index');
        $response->assertViewHas('grades');
    }

    public function test_admin_can_store_grade()
    {
        $data = [
            'name' => 'المرحلة الابتدائية',
            'description' => 'Some notes here',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.grades.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('grades', [
            'name' => 'المرحلة الابتدائية'
        ]);
    }

    public function test_admin_can_update_grade()
    {
        $grade = Grade::factory()->create(['name' => 'Old Name']);

        $data = [
            'name' => 'New Name',
            'description' => 'Updated notes',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.grades.update', $grade), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('grades', [
            'id' => $grade->id,
            'name' => 'New Name',
            'description' => 'Updated notes',
        ]);
    }

    public function test_admin_can_delete_grade()
    {
        $grade = Grade::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.grades.destroy', $grade));

        $response->assertRedirect();
        $this->assertSoftDeleted('grades', ['id' => $grade->id]);
    }
}
