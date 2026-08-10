<?php

namespace App\Services;

use App\Enums\ExamStatus;
use App\Enums\QuestionSource;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamBuilderService
{
    /**
     * Guard: Ensure exam is in Draft status before any modifications.
     */
    protected function guardDraft(Exam $exam): void
    {
        if ($exam->status !== ExamStatus::DRAFT) {
            throw ValidationException::withMessages([
                'exam' => ['This exam is locked and cannot be modified. Change status to Draft first.'],
            ]);
        }
    }

    /**
     * Add a brand-new question to both the bank and the exam.
     */
    public function addQuestion(Exam $exam, array $data): Question
    {
        $this->guardDraft($exam);

        return DB::transaction(function () use ($exam, $data) {
            // Auto-generate question code
            $subject = $exam->subject;
            $data['question_code'] = Question::generateCode($subject);
            $data['subject_id'] = $exam->subject_id;

            // Create question in bank
            $question = Question::create([
                'question_code' => $data['question_code'],
                'subject_id' => $data['subject_id'],
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'mark' => $data['mark'] ?? 1.00,
                'difficulty' => $data['difficulty'] ?? 'medium',
                'bloom_level' => $data['bloom_level'] ?? null,
                'estimated_time' => $data['estimated_time'] ?? null,
                'created_by' => auth()->id(),
                'is_public' => $data['is_public'] ?? true,
            ]);

            // Handle type-specific options
            $this->createOptionsForType($question, $data);

            // Attach to exam via pivot
            $maxOrder = $exam->questions()->max('exam_question.display_order') ?? 0;
            $exam->questions()->attach($question->id, [
                'display_order' => $maxOrder + 1,
                'mark_override' => $data['mark'] ?? null,
                'source_type' => QuestionSource::CUSTOM->value,
            ]);

            return $question->load('options');
        });
    }

    /**
     * Import an existing question from the bank into an exam.
     */
    public function importFromBank(Exam $exam, Question $question, ?float $markOverride = null): void
    {
        $this->guardDraft($exam);

        $maxOrder = $exam->questions()->max('exam_question.display_order') ?? 0;
        $exam->questions()->attach($question->id, [
            'display_order' => $maxOrder + 1,
            'mark_override' => $markOverride,
            'source_type' => QuestionSource::BANK->value,
        ]);
    }

    /**
     * Edit a question: Clone on Edit for bank-imported questions.
     */
    public function updateQuestion(Exam $exam, Question $question, array $data): Question
    {
        $this->guardDraft($exam);

        return DB::transaction(function () use ($exam, $question, $data) {
            // Check if this is a bank question used by other exams
            $pivot = $exam->questions()->where('question_id', $question->id)->first()?->pivot;
            $isBank = $pivot && $pivot->source_type === QuestionSource::BANK->value;
            $usedElsewhere = $question->exams()->where('exam_id', '!=', $exam->id)->exists();

            if ($isBank && $usedElsewhere) {
                // Clone on Edit: create a new question, swap in pivot
                $clone = $question->replicate();
                $clone->question_code = Question::generateCode($question->subject);
                $clone->created_by = auth()->id();
                $clone->parent_question_id = $question->id;
                $clone->version = $question->version + 1;
                $clone->fill($data);
                $clone->save();

                // Clone options
                foreach ($question->options as $opt) {
                    $newOpt = $opt->replicate();
                    $newOpt->question_id = $clone->id;
                    $newOpt->save();
                }

                // Swap in pivot
                $exam->questions()->updateExistingPivot($question->id, [
                    'source_type' => QuestionSource::CUSTOM->value,
                ]);
                $exam->questions()->detach($question->id);
                $exam->questions()->attach($clone->id, [
                    'display_order' => $pivot->display_order,
                    'mark_override' => $data['mark'] ?? $pivot->mark_override,
                    'source_type' => QuestionSource::CUSTOM->value,
                ]);

                // Recreate options if type changed
                if (isset($data['type']) || isset($data['options'])) {
                    $clone->options()->delete();
                    $this->createOptionsForType($clone, $data);
                }

                return $clone->load('options');
            }

            // Direct edit (custom or sole user)
            $question->update($data);

            if (isset($data['type']) || isset($data['options']) || isset($data['is_correct_boolean']) || isset($data['pairs']) || isset($data['model_answer'])) {
                $question->options()->delete();
                $this->createOptionsForType($question, $data);
            }

            if (isset($data['mark'])) {
                $exam->questions()->updateExistingPivot($question->id, [
                    'mark_override' => $data['mark'],
                ]);
            }

            return $question->load('options');
        });
    }

    /**
     * Duplicate a question within the same exam.
     */
    public function duplicateQuestion(Exam $exam, Question $question): Question
    {
        $this->guardDraft($exam);

        return DB::transaction(function () use ($exam, $question) {
            $clone = $question->replicate();
            $clone->question_code = Question::generateCode($question->subject);
            $clone->created_by = auth()->id();
            $clone->parent_question_id = $question->id;
            $clone->version = $question->version + 1;
            $clone->save();

            foreach ($question->options as $opt) {
                $newOpt = $opt->replicate();
                $newOpt->question_id = $clone->id;
                $newOpt->save();
            }

            // Clone tags
            $clone->tags()->sync($question->tags->pluck('id'));

            $pivot = $exam->questions()->where('question_id', $question->id)->first()?->pivot;
            $maxOrder = $exam->questions()->max('exam_question.display_order') ?? 0;

            $exam->questions()->attach($clone->id, [
                'display_order' => $maxOrder + 1,
                'mark_override' => $pivot?->mark_override ?? $question->mark,
                'source_type' => QuestionSource::CUSTOM->value,
            ]);

            return $clone->load('options');
        });
    }

    /**
     * Reorder questions via SortableJS AJAX call.
     */
    public function reorderQuestions(Exam $exam, array $orderedIds): void
    {
        $this->guardDraft($exam);

        DB::transaction(function () use ($exam, $orderedIds) {
            foreach ($orderedIds as $index => $questionId) {
                $exam->questions()->updateExistingPivot($questionId, [
                    'display_order' => $index + 1,
                ]);
            }
        });
    }

    /**
     * Remove a question from the exam (detach from pivot, don't delete from bank).
     */
    public function removeQuestion(Exam $exam, Question $question): void
    {
        $this->guardDraft($exam);

        $exam->questions()->detach($question->id);
    }

    /**
     * Delete question from bank entirely.
     */
    public function deleteFromBank(Question $question): void
    {
        $question->delete();
    }

    /**
     * Get bank questions for import modal (filterable by subject, difficulty, tags).
     */
    public function getBankQuestions(int $subjectId, array $filters = [])
    {
        $query = Question::with(['options', 'tags'])
            ->where('subject_id', $subjectId)
            ->where(function ($q) {
                $q->where('is_public', true)
                  ->orWhere('created_by', auth()->id());
            });

        if (!empty($filters['difficulty'])) {
            $query->where('difficulty', $filters['difficulty']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $query->where('question_text', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['tag_id'])) {
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $filters['tag_id']));
        }

        return $query->latest()->get();
    }

    // ── Private Helpers ──

    protected function createOptionsForType(Question $question, array $data): void
    {
        $type = $data['type'] ?? $question->type->value ?? $question->type;

        if ($type === 'mcq') {
            foreach (($data['options'] ?? []) as $index => $optionText) {
                $question->options()->create([
                    'option_text' => $optionText,
                    'is_correct' => ($index == ($data['correct_option_index'] ?? 0)),
                    'order' => $index,
                ]);
            }
        } elseif ($type === 'true_false') {
            $isCorrect = (bool) ($data['is_correct_boolean'] ?? false);
            
            $question->options()->create([
                'option_text' => 'صح',
                'is_correct' => $isCorrect,
                'order' => 0,
            ]);
            $question->options()->create([
                'option_text' => 'خطأ',
                'is_correct' => !$isCorrect,
                'order' => 1,
            ]);
        } elseif ($type === 'matching') {
            foreach (($data['pairs'] ?? []) as $index => $pair) {
                $question->options()->create([
                    'left_item' => $pair['left'],
                    'right_item' => $pair['right'],
                    'partial_mark' => $pair['partial_mark'] ?? null,
                    'order' => $index,
                ]);
            }
        } elseif (in_array($type, ['short_answer', 'essay', 'fill_blank'])) {
            // Save model answers as reference options for correction
            $answers = [];
            if (!empty($data['model_answers']) && is_array($data['model_answers'])) {
                $answers = $data['model_answers'];
            } elseif (!empty($data['model_answer'])) {
                // Fallback for older format
                $answers = [$data['model_answer']];
            }
            
            foreach ($answers as $index => $answer) {
                if (!empty(trim($answer))) {
                    $question->options()->create([
                        'option_text' => trim($answer),
                        'is_correct'  => true,
                        'order'       => $index,
                    ]);
                }
            }
        }
    }
}
