<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\AttendanceStatus;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_session_id',
        'student_id',
        'status',
        'remarks',
        'marked_by',
        'marked_at',
        'updated_by',
    ];

    protected $casts = [
        'status'    => AttendanceStatus::class,
        'marked_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function session()
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function overrides()
    {
        return $this->hasMany(AttendanceOverride::class, 'attendance_record_id')->orderBy('overridden_at', 'desc');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isPresent(): bool
    {
        return $this->status === AttendanceStatus::Present;
    }

    public function isAbsent(): bool
    {
        return $this->status === AttendanceStatus::Absent;
    }

    public function isLate(): bool
    {
        return $this->status === AttendanceStatus::Late;
    }
}
