<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSemesterMark extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'section_id',
        'semester_id',
        'academic_year_id',
        'activity',
        'attendance',
        'homework',
        'monthly1',
        'midterm',
        'monthly2',
        'final_exam',
        'total',
        'is_locked',
        'entered_by',
        'entered_at',
        'locked_by',
        'locked_at',
    ];

    protected $casts = [
        'activity'   => 'decimal:2',
        'attendance' => 'decimal:2',
        'homework'   => 'decimal:2',
        'monthly1'   => 'decimal:2',
        'midterm'    => 'decimal:2',
        'monthly2'   => 'decimal:2',
        'final_exam' => 'decimal:2',
        'total'      => 'decimal:2',
        'is_locked'  => 'boolean',
        'entered_at' => 'datetime',
        'locked_at'  => 'datetime',
    ];

    // ── الحدود القصوى لكل مكون ──
    public const MAX_ACTIVITY   = 10;
    public const MAX_ATTENDANCE = 10;
    public const MAX_HOMEWORK   = 10;
    public const MAX_MONTHLY1   = 10;
    public const MAX_MIDTERM    = 20;
    public const MAX_MONTHLY2   = 10;
    public const MAX_FINAL      = 30;
    public const MAX_TOTAL      = 100;

    // ── العلاقات ──

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    // ── دوال مساعدة ──

    /**
     * احسب المجموع من 100 وخزّنه
     */
    public function calculateTotal(): float
    {
        return (float) (
            ($this->activity   ?? 0) +
            ($this->attendance ?? 0) +
            ($this->homework   ?? 0) +
            ($this->monthly1   ?? 0) +
            ($this->midterm    ?? 0) +
            ($this->monthly2   ?? 0) +
            ($this->final_exam ?? 0)
        );
    }

    /**
     * احصل على التقدير الحرفي
     */
    public function getLetterGrade(): string
    {
        $total = $this->total ?? 0;

        if ($total >= 90) return 'ممتاز';
        if ($total >= 80) return 'جيد جداً';
        if ($total >= 70) return 'جيد';
        if ($total >= 60) return 'مقبول';
        return 'راسب';
    }

    /**
     * هل الطالب ناجح؟
     */
    public function isPassing(): bool
    {
        return ($this->total ?? 0) >= 50;
    }
}
