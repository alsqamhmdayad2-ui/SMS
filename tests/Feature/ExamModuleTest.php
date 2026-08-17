<?php

use App\Models\User;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed essential roles
    Role::firstOrCreate(['name' => 'admin']);
    
    // Seed default admin user
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    // Seed dummy academic entities
    $this->academicYear = AcademicYear::create([
        'name' => '2026/2027', 
        'start_date' => '2026-09-01',
        'end_date' => '2027-06-30',
        'status' => true
    ]);
    $this->semester = Semester::create([
        'name' => 'First Semester', 
        'academic_year_id' => $this->academicYear->id,
        'start_date' => '2026-09-01',
        'end_date' => '2027-01-15',
        'status' => true
    ]);
    $this->grade = Grade::create(['name' => 'Grade 10']);
    $this->schoolClass = SchoolClass::create(['name' => 'Class A', 'grade_id' => $this->grade->id]);
    $this->section = Section::create(['name' => 'Section 1', 'class_id' => $this->schoolClass->id]);
    $this->subject = Subject::create(['name' => 'Mathematics', 'code' => 'MATH101', 'status' => true]);
    $this->teacher = Teacher::create([
        'first_name' => 'John',
        'father_name' => 'Michael',
        'family_name' => 'Doe',
        'national_id' => 'TEST-TEACHER-001',
        'phone' => '12345678',
        'address' => 'Test Address',
        'specialization' => 'Math',
    ]);
});

test('admin can create a new exam with LMS parameters', function () {
    $this->withoutExceptionHandling();
    $response = $this->actingAs($this->admin)->post(route('admin.exams.store'), [
        'title' => 'Math Midterm',
        'type' => 'midterm',
        'academic_year_id' => $this->academicYear->id,
        'semester_id' => $this->semester->id,
        'grade_id' => $this->grade->id,
        'class_id' => $this->schoolClass->id,
        'section_ids' => [$this->section->id],
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->teacher->id,
        'exam_date' => '2026-07-10',
        'start_time' => '08:00',
        'end_time' => '09:30',
        'duration_minutes' => 90,
        'total_marks' => 100,

        'status' => 'draft',
        'instructions' => 'Please bring your own calculator.',
    ]);

    $response->assertSessionHasNoErrors();
    
    $exam = Exam::first();
    expect($exam)->not->toBeNull();
    expect($exam->title)->toBe('Math Midterm');
    expect($exam->duration_minutes)->toBe(90);
    expect($exam->status->value ?? $exam->status)->toBe('draft');
});

test('exam scheduling prevents section time conflict', function () {
    // Create first exam
    Exam::create([
        'title' => 'Math Midterm',
        'type' => 'midterm',
        'academic_year_id' => $this->academicYear->id,
        'semester_id' => $this->semester->id,
        'grade_id' => $this->grade->id,
        'class_id' => $this->schoolClass->id,
        'section_id' => $this->section->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->teacher->id,
        'exam_date' => '2026-07-10',
        'start_time' => '08:00',
        'end_time' => '09:30',
        'duration_minutes' => 90,

        'status' => 'draft',
    ]);

    // Attempt second exam at overlapping time for the same Section
    $response = $this->actingAs($this->admin)->post(route('admin.exams.store'), [
        'title' => 'Physics Midterm',
        'type' => 'midterm',
        'academic_year_id' => $this->academicYear->id,
        'semester_id' => $this->semester->id,
        'grade_id' => $this->grade->id,
        'class_id' => $this->schoolClass->id,
        'section_ids' => [$this->section->id],
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->teacher->id,
        'exam_date' => '2026-07-10',
        'start_time' => '09:00', // overlaps first exam (08:00 - 09:30)
        'end_time' => '10:00',
        'duration_minutes' => 60,
        'total_marks' => 100,

        'status' => 'draft',
    ]);


    $response->assertSessionHasErrors(['teacher_id']);
});

