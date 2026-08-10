<?php

namespace App\Models;

use App\Enums\ExamStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'type',
        'academic_year_id',
        'semester_id',
        'grade_id',
        'class_id',
        'section_id',
        'subject_id',
        'teacher_id',
        'exam_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'status',
        'display_mode',
        'instructions',
        'total_marks',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'status' => ExamStatus::class,
    ];

    // ── Academic Relations ──

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    // ── Question Bank Relation (via Pivot) ──

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'exam_question')
            ->withPivot(['display_order', 'mark_override', 'source_type'])
            ->orderByPivot('display_order')
            ->withTimestamps();
    }

    // ── Computed ──

    public function getTotalMarksAttribute($value)
    {
        // If there are specific questions built for this exam, the sum overrides the column
        if ($this->questions()->exists()) {
            return $this->questions->sum(function ($q) {
                return $q->pivot->mark_override ?? $q->mark;
            });
        }
        
        // Otherwise, use the manually entered total_marks column
        return $value ?? 0;
    }

    public function getQuestionCountAttribute()
    {
        return $this->questions()->count();
    }

    public function getDurationFormattedAttribute()
    {
        if (!$this->duration_minutes) {
            return null;
        }
        $hours = intdiv($this->duration_minutes, 60);
        $mins = $this->duration_minutes % 60;
        return $hours > 0 ? "{$hours}h {$mins}m" : "{$mins} min";
    }
}
