<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SemesterTest extends TestCase
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
        $this->academicYear = AcademicYear::factory()->create();
    }

    public function test_admin_can_view_semesters()
    {
        Semester::factory()->count(2)->create(['academic_year_id' => $this->academicYear->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.semesters.index'));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.semesters.index');
        $response->assertViewHas('semesters');
    }

    public function test_admin_can_store_semester()
    {
        $data = [
            'academic_year_id' => $this->academicYear->id,
            'name' => 'الفصل الدراسي الأول',
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-15',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.semesters.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('semesters', [
            'name' => 'الفصل الدراسي الأول',
            'academic_year_id' => $this->academicYear->id
        ]);
    }

    public function test_admin_can_update_semester()
    {
        $semester = Semester::factory()->create(['academic_year_id' => $this->academicYear->id, 'name' => 'Old Name']);

        $data = [
            'academic_year_id' => $this->academicYear->id,
            'name' => 'New Name',
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-15',
            'status' => '1',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.semesters.update', $semester), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('semesters', [
            'id' => $semester->id,
            'name' => 'New Name',
            'status' => 1
        ]);
    }

    public function test_admin_can_delete_semester()
    {
        $semester = Semester::factory()->create(['academic_year_id' => $this->academicYear->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.semesters.destroy', $semester));

        $response->assertRedirect();
        $this->assertSoftDeleted('semesters', ['id' => $semester->id]);
    }
}
