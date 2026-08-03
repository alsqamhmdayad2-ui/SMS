<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResultPublication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'academic_year_id',
        'semester_id',
        'grade_id',
        'section_id',
        'subject_id',
        'published_type', // 'subject', 'section', 'semester'
        'status',         // 'draft', 'submitted', 'approved', 'published', 'archived'
        'published_by',
        'published_at',
        'notes',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

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

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
