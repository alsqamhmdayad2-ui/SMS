<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;

class ExamController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'حساب الطالب غير مكتمل البيانات.');
        }

        // All exams for student's section
        $allExams = \App\Models\Exam::with('subject')
            ->where('section_id', $student->section_id)
            ->whereIn('status', ['published', 'locked'])
            ->orderBy('exam_date', 'desc')
            ->get();

        // Get student's results
        $results = \App\Models\ExamResult::where('student_id', $student->id)
            ->get()
            ->keyBy('exam_id');

        $availableExams = [];
        $completedExams = [];

        foreach ($allExams as $exam) {
            if ($results->has($exam->id)) {
                $completedExams[] = [
                    'exam' => $exam,
                    'result' => $results->get($exam->id)
                ];
            } else {
                if ($exam->status->value === 'published') {
                    $availableExams[] = $exam;
                }
            }
        }

        return view('panels.student.exams', compact('student', 'availableExams', 'completedExams'));
    }

    public function take(\App\Models\Exam $exam)
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        // Validate if student is in the exam's section
        if ($exam->section_id !== $student->section_id) {
            return redirect()->route('student.exams')->with('error', 'ليس لديك صلاحية لهذا الاختبار.');
        }

        // Check if exam is published
        if ($exam->status->value !== 'published') {
            return redirect()->route('student.exams')->with('error', 'الاختبار غير متاح حالياً.');
        }

        // Check if already taken
        $alreadyTaken = \App\Models\ExamResult::where('exam_id', $exam->id)
            ->where('student_id', $student->id)->exists();

        if ($alreadyTaken) {
            return redirect()->route('student.exams')->with('error', 'لقد قمت بتقديم هذا الاختبار مسبقاً.');
        }

        // Load questions and options
        $exam->load('questions.options');

        return view('panels.student.exams.take', compact('exam', 'student'));
    }

    public function submit(\Illuminate\Http\Request $request, \App\Models\Exam $exam)
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        // Security checks
        if ($exam->section_id !== $student->section_id || $exam->status->value !== 'published') {
            return redirect()->route('student.exams')->with('error', 'لا يمكنك تسليم هذا الاختبار.');
        }

        if (\App\Models\ExamResult::where('exam_id', $exam->id)->where('student_id', $student->id)->exists()) {
            return redirect()->route('student.exams')->with('error', 'لقد قمت بتقديم هذا الاختبار مسبقاً.');
        }

        $answers = $request->input('answers', []);
        $totalMarks = 0;
        
        // Prepare data for insertion and auto-grading
        $exam->load('questions.options');
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($exam, $student, $answers, &$totalMarks) {
            $examResult = \App\Models\ExamResult::create([
                'exam_id'           => $exam->id,
                'student_id'        => $student->id,
                'marks_obtained'    => 0, // Will update after loop
                'total_marks'       => $exam->total_marks,
                'attendance_status' => 'present',
                'submitted_at'      => now(),
                'remarks'           => null,
            ]);

            foreach ($exam->questions as $question) {
                $studentAnswer = $answers[$question->id] ?? null;
                $marksAwarded = 0;
                $isCorrect = false;
                $isGraded = true; // Default true except for essays
                $questionOptionId = null;
                $textAnswer = null;

                if ($question->type === 'mcq' || $question->type === 'true_false') {
                    if ($studentAnswer) {
                        $questionOptionId = $studentAnswer;
                        $correctOption = $question->options->where('is_correct', true)->first();
                        if ($correctOption && (string)$correctOption->id === (string)$studentAnswer) {
                            $isCorrect = true;
                            $marksAwarded = $question->marks;
                        }
                    }
                } elseif ($question->type === 'short_answer' || $question->type === 'fill_blank') {
                    $textAnswer = $studentAnswer;
                    if (trim(strtolower($textAnswer)) === trim(strtolower($question->correct_answer))) {
                        $isCorrect = true;
                        $marksAwarded = $question->marks;
                    }
                } elseif ($question->type === 'essay') {
                    $textAnswer = $studentAnswer;
                    $isGraded = false; // Requires manual teacher grading
                    $marksAwarded = 0;
                }

                $totalMarks += $marksAwarded;

                \Illuminate\Support\Facades\DB::table('exam_student_answers')->insert([
                    'exam_result_id' => $examResult->id,
                    'student_id' => $student->id,
                    'exam_id' => $exam->id,
                    'question_id' => $question->id,
                    'question_option_id' => $questionOptionId,
                    'text_answer' => $textAnswer,
                    'marks_awarded' => $marksAwarded,
                    'is_correct' => $isCorrect,
                    'is_graded' => $isGraded,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $percentage = $exam->total_marks > 0
                ? round(($totalMarks / $exam->total_marks) * 100, 2)
                : 0;
            $examResult->update([
                'marks_obtained' => $totalMarks,
                'percentage'     => $percentage,
            ]);
            
            // Mark attendance
            $session = \App\Models\AttendanceSession::firstOrCreate(
                [
                    'session_date' => today(),
                    'section_id' => $student->section_id,
                    'subject_id' => $exam->subject_id,
                ],
                ['recorded_by' => $exam->teacher_id ?? 1]
            );
            
            \App\Models\AttendanceRecord::updateOrCreate(
                [
                    'attendance_session_id' => $session->id,
                    'student_id' => $student->id,
                ],
                ['status' => 'present']
            );
        });

        return redirect()->route('student.exams')->with('success', 'تم تسليم الاختبار بنجاح. ' . ($totalMarks > 0 ? 'حصلت على ' . $totalMarks . ' درجة (بانتظار تصحيح المقالي إن وجد).' : ''));
    }
}
