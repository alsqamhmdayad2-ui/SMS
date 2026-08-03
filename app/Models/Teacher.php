<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'father_name',
        'grandfather_name',
        'family_name',
        'national_id',
        'phone',
        'specialization',
        'salary',
        'max_weekly_periods',
        'address',
        'avatar'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->father_name} {$this->grandfather_name} {$this->family_name}");
    }

    public function getNameAttribute()
    {
        return $this->full_name;
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_section_teacher', 'teacher_id', 'subject_id')
                    ->withPivot('section_id')
                    ->withTimestamps()
                    ->distinct();
    }

    public function qualifiedSubjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher', 'teacher_id', 'subject_id')
                    ->withTimestamps();
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }
}