test('exam builder allows adding questions', function () {
    $exam = Exam::create([
        'title' => 'Math Midterm',
        'type' => 'midterm',
        'academic_year_id' => $this->academicYear->id,
        'semester_id' => $this->semester->id,
        'grade_id' => $this->grade->id,
        'class_id' => $this->schoolClass->id,
        'section_id' => $this->section->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->teacher->id,
        'exam_date' => '2026-07-10',
        'start_time' => '08:00',
        'end_time' => '09:30',
        'duration_minutes' => 90,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($this->admin)->postJson(route('admin.questions.store', $exam->id), [
        'type' => 'mcq',
        'question_text' => 'What is 2 + 2?',
        'mark' => 2.0,
        'difficulty' => 'easy',
        'bloom_level' => 'remember',
        'estimated_time' => 30,
        'is_public' => true,
        'options' => ['3', '4', '5'],
        'correct_option_index' => 1,
    ]);

    $response->assertOk()->assertJsonPath('success', true);

    $this->assertDatabaseHas('questions', [
        'question_text' => 'What is 2 + 2?',
        'mark' => 2.0,
        'difficulty' => 'easy',
    ]);
});

test('editing an imported question triggers clone-on-edit', function () {
    $exam1 = Exam::create([
        'title' => 'Math Exam 1',
        'type' => 'midterm',
        'academic_year_id' => $this->academicYear->id,
        'semester_id' => $this->semester->id,
        'grade_id' => $this->grade->id,
        'class_id' => $this->schoolClass->id,
        'section_id' => $this->section->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->teacher->id,
        'exam_date' => '2026-07-10',
        'start_time' => '08:00',
        'end_time' => '09:30',
        'status' => 'draft',
    ]);

    $exam2 = Exam::create([
        'title' => 'Math Exam 2',
        'type' => 'midterm',
        'academic_year_id' => $this->academicYear->id,
        'semester_id' => $this->semester->id,
        'grade_id' => $this->grade->id,
        'class_id' => $this->schoolClass->id,
        'section_id' => $this->section->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->teacher->id,
        'exam_date' => '2026-07-10',
        'start_time' => '10:00',
        'end_time' => '11:30',
        'status' => 'draft',
    ]);

    // Create a question in Bank first (exam_id = null, associated with subject_id)
    $question = Question::create([
        'subject_id' => $this->subject->id,
        'type' => 'true_false',
        'question_text' => 'Is math fun?',
        'mark' => 1.0,
        'difficulty' => 'medium',
        'question_code' => 'MATH-Q-1',
    ]);
    $question->options()->create(['option_text' => 'True', 'is_correct' => true]);

    // Link/Import to both exams
    $exam1->questions()->attach($question->id, ['display_order' => 1, 'source_type' => 'bank']);
    $exam2->questions()->attach($question->id, ['display_order' => 1, 'source_type' => 'bank']);

    expect($exam1->questions()->count())->toBe(1);
    expect($exam2->questions()->count())->toBe(1);

    // Now edit the question inside Exam 1 (which triggers Clone-on-Edit)
    $response = $this->actingAs($this->admin)->putJson(route('admin.questions.update', [$exam1->id, $question->id]), [
        'type' => 'true_false',
        'question_text' => 'Is Math REALLY fun?', // edited text
        'mark' => 1.5,
        'difficulty' => 'medium',
        'is_correct_boolean' => true,
    ]);

    $response->assertOk();

    // Verify Exam 1 got the cloned question
    $exam1Questions = $exam1->fresh()->questions;
    expect($exam1Questions->count())->toBe(1);
    $clonedQuestion = $exam1Questions->first();
    expect($clonedQuestion->id)->not->toBe($question->id);
    expect($clonedQuestion->question_text)->toBe('Is Math REALLY fun?');
    expect((float)$clonedQuestion->pivot->mark_override)->toBe(1.5);

    // Verify Exam 2 still points to the original question
    $exam2Questions = $exam2->fresh()->questions;
    expect($exam2Questions->count())->toBe(1);
    expect($exam2Questions->first()->id)->toBe($question->id);
    expect($exam2Questions->first()->question_text)->toBe('Is math fun?');
});
