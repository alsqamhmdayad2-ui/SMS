<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\AcademicYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AcademicYearTest extends TestCase
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

    public function test_admin_can_view_academic_years()
    {
        AcademicYear::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.academic-years.index'));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.academic_years.index');
        $response->assertViewHas('academicYears');
    }

    public function test_admin_can_store_academic_year()
    {
        $data = [
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.academic-years.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('academic_years', [
            'name' => '2026-2027',
            'status' => 1
        ]);
    }

    public function test_admin_can_update_academic_year()
    {
        $year = AcademicYear::factory()->create(['name' => 'Old Name']);

        $data = [
            'name' => 'New Name',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => '1',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.academic-years.update', $year), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('academic_years', [
            'id' => $year->id,
            'name' => 'New Name',
            'status' => 1
        ]);
    }

    public function test_admin_can_delete_academic_year()
    {
        $year = AcademicYear::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.academic-years.destroy', $year));

        $response->assertRedirect();
        $this->assertSoftDeleted('academic_years', ['id' => $year->id]);
    }
}
