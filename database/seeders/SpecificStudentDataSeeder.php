<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\StudentSubjectGrade;
use App\Models\AcademicYear;
use App\Models\Semester;
use Carbon\Carbon;

class SpecificStudentDataSeeder extends Seeder
{
    public function run(): void
    {
        $parent = ParentModel::where('full_name', 'like', '%علي حسن طارق حرارة%')->first();
        if (!$parent) {
            $this->command->error("Parent not found.");
            return;
        }

        $students = $parent->students;
        if ($students->isEmpty()) {
            $this->command->error("No students found for this parent.");
            return;
        }

        $academicYear = AcademicYear::where('status', true)->first();
        $semester = Semester::where('academic_year_id', $academicYear->id)->where('status', true)->first();

        $subjects = Subject::all();
        $teachers = Teacher::all();

        // أيام الأسبوع الدراسية: السبت → الخميس
        $daysOfWeek = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];

        foreach ($students as $student) {
            $this->command->info("جاري توليد البيانات لـ: {$student->first_name}");

            // ── 1. جدول دراسي للشعبة (بدون مواعيد) ──────────────
            if (Timetable::where('section_id', $student->section_id)->count() == 0) {
                $period = 1;
                foreach ($daysOfWeek as $day) {
                    foreach ($subjects->take(7) as $subject) {
                        $teacher = $teachers->random();
                        Timetable::create([
                            'academic_year_id' => $academicYear->id,
                            'semester_id'      => $semester->id,
                            'grade_id'         => $student->grade_id,
                            'class_id'         => $student->class_id,
                            'section_id'       => $student->section_id,
                            'subject_id'       => $subject->id,
                            'teacher_id'       => $teacher->id,
                            'day_of_week'      => $day,
                            'period_number'    => $period,
                            'start_time'       => '08:00:00',
                            'end_time'         => '08:45:00',
                            'status'           => 1,
                        ]);
                        $period++;
                    }
                }
            }

            // ── 2. اختبارات ونتائج ───────────────────────────────
            $examTypes = [
                ['name' => 'اختبار نصفي',   'type' => 'midterm', 'marks' => 30],
                ['name' => 'اختبار نهائي',   'type' => 'final',   'marks' => 50],
                ['name' => 'اختبار قصير 1', 'type' => 'quiz',    'marks' => 10],
                ['name' => 'اختبار قصير 2', 'type' => 'quiz',    'marks' => 10],
            ];

            foreach ($subjects->take(6) as $subject) {
                // كل مادة يكون لها معلم ثابت يضع الاختبار
                $teacher = $teachers->random();
                $totalSubjectMarks = 0;
                $totalPossible = 0;

                foreach ($examTypes as $examDef) {
                    // الاختبار القصير الثاني بتاريخ اليوم، النصفي والنهائي بتواريخ سابقة منطقية
                    $examDate = match($examDef['type']) {
                        'quiz'    => Carbon::today(),                   // اليوم
                        'midterm' => Carbon::today()->subWeeks(6),      // قبل 6 أسابيع
                        'final'   => Carbon::today()->subWeeks(2),      // قبل أسبوعين
                        default   => Carbon::today()->subDays(rand(1, 20)),
                    };

                    $exam = Exam::firstOrCreate(
                        [
                            'title'           => "{$examDef['name']} - {$subject->name}",
                            'type'            => $examDef['type'],
                            'academic_year_id' => $academicYear->id,
                            'semester_id'     => $semester->id,
                            'grade_id'        => $student->grade_id,
                            'class_id'        => $student->class_id,
                            'subject_id'      => $subject->id,
                            'teacher_id'      => $teacher->id, // المعلم صاحب الاختبار
                        ],
                        [
                            'exam_date'               => $examDate,
                            'start_time'              => '09:00:00',
                            'end_time'                => '10:00:00',
                            'duration_minutes'        => 60,
                            'total_marks'             => $examDef['marks'],
                            'show_marks_to_student'   => true,
                            'show_answers_to_student' => false,
                            'status'                  => 'published',
                        ]
                    );

                    $marksObtained = rand((int)($examDef['marks'] * 0.6), $examDef['marks']);
                    $totalSubjectMarks += $marksObtained;
                    $totalPossible += $examDef['marks'];

                    ExamResult::updateOrCreate(
                        [
                            'exam_id'    => $exam->id,
                            'student_id' => $student->id,
                        ],
                        [
                            'marks_obtained'    => $marksObtained,
                            'total_marks'       => $examDef['marks'],
                            'percentage'        => round(($marksObtained / $examDef['marks']) * 100, 2),
                            'attendance_status' => 'present',
                            'submitted_at'      => $examDate,
                            'graded_at'         => $examDate,
                        ]
                    );
                }

                // ── 3. درجات المادة الإجمالية للشهادة ───────────
                $percentage = $totalPossible > 0 ? ($totalSubjectMarks / $totalPossible) * 100 : 0;
                $letterGrade = match(true) {
                    $percentage >= 90 => 'A',
                    $percentage >= 80 => 'B',
                    $percentage >= 70 => 'C',
                    $percentage >= 60 => 'D',
                    default           => 'F',
                };

                StudentSubjectGrade::updateOrCreate(
                    [
                        'student_id'      => $student->id,
                        'subject_id'      => $subject->id,
                        'academic_year_id' => $academicYear->id,
                        'semester_id'     => $semester->id,
                    ],
                    [
                        'section_id'      => $student->section_id,
                        'total_percentage' => round($percentage, 2),
                        'letter_grade'    => $letterGrade,
                        'is_passing'      => $percentage >= 50,
                        'is_finalized'    => true,
                        'calculated_at'   => Carbon::now(),
                    ]
                );
            }
        }

        $this->command->info("✅ تم توليد البيانات بنجاح للطلاب الثلاثة!");
    }
}
