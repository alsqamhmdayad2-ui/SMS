<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'subject_id',
        'name',
        'code',
        'weight_percentage',
        'order',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'weight_percentage' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
