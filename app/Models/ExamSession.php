<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'started_at',
        'draft_answers',
        'submitted_at',
    ];

    protected $casts = [
        'started_at'    => 'datetime',
        'submitted_at'  => 'datetime',
        'draft_answers' => 'array',
    ];
}
