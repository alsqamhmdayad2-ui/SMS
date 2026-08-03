<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\ReportCardStatus;

class ReportCard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'semester_id',
        'section_id',
        'report_period',
        'student_name_snapshot',
        'section_name_snapshot',
        'academic_year_name_snapshot',
        'gpa',
        'total_percentage',
        'rank_in_section',
        'status',
        'academic_status',
        'is_locked',
        'locked_at',
        'locked_by',
        'published_at',
        'published_by',
        'verification_uuid',
        'verification_hash',
    ];

    protected $casts = [
        'gpa' => 'decimal:2',
        'total_percentage' => 'decimal:2',
        'status' => ReportCardStatus::class,
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

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

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function publishedBy()
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
