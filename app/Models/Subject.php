<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];

    /**
     * Classes this subject is assigned to (via class_subject_teacher pivot).
     * Used for "which classes teach this subject".
     */
    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject_teacher', 'subject_id', 'class_id')
                    ->withPivot('teacher_id', 'weekly_periods')
                    ->withTimestamps();
    }

    /**
     * Sections this subject is assigned to (section-level teacher assignment).
     * One teacher per subject per section.
     */
    public function sections()
    {
        return $this->belongsToMany(Section::class, 'subject_section_teacher', 'subject_id', 'section_id')
                    ->withPivot('teacher_id')
                    ->withTimestamps();
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'subject_section_teacher')
                    ->withPivot('section_id')
                    ->withTimestamps();
    }

    /**
     * Teachers qualified to teach this subject (generic specialization).
     */
    public function qualifiedTeachers()
    {
        return $this->belongsToMany(Teacher::class, 'subject_teacher', 'subject_id', 'teacher_id')
                    ->withTimestamps();
    }
}
