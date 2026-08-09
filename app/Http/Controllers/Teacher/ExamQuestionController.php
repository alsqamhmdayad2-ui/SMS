<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Enums\ExamStatus;
use App\Enums\QuestionType;
use App\Enums\QuestionDifficulty;
use App\Enums\BloomLevel;
use App\Services\ExamBuilderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Timetable;

class ExamQuestionController extends Controller
{
    public function __construct(protected ExamBuilderService $builderService) {}

    protected function getTeacher()
    {
        return Auth::user()?->teacher;
    }

    protected function getTeacherSectionIds($teacher): array
    {
        if (!$teacher) return [];
        return Timetable::where('teacher_id', $teacher->id)
            ->pluck('section_id')->unique()->values()->toArray();
    }

    protected function getTeacherSubjectIds($teacher): array
    {
        if (!$teacher) return [];
        return Timetable::where('teacher_id', $teacher->id)
            ->pluck('subject_id')->unique()->values()->toArray();
    }

    protected function authorizeTeacherExam(Exam $exam): void
    {
        $teacher    = $this->getTeacher();
        $sectionIds = $this->getTeacherSectionIds($teacher);
        $subjectIds = $this->getTeacherSubjectIds($teacher);

        if (!in_array($exam->section_id, $sectionIds) || !in_array($exam->subject_id, $subjectIds)) {
            abort(403, 'غير مصرح لك بالوصول لهذا الاختبار.');
        }
    }

    /**
     * Display the full exam builder (question list + add form)
     */
    public function index(Exam $exam)
    {
        $this->authorizeTeacherExam($exam);

        $exam->load([
            'questions' => fn($q) => $q->with('options')->orderBy('exam_question.display_order'),
            'subject',
            'section.schoolClass.grade',
            'academicYear',
            'semester',
        ]);

        $questionTypes = QuestionType::cases();
        $difficulties  = QuestionDifficulty::cases();
        $bloomLevels   = BloomLevel::cases();

        return view('panels.teacher.exams.questions', compact(
            'exam', 'questionTypes', 'difficulties', 'bloomLevels'
        ));
    }

    /**
     * Store a new question
     */
    public function store(Request $request, Exam $exam)
    {
        $this->authorizeTeacherExam($exam);

        try {
            $data = $request->all();
            $question = $this->builderService->addQuestion($exam, $data);

            if ($request->wantsJson()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'تم إضافة السؤال بنجاح.',
                    'question' => $question,
                ]);
            }
            return back()->with('success', 'تم إضافة السؤال بنجاح.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update an existing question
     */
    public function update(Request $request, Exam $exam, Question $question)
    {
        $this->authorizeTeacherExam($exam);

        try {
            $updated = $this->builderService->updateQuestion($exam, $question, $request->all());

            if ($request->wantsJson()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'تم تحديث السؤال بنجاح.',
                    'question' => $updated,
                ]);
            }
            return back()->with('success', 'تم تحديث السؤال بنجاح.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Duplicate a question within the exam
     */
    public function duplicate(Request $request, Exam $exam, Question $question)
    {
        $this->authorizeTeacherExam($exam);

        try {
            $clone = $this->builderService->duplicateQuestion($exam, $question);

            if ($request->wantsJson()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'تم تكرار السؤال بنجاح.',
                    'question' => $clone,
                ]);
            }
            return back()->with('success', 'تم تكرار السؤال بنجاح.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reorder questions (AJAX)
     */
    public function reorder(Request $request, Exam $exam)
    {
        $this->authorizeTeacherExam($exam);

        $request->validate(['ordered_ids' => 'required|array', 'ordered_ids.*' => 'required|exists:questions,id']);

        try {
            $this->builderService->reorderQuestions($exam, $request->ordered_ids);
            return response()->json(['success' => true, 'message' => 'تم إعادة الترتيب.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get bank questions for import modal (AJAX)
     */
    public function getBank(Request $request, Exam $exam)
    {
        $this->authorizeTeacherExam($exam);

        try {
            $filters   = $request->only(['difficulty', 'type', 'search']);
            $questions = $this->builderService->getBankQuestions($exam->subject_id, $filters);
            return response()->json(['success' => true, 'questions' => $questions]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Import a question from the bank
     */
    public function import(Request $request, Exam $exam)
    {
        $this->authorizeTeacherExam($exam);

        $request->validate([
            'question_id'  => 'required|exists:questions,id',
            'mark_override' => 'nullable|numeric|min:0.5',
        ]);

        try {
            $question = Question::findOrFail($request->question_id);
            $this->builderService->importFromBank($exam, $question, $request->mark_override);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'تم استيراد السؤال بنجاح.']);
            }
            return back()->with('success', 'تم استيراد السؤال بنجاح.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove (detach) a question from the exam
     */
    public function destroy(Request $request, Exam $exam, Question $question)
    {
        $this->authorizeTeacherExam($exam);

        try {
            $this->builderService->removeQuestion($exam, $question);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'تم حذف السؤال من الاختبار.']);
            }
            return back()->with('success', 'تم حذف السؤال.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }
}
