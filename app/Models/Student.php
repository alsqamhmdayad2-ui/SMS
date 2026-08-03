<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'birth_date' => 'date',
    ];

    protected $fillable = [
        'national_id',
        'student_number',
        'first_name',
        'father_name',
        'grandfather_name',
        'family_name',
        'english_name',
        'email',
        'phone',
        'birth_date',
        'place_of_birth',
        'gender',
        'nationality',
        'religion',
        'blood_type',
        'health_status',
        'avatar',
        'status',
        'governorate',
        'city',
        'region',
        'neighborhood',
        'street',
        'nearest_landmark',
        'grade_id',
        'class_id',
        'section_id',
        'parent_id'
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

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function parent()
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}

