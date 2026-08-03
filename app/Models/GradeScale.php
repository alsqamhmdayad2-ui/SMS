<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeScale extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'percentage_from',
        'percentage_to',
        'letter_grade',
        'gpa_point',
        'is_passing',
        'minimum_required_percentage',
        'description',
        'status',
    ];

    protected $casts = [
        'percentage_from' => 'decimal:2',
        'percentage_to' => 'decimal:2',
        'gpa_point' => 'decimal:2',
        'minimum_required_percentage' => 'decimal:2',
        'is_passing' => 'boolean',
        'status' => 'boolean',
    ];
}
