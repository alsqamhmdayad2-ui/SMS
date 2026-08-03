<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\AttendanceStatus;

class AttendanceOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_record_id',
        'attendance_session_id',
        'student_id',
        'old_status',
        'new_status',
        'overridden_by',
        'overridden_at',
        'reason',
    ];

    protected $casts = [
        'overridden_at' => 'datetime',
    ];

    public function record()
    {
        return $this->belongsTo(AttendanceRecord::class, 'attendance_record_id');
    }

    public function session()
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function overriddenBy()
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }

    public function getOldStatusLabelAttribute(): string
    {
        return AttendanceStatus::tryFrom($this->old_status)?->label() ?? $this->old_status;
    }

    public function getNewStatusLabelAttribute(): string
    {
        return AttendanceStatus::tryFrom($this->new_status)?->label() ?? $this->new_status;
    }
}
