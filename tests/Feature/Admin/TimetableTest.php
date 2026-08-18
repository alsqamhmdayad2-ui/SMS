<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TimetableTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $academicYear;
    protected $semester;
    protected $grade;
    protected $class;
    protected $section;
    protected $subject;
    protected $teacher;

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
        $this->section = Section::factory()->create(['class_id' => $this->class->id]);
        
        $this->subject = Subject::factory()->create();
        $this->teacher = Teacher::factory()->create();
        
        // Link teacher to subject and section
        \Illuminate\Support\Facades\DB::table('subject_section_teacher')->insert([
            'academic_year_id' => $this->academicYear->id,
            'subject_id' => $this->subject->id,
            'section_id' => $this->section->id,
            'teacher_id' => $this->teacher->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function test_admin_can_view_timetables_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.timetables.index', ['section_id' => $this->section->id]));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.timetables.index');
        $response->assertViewHas('selectedSection');
    }

    public function test_admin_can_save_timetable()
    {
        $data = [
            'section_id' => $this->section->id,
            'schedule' => [
                'Sunday' => [
                    1 => $this->subject->id,
                    2 => null,
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.timetables.save'), $data);

        $response->assertRedirect(route('admin.timetables.index', ['section_id' => $this->section->id]));
        $this->assertDatabaseHas('timetables', [
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'section_id' => $this->section->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'day_of_week' => 'Sunday',
            'period_number' => 1
        ]);
    }

    public function test_timetable_detects_teacher_conflict()
    {
        // Add existing timetable for teacher in ANOTHER section
        $otherSection = Section::factory()->create(['class_id' => $this->class->id]);
        \App\Models\Timetable::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'section_id' => $otherSection->id,
            'teacher_id' => $this->teacher->id,
            'day_of_week' => 'Monday',
            'period_number' => 3
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.timetables.check_conflict'), [
            'teacher_id' => $this->teacher->id,
            'day_of_week' => 'Monday',
            'period_number' => 3,
            'section_id' => $this->section->id
        ]);

        $response->assertStatus(200);
        $response->assertJson(['hasConflict' => true]);
    }

    public function test_admin_can_auto_generate_timetable()
    {
        // Add weekly_periods to the class_subject_teacher pivot so generation can work
        \Illuminate\Support\Facades\DB::table('class_subject_teacher')->insert([
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'weekly_periods' => 3,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.timetables.generate'), [
            'section_id' => $this->section->id
        ]);

        $response->assertRedirect(route('admin.timetables.index', ['section_id' => $this->section->id]));
        // The generator might not generate everything if constraints fail, 
        // but it shouldn't error out.
        $response->assertSessionHas('success');
    }
}
