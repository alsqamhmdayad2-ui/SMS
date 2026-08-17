<?php

namespace App\Models;

use App\Enums\BloomLevel;
use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'question_code',
        'exam_id',
        'subject_id',
        'type',
        'question_text',
        'mark',
        'difficulty',
        'bloom_level',
        'estimated_time',
        'display_order',
        'created_by',
        'is_public',
        'parent_question_id',
        'version',
    ];

    protected $casts = [
        'type' => QuestionType::class,
        'difficulty' => QuestionDifficulty::class,
        'bloom_level' => BloomLevel::class,
        'is_public' => 'boolean',
    ];

    // ── Relations ──

    public function parent()
    {
        return $this->belongsTo(Question::class, 'parent_question_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'question_tag')->withTimestamps();
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_question')
            ->withPivot(['display_order', 'mark_override', 'source_type'])
            ->withTimestamps();
    }

    // ── Helpers ──

    /**
     * Generate a unique question code like MATH-000124
     */
    public static function generateCode(?Subject $subject = null): string
    {
        $prefix = $subject ? strtoupper(substr($subject->name, 0, 4)) : 'GENL';
        $lastId = self::withTrashed()->max('id') ?? 0;
        return $prefix . '-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);
    }

    public function getEstimatedTimeFormattedAttribute()
    {
        if (!$this->estimated_time) {
            return null;
        }
        if ($this->estimated_time >= 60) {
            $min = intdiv($this->estimated_time, 60);
            $sec = $this->estimated_time % 60;
            return $sec > 0 ? "{$min}m {$sec}s" : "{$min} min";
        }
        return "{$this->estimated_time} sec";
    }

    public function getMarksAttribute()
    {
        return $this->pivot->mark_override ?? $this->mark ?? 0;
    }

    public function getCorrectAnswerAttribute()
    {
        if (in_array($this->type->value, ['short_answer', 'essay', 'fill_blank'])) {
            return $this->options->first()?->option_text;
        }
        return null;
    }
}
