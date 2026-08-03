<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Services\ExamBuilderService;
use App\Http\Requests\StoreQuestionRequest;
use Illuminate\Http\Request;

use App\Http\Requests\UpdateQuestionRequest;

class QuestionController extends Controller
{
    public function __construct(
        protected ExamBuilderService $builderService
    ) {}

    public function store(StoreQuestionRequest $request, Exam $exam)
    {
        try {
            $question = $this->builderService->addQuestion($exam, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم إضافة السؤال بنجاح.',
                    'question' => $question,
                ]);
            }
            
            return back()->with('success', 'تم إضافة السؤال بنجاح.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to add question');
        }
    }

    public function update(UpdateQuestionRequest $request, Exam $exam, Question $question)
    {
        try {
            $updatedQuestion = $this->builderService->updateQuestion($exam, $question, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث السؤال بنجاح.',
                    'question' => $updatedQuestion,
                ]);
            }
            
            return back()->with('success', 'تم تحديث السؤال بنجاح.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to update question');
        }
    }

    public function duplicate(Request $request, Exam $exam, Question $question)
    {
        try {
            $newQuestion = $this->builderService->duplicateQuestion($exam, $question);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تكرار السؤال بنجاح.',
                    'question' => $newQuestion,
                ]);
            }
            
            return back()->with('success', 'تم تكرار السؤال بنجاح.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to duplicate question');
        }
    }

    public function import(Request $request, Exam $exam)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'mark_override' => 'nullable|numeric|min:0.5',
        ]);

        try {
            $question = Question::findOrFail($request->question_id);
            $this->builderService->importFromBank($exam, $question, $request->mark_override);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم استيراد السؤال بنجاح.',
                ]);
            }
            
            return back()->with('success', 'تم استيراد السؤال بنجاح.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to import question');
        }
    }

    public function reorder(Request $request, Exam $exam)
    {
        $request->validate([
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'required|exists:questions,id',
        ]);

        try {
            $this->builderService->reorderQuestions($exam, $request->ordered_ids);

            return response()->json([
                'success' => true,
                'message' => 'Questions reordered successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getBank(Request $request, Exam $exam)
    {
        try {
            $filters = $request->only(['difficulty', 'type', 'search', 'tag_id']);
            $questions = $this->builderService->getBankQuestions($exam->subject_id, $filters);

            return response()->json([
                'success' => true,
                'questions' => $questions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, Exam $exam, Question $question)
    {
        try {
            $this->builderService->removeQuestion($exam, $question);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Question removed from exam successfully'
                ]);
            }

            return back()->with('success', 'تم إزالة السؤال من الاختبار بنجاح.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to remove question');
        }
    }
}
