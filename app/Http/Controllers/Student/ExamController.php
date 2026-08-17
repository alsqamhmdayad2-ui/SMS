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
            ->whereHas('sections', function ($q) use ($student) {
                $q->where('sections.id', $student->section_id);
            })
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
        if (!$exam->sections->contains($student->section_id)) {
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

        // Manage exam session
        $examSession = \App\Models\ExamSession::firstOrCreate(
            ['exam_id' => $exam->id, 'student_id' => $student->id],
            ['started_at' => now()]
        );

        // Check if already submitted in session
        if ($examSession->submitted_at) {
            return redirect()->route('student.exams')->with('error', 'لقد قمت بتسليم هذا الاختبار مسبقاً.');
        }

        // Calculate remaining time
        $durationSeconds = ($exam->duration_minutes ?? 60) * 60;
        $elapsedSeconds = $examSession->started_at->diffInSeconds(now());
        $remainingSeconds = max(0, $durationSeconds - $elapsedSeconds);

        // ── Time expired → auto-submit from draft_answers ──
        if ($remainingSeconds <= 0 && $exam->duration_minutes > 0) {
            $draftAnswers = $examSession->draft_answers ?? [];

            if (empty($draftAnswers)) {
                // No saved answers → show expired page (no result to submit)
                return view('panels.student.exams.expired', compact('exam', 'student'));
            }

            // Auto-submit using the saved draft
            $this->processSubmit($exam, $student, $user, $draftAnswers);
            $examSession->update(['submitted_at' => now()]);

            return redirect()->route('student.exams')
                ->with('warning', 'انتهى وقت الاختبار! تم تسليم إجاباتك المحفوظة تلقائياً.');
        }

        return view('panels.student.exams.take', compact('exam', 'student', 'remainingSeconds'));
    }

    /**
     * AJAX endpoint: save draft answers silently in background.
     */
    public function autoSave(\Illuminate\Http\Request $request, \App\Models\Exam $exam)
    {
        $user    = auth()->user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        // Security: must be in exam section and exam must be published
        if (!$exam->sections->contains($student->section_id) || $exam->status->value !== 'published') {
            return response()->json(['status' => 'error', 'message' => 'غير مصرح'], 403);
        }

        // Must not be already submitted
        $alreadyTaken = \App\Models\ExamResult::where('exam_id', $exam->id)
            ->where('student_id', $student->id)->exists();
        if ($alreadyTaken) {
            return response()->json(['status' => 'already_submitted'], 200);
        }

        $answers = $request->input('answers', []);

        \App\Models\ExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->update(['draft_answers' => json_encode($answers)]);

        return response()->json(['status' => 'saved', 'saved_at' => now()->toTimeString()]);
    }

    /**
     * Shared grading logic used by both submit() and auto-submit-on-expiry.
     */
    private function processSubmit(\App\Models\Exam $exam, $student, $user, array $answers): void
    {
        if (\App\Models\ExamResult::where('exam_id', $exam->id)->where('student_id', $student->id)->exists()) {
            return; // Already submitted, do nothing
        }

        $exam->load('questions.options');
        $totalMarks = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($exam, $student, $user, $answers, &$totalMarks) {
            $examResult = \App\Models\ExamResult::create([
                'exam_id'           => $exam->id,
                'student_id'        => $student->id,
                'marks_obtained'    => 0,
                'total_marks'       => $exam->total_marks,
                'attendance_status' => 'present',
                'submitted_at'      => now(),
                'remarks'           => null,
            ]);

            foreach ($exam->questions as $question) {
                $studentAnswer  = $answers[$question->id] ?? null;
                $marksAwarded   = 0;
                $isCorrect      = false;
                $isGraded       = true;
                $questionOptionId = null;
                $textAnswer     = null;

                if ($question->type->value === 'mcq' || $question->type->value === 'true_false') {
                    if ($studentAnswer) {
                        $questionOptionId = $studentAnswer;
                        $correctOption = $question->options->where('is_correct', true)->first();
                        if ($correctOption && (string)$correctOption->id === (string)$studentAnswer) {
                            $isCorrect    = true;
                            $marksAwarded = $question->marks;
                        }
                    }
                } elseif (in_array($question->type->value, ['short_answer', 'fill_blank'])) {
                    $textAnswer = $studentAnswer;
                    $studentTextLower = trim(mb_strtolower($textAnswer, 'UTF-8'));
                    foreach ($question->options->where('is_correct', true) as $option) {
                        if (trim(mb_strtolower($option->option_text, 'UTF-8')) === $studentTextLower) {
                            $isCorrect    = true;
                            $marksAwarded = $question->marks;
                            break;
                        }
                    }
                } elseif ($question->type->value === 'essay') {
                    $textAnswer = $studentAnswer;
                    $isGraded   = false;
                } elseif ($question->type->value === 'matching') {
                    if (is_array($studentAnswer)) {
                        $textAnswer  = json_encode($studentAnswer, JSON_UNESCAPED_UNICODE);
                        $totalPairs  = $question->options->count();
                        $correctPairs = 0;
                        foreach ($question->options as $option) {
                            if (isset($studentAnswer[$option->id]) && $studentAnswer[$option->id] == $option->right_item) {
                                $correctPairs++;
                                $marksAwarded += $option->partial_mark ?? ($question->marks / $totalPairs);
                            }
                        }
                        if ($correctPairs === $totalPairs) $isCorrect = true;
                    }
                }

                $totalMarks += $marksAwarded;

                \Illuminate\Support\Facades\DB::table('exam_student_answers')->insert([
                    'exam_result_id'     => $examResult->id,
                    'student_id'         => $student->id,
                    'exam_id'            => $exam->id,
                    'question_id'        => $question->id,
                    'question_option_id' => $questionOptionId,
                    'text_answer'        => $textAnswer,
                    'marks_awarded'      => $marksAwarded,
                    'is_correct'         => $isCorrect,
                    'is_graded'          => $isGraded,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            $percentage = $exam->total_marks > 0
                ? round(($totalMarks / $exam->total_marks) * 100, 2)
                : 0;

            $examResult->update([
                'marks_obtained' => $totalMarks,
                'percentage'     => $percentage,
            ]);

            // Mark session as submitted
            \App\Models\ExamSession::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->update(['submitted_at' => now()]);
        });
    }

    public function submit(\Illuminate\Http\Request $request, \App\Models\Exam $exam)
    {
        $user    = auth()->user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        // Security checks
        if (!$exam->sections->contains($student->section_id) || $exam->status->value !== 'published') {
            return redirect()->route('student.exams')->with('error', 'لا يمكنك تسليم هذا الاختبار.');
        }

        if (\App\Models\ExamResult::where('exam_id', $exam->id)->where('student_id', $student->id)->exists()) {
            return redirect()->route('student.exams')->with('error', 'لقد قمت بتقديم هذا الاختبار مسبقاً.');
        }

        $answers = $request->input('answers', []);

        $this->processSubmit($exam, $student, $user, $answers);

        $exam->load('questions');
        $totalMarks = \App\Models\ExamResult::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->value('marks_obtained') ?? 0;

        $successMessage = $exam->show_marks_to_student
            ? 'تم تسليم الاختبار بنجاح. حصلت على ' . $totalMarks . ' من ' . $exam->total_marks . ' درجة' . ($exam->questions->where('type.value', 'essay')->count() > 0 ? ' (بانتظار تصحيح المقالي).' : '.')
            : 'تم تسليم الاختبار بنجاح. ستظهر نتيجتك عند اعتمادها من المعلم.';

        return redirect()->route('student.exams')->with('success', $successMessage);
    }

    // ─── Review Exam Answers ──────────────────────────────────────────────────
    public function review(\App\Models\Exam $exam)
    {
        $user = auth()->user();
        $student = \App\Models\Student::where('user_id', $user->id)->first();
        if (!$student) {
            return redirect()->back()->with('error', 'غير مصرح لك.');
        }

        // Check if exam is completed
        $examResult = \App\Models\ExamResult::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$examResult) {
            return redirect()->route('student.exams')->with('error', 'لم تكمل هذا الاختبار.');
        }

        // Check teacher's permission
        if (!$exam->show_answers_to_student || !$exam->show_marks_to_student) {
            return redirect()->route('student.exams')->with('error', 'مراجعة الإجابات غير متاحة حالياً.');
        }

        $exam->load('questions.options');
        $answers = \Illuminate\Support\Facades\DB::table('exam_student_answers')
            ->where('exam_result_id', $examResult->id)
            ->get()
            ->keyBy('question_id');

        return view('panels.student.exams.review', compact('exam', 'student', 'examResult', 'answers'));
    }
}
