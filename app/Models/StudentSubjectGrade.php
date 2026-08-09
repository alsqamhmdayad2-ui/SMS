<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSubjectGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'academic_year_id',
        'semester_id',
        'section_id',
        'total_percentage',
        'letter_grade',
        'gpa_points',
        'is_passing',
        'rank_in_section',
        'is_finalized',
        'calculated_at',
        'calculated_by',
    ];

    protected $casts = [
        'total_percentage' => 'decimal:2',
        'gpa_points' => 'decimal:2',
        'is_passing' => 'boolean',
        'is_finalized' => 'boolean',
        'rank_in_section' => 'integer',
        'calculated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
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

    public function calculator()
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }
}
