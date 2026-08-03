<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\AttendanceSessionStatus;

class AttendanceSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'academic_year_id',
        'semester_id',
        'section_id',
        'date',
        'period_number',
        'subject_id',
        'teacher_id',
        'timetable_id',
        'status',
        'created_by',
        'locked_at',
        'locked_by',
    ];

    protected $casts = [
        'date'      => 'date',
        'status'    => AttendanceSessionStatus::class,
        'locked_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
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

    public function timetable()
    {
        return $this->belongsTo(Timetable::class);
    }

    public function records()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isLocked(): bool
    {
        return $this->status === AttendanceSessionStatus::Locked;
    }

    public function isOpen(): bool
    {
        return $this->status === AttendanceSessionStatus::Open;
    }
}
