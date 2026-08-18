<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\ParentModel;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $grade;
    protected $academicYear;
    protected $class;
    protected $section;
    protected $parent;

    protected function setUp(): void
    {
        parent::setUp();

        // Admin user
        $this->admin = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $this->admin->assignRole($adminRole);

        // Required roles for students
        Role::firstOrCreate(['name' => 'student']);

        // Academic structure
        $this->academicYear = AcademicYear::factory()->create(['status' => true]);
        $this->grade        = Grade::factory()->create();
        $this->class        = SchoolClass::factory()->create([
            'grade_id'        => $this->grade->id,
            'academic_year_id'=> $this->academicYear->id,
        ]);
        $this->section = Section::factory()->create([
            'class_id' => $this->class->id,
        ]);

        // Parent
        $this->parent = ParentModel::factory()->create();
    }

    // ───────────────────────────────────────────
    // قائمة الطلاب
    // ───────────────────────────────────────────

    public function test_admin_can_view_students_list()
    {
        Student::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.students.index'));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.students.index');
        $response->assertViewHas('students');
    }

    public function test_admin_can_search_students_by_first_name()
    {
        Student::factory()->create(['first_name' => 'Ahmad', 'family_name' => 'Hasan']);
        Student::factory()->create(['first_name' => 'Sara',  'family_name' => 'Nasser']);

        $response = $this->actingAs($this->admin)->get(route('admin.students.index', ['search' => 'Ahmad']));

        $response->assertStatus(200);
        $response->assertSee('Ahmad');
    }

    public function test_admin_can_filter_students_by_class()
    {
        $student = Student::factory()->create([
            'class_id'   => $this->class->id,
            'section_id' => $this->section->id,
            'grade_id'   => $this->grade->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.students.index', ['class_id' => $this->class->id]));

        $response->assertStatus(200);
        $response->assertSee($student->first_name);
    }

    // ───────────────────────────────────────────
    // عرض بيانات طالب
    // ───────────────────────────────────────────

    public function test_admin_can_view_student_details()
    {
        $student = Student::factory()->create([
            'class_id'   => $this->class->id,
            'section_id' => $this->section->id,
            'grade_id'   => $this->grade->id,
            'parent_id'  => $this->parent->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.students.show', $student));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.students.show');
        $response->assertViewHas('student');
    }

    // ───────────────────────────────────────────
    // إضافة طالب
    // ───────────────────────────────────────────

    public function test_admin_can_view_create_student_form()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.students.create'));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.students.create');
    }

    public function test_admin_can_store_student_with_existing_parent()
    {
        // Set the academic year as active/default
        \Illuminate\Support\Facades\DB::table('academic_years')
            ->where('id', $this->academicYear->id)
            ->update(['status' => 1]);

        $data = [
            'first_name'        => 'محمد',
            'father_name'       => 'عمر',
            'grandfather_name'  => 'خالد',
            'family_name'       => 'الأحمد',
            'national_id'       => '987654321',
            'birth_date'        => '2010-05-15',
            'gender'            => 'Male',
            'nationality'       => 'فلسطيني',
            'religion'          => 'Muslim',
            'governorate'       => 'رام الله',
            'city'              => 'رام الله',
            'parent_id'         => $this->parent->id,
            'stage_id'          => $this->grade->id,
            'grade_id'          => $this->class->id,
            'section_id'        => $this->section->id,
            'registration_date' => '2026-09-01',
            'registration_type' => 'New',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.students.store'), $data);

        $response->assertRedirect(route('admin.students.index'));
        $this->assertDatabaseHas('students', [
            'first_name'  => 'محمد',
            'family_name' => 'الأحمد',
            'national_id' => '987654321',
        ]);
    }

    public function test_store_student_requires_mandatory_fields()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.students.store'), []);

        $response->assertSessionHasErrors([
            'first_name', 'father_name', 'grandfather_name', 'family_name',
            'national_id', 'birth_date', 'gender',
            'stage_id', 'grade_id', 'section_id',
            'registration_date', 'registration_type',
        ]);
    }

    public function test_store_student_requires_unique_national_id()
    {
        Student::factory()->create(['national_id' => 'DUPLICATE123']);

        $data = [
            'first_name'       => 'Test',
            'father_name'      => 'Test',
            'grandfather_name' => 'Test',
            'family_name'      => 'Test',
            'national_id'      => 'DUPLICATE123',
            'birth_date'       => '2010-01-01',
            'gender'           => 'Male',
            'nationality'      => 'فلسطيني',
            'governorate'      => 'رام الله',
            'city'             => 'رام الله',
            'parent_id'        => $this->parent->id,
            'stage_id'         => $this->grade->id,
            'grade_id'         => $this->class->id,
            'section_id'       => $this->section->id,
            'registration_date'=> '2026-09-01',
            'registration_type'=> 'New',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.students.store'), $data);

        $response->assertSessionHasErrors('national_id');
    }

    // ───────────────────────────────────────────
    // تعديل بيانات طالب
    // ───────────────────────────────────────────

    public function test_admin_can_view_edit_student_form()
    {
        $student = Student::factory()->create([
            'class_id'   => $this->class->id,
            'section_id' => $this->section->id,
            'grade_id'   => $this->grade->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.students.edit', $student));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.students.edit');
        $response->assertViewHas('student');
    }

    public function test_admin_can_update_student_name()
    {
        $student = Student::factory()->create([
            'first_name'  => 'Old',
            'family_name' => 'Name',
            'class_id'    => $this->class->id,
            'section_id'  => $this->section->id,
            'grade_id'    => $this->grade->id,
            'parent_id'   => $this->parent->id,
        ]);

        $data = [
            'first_name'       => 'New',
            'father_name'      => $student->father_name,
            'grandfather_name' => $student->grandfather_name,
            'family_name'      => 'Updated',
            'national_id'      => $student->national_id,
            'birth_date'       => '2010-05-15',
            'gender'           => $student->gender,
            'nationality'      => $student->nationality,
            'religion'         => $student->religion ?? 'Muslim',
            'governorate'      => 'رام الله',
            'city'             => 'رام الله',
            'parent_id'        => $this->parent->id,
            'stage_id'         => $this->grade->id,
            'grade_id'         => $this->class->id,
            'section_id'       => $this->section->id,
            'registration_date'=> '2026-09-01',
            'registration_type'=> 'New',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.students.update', $student), $data);

        $response->assertRedirect(route('admin.students.index'));
        $this->assertDatabaseHas('students', [
            'id'          => $student->id,
            'first_name'  => 'New',
            'family_name' => 'Updated',
        ]);
    }

    // ───────────────────────────────────────────
    // نقل طالب بين الشعب
    // ───────────────────────────────────────────

    public function test_admin_can_transfer_student_to_another_section()
    {
        $newSection = Section::factory()->create([
            'class_id' => $this->class->id,
        ]);

        $student = Student::factory()->create([
            'class_id'   => $this->class->id,
            'section_id' => $this->section->id,
            'grade_id'   => $this->grade->id,
        ]);

        // Create enrollment record required for transfer
        \Illuminate\Support\Facades\DB::table('student_enrollments')->insert([
            'student_id'        => $student->id,
            'academic_year_id'  => $this->academicYear->id,
            'grade_id'          => $this->grade->id,
            'class_id'          => $this->class->id,
            'section_id'        => $this->section->id,
            'registration_date' => now()->toDateString(),
            'registration_type' => 'New',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('admin.students.transfer', $student),
            ['section_id' => $newSection->id]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('students', [
            'id'         => $student->id,
            'section_id' => $newSection->id,
        ]);
    }

    public function test_transfer_rejects_section_from_different_class()
    {
        $otherClass = SchoolClass::factory()->create([
            'grade_id'        => $this->grade->id,
            'academic_year_id'=> $this->academicYear->id,
        ]);
        $otherSection = Section::factory()->create(['class_id' => $otherClass->id]);

        $student = Student::factory()->create([
            'class_id'   => $this->class->id,
            'section_id' => $this->section->id,
            'grade_id'   => $this->grade->id,
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('admin.students.transfer', $student),
            ['section_id' => $otherSection->id]
        );

        $response->assertSessionHasErrors('section_id');
        $this->assertDatabaseHas('students', [
            'id'         => $student->id,
            'section_id' => $this->section->id, // unchanged
        ]);
    }

    // ───────────────────────────────────────────
    // حذف طالب
    // ───────────────────────────────────────────

    public function test_admin_can_delete_student()
    {
        $student = Student::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.students.destroy', $student));

        $response->assertRedirect(route('admin.students.index'));
        $this->assertSoftDeleted('students', ['id' => $student->id]);
    }

    // ───────────────────────────────────────────
    // صلاحيات
    // ───────────────────────────────────────────

    public function test_guest_cannot_access_students_list()
    {
        $response = $this->get(route('admin.students.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_students_list()
    {
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $teacher = User::factory()->create();
        $teacher->assignRole($teacherRole);

        $response = $this->actingAs($teacher)->get(route('admin.students.index'));

        $response->assertStatus(403);
    }
}
