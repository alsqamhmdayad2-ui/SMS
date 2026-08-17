<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Teacher;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\StudentSubjectGrade;
use App\Models\ResultPublication;
use App\Services\ResultPublicationService;
use Illuminate\Validation\ValidationException;

class ResultPublicationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $teacher;
    protected $academicYear;
    protected $semester;
    protected $grade;
    protected $schoolClass;
    protected $section;
    protected $subject;
    protected $student;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@a.com', 'password' => 'password']);
        $this->teacher = Teacher::create([
            'first_name' => 'Test',
            'father_name' => 'Teacher',
            'family_name' => 'One',
            'national_id' => 'TEST-TEACHER-002',
        ]);
        $this->academicYear = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-09-01', 'end_date' => '2027-06-01', 'status' => true]);
        $this->semester = Semester::create(['name' => 'Semester 1', 'academic_year_id' => $this->academicYear->id, 'start_date' => '2026-09-01', 'end_date' => '2027-01-01', 'status' => true]);
        $this->grade = Grade::create(['name' => 'Grade 10']);
        $this->schoolClass = SchoolClass::create(['name' => 'A', 'grade_id' => $this->grade->id]);
        $this->section = Section::create(['name' => 'A1', 'class_id' => $this->schoolClass->id]);
        $this->subject = Subject::create(['name' => 'Math', 'code' => 'MTH', 'grade_id' => $this->grade->id, 'type' => 'core']);
        
        $user = User::create(['name' => 'Student', 'email' => 's@s.com', 'password' => 'password']);
        $this->student = Student::create([
            'user_id' => $user->id,
            'student_number' => 'STU-TEST-001',
            'first_name' => 'Student',
            'father_name' => 'Test',
            'grandfather_name' => 'Grand',
            'family_name' => 'User',
            'email' => 's@s.com',
            'phone' => '123456',
            'date_of_birth' => '2010-01-01',
            'section_id' => $this->section->id,
            'grade_id' => $this->grade->id,
        ]);

        $this->service = app(ResultPublicationService::class);
    }

    public function test_cannot_publish_if_marks_missing()
    {
        $exam = Exam::create([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'grade_id' => $this->grade->id,
            'class_id' => $this->schoolClass->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Quiz 1',
            'type' => 'quiz',
            'exam_date' => '2026-10-10',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'duration_minutes' => 60,
            'status' => 'draft',
            'total_marks' => 100,
        ]);
        $exam->sections()->attach($this->section->id);

        // Note: No ExamResult created for the student

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing marks for student');

        $this->service->publish([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => null,
            'grade_id' => $this->grade->id,
            'section_id' => $this->section->id,
            'subject_id' => $this->subject->id,
            'published_type' => 'subject',
        ], $this->admin->id);
    }

    public function test_can_publish_if_all_validations_pass()
    {
        $exam = Exam::create([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'grade_id' => $this->grade->id,
            'class_id' => $this->schoolClass->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Quiz 1',
            'type' => 'quiz',
            'exam_date' => '2026-10-10',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'duration_minutes' => 60,
            'status' => 'draft',
            'total_marks' => 100,
        ]);
        $exam->sections()->attach($this->section->id);

        ExamResult::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'attendance_status' => 'present',
            'marks_obtained' => 90,
            'total_marks' => 100,
        ]);

        StudentSubjectGrade::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'subject_id' => $this->subject->id,
            'section_id' => $this->section->id,
        ]);

        $publication = $this->service->publish([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => null,
            'grade_id' => $this->grade->id,
            'section_id' => $this->section->id,
            'subject_id' => $this->subject->id,
            'published_type' => 'subject',
        ], $this->admin->id);

        $this->assertEquals('published', $publication->status);
        $this->assertTrue($this->service->canViewResult($this->student, $this->academicYear->id, null, $this->subject->id));
    }
}
