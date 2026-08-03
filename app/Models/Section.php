<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'class_id',
        'status',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function getGradeAttribute()
    {
        return $this->schoolClass->grade ?? null;
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'section_id');
    }
}
