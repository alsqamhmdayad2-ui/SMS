<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'academic_year_id',
        'title',
        'description',
        'event_type',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'is_all_day',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'start_time' => 'datetime:H:i',
        'end_time'   => 'datetime:H:i',
        'is_all_day' => 'boolean',
        'status'     => 'boolean',
    ];

    /**
     * Get the academic year that owns the event.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
