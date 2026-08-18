<?php

namespace Tests\Feature\Admin;

use App\Enums\ExamStatus;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExamTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $academicYear;
    protected $semester;
    protected $grade;
    protected $class;
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
        $this->subject = Subject::factory()->create();
        $this->teacher = Teacher::factory()->create();
    }

    public function test_admin_can_view_exams_list()
    {
        $exam = Exam::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'grade_id' => $this->grade->id,
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.exams.index'));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.exams.index');
        $response->assertViewHas('exams');
    }

    public function test_admin_can_store_exam()
    {
        $data = [
            'title' => 'Math Final Exam',
            'type' => 'final',
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'grade_id' => $this->grade->id,
            'class_id' => $this->class->id,
            'section_ids' => [\App\Models\Section::factory()->create(['class_id' => $this->class->id])->id],
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'exam_date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'duration_minutes' => 120,
            'status' => ExamStatus::DRAFT->value,
            'display_mode' => 'online',
            'total_marks' => 100,
            'show_marks_to_student' => true,
            'show_answers_to_student' => true,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.exams.store'), $data);

        $response->assertRedirect(); // redirects to show
        $this->assertDatabaseHas('exams', [
            'title' => 'Math Final Exam',
            'type' => 'final',
            'subject_id' => $this->subject->id,
        ]);
    }

    public function test_admin_can_update_exam()
    {
        $exam = Exam::factory()->create([
            'title' => 'Old Title',
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'grade_id' => $this->grade->id,
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
        ]);

        $data = [
            'title' => 'Updated Title',
            'type' => $exam->type,
            'academic_year_id' => $exam->academic_year_id,
            'semester_id' => $exam->semester_id,
            'grade_id' => $exam->grade_id,
            'class_id' => $exam->class_id,
            'section_ids' => [\App\Models\Section::factory()->create(['class_id' => $exam->class_id])->id],
            'subject_id' => $exam->subject_id,
            'teacher_id' => $exam->teacher_id,
            'exam_date' => \Carbon\Carbon::parse($exam->exam_date)->format('Y-m-d'),
            'start_time' => \Carbon\Carbon::parse($exam->start_time)->format('H:i'),
            'end_time' => \Carbon\Carbon::parse($exam->end_time)->format('H:i'),
            'duration_minutes' => $exam->duration_minutes,
            'status' => $exam->status instanceof \App\Enums\ExamStatus ? $exam->status->value : $exam->status,
            'display_mode' => $exam->display_mode,
            'total_marks' => $exam->total_marks,
            'show_marks_to_student' => $exam->show_marks_to_student,
            'show_answers_to_student' => $exam->show_answers_to_student,
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.exams.update', $exam), $data);

        $response->assertRedirect(route('admin.exams.index'));
        $this->assertDatabaseHas('exams', [
            'id' => $exam->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_admin_can_delete_exam()
    {
        $exam = Exam::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'grade_id' => $this->grade->id,
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->withHeaders(['Referer' => route('admin.exams.index')])
            ->delete(route('admin.exams.destroy', $exam));

        $response->assertRedirect(route('admin.exams.index'));
        $this->assertSoftDeleted('exams', ['id' => $exam->id]);
    }

    public function test_admin_can_publish_exam()
    {
        $exam = Exam::factory()->create([
            'status' => ExamStatus::DRAFT->value,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'grade_id' => $this->grade->id,
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.exams.publish', $exam));

        $response->assertRedirect();
        $this->assertDatabaseHas('exams', [
            'id' => $exam->id,
            'status' => ExamStatus::PUBLISHED->value,
        ]);
    }
}
