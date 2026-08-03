<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'student_id',
        'marks_obtained',
        'total_marks',
        'percentage',
        'attendance_status',
        'attempt_number',
        'remarks',
        'submitted_at',
        'graded_at',
        'graded_by',
        'updated_by',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'total_marks' => 'decimal:2',
        'percentage' => 'decimal:2',
        'attempt_number' => 'integer',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
    ];

    // Valid attendance statuses
    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_EXCUSED = 'excused';
    const STATUS_CHEATING = 'cheating';
    const STATUS_INCOMPLETE = 'incomplete';

    public static function attendanceStatuses(): array
    {
        return [
            self::STATUS_PRESENT => 'Present',
            self::STATUS_ABSENT => 'Absent',
            self::STATUS_EXCUSED => 'Excused',
            self::STATUS_CHEATING => 'Cheating',
            self::STATUS_INCOMPLETE => 'Incomplete',
        ];
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function grader()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
