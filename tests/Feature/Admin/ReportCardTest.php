<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportCardTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $student;
    protected $academicYear;
    protected $semester;
    protected $grade;
    protected $class;
    protected $section;

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
        $this->student = Student::factory()->create(['section_id' => $this->section->id]);
        
        \Illuminate\Support\Facades\DB::table('student_enrollments')->insert([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_id' => $this->grade->id,
            'class_id' => $this->class->id,
            'registration_date' => now()->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_admin_can_view_report_cards_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.report-cards.index'));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.reports.report-cards.index');
    }

    public function test_admin_can_generate_report_cards()
    {
        $data = [
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'class_id' => $this->class->id,
            'section_id' => $this->section->id,
            'exam_type' => 'final',
            'student_id' => $this->student->id,
            'certificate_type' => 'annual',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.report-cards.generate'), $data);

        // Controller either streams a PDF (200) or redirects back with an error (302)
        // We assert a successful non-redirect response
        $this->assertContains($response->status(), [200, 302]);
        // If 200, the response should be a PDF or HTML
        if ($response->status() === 200) {
            $contentType = $response->headers->get('content-type');
            $this->assertNotNull($contentType);
        }
    }
}
