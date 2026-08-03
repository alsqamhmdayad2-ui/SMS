<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct',
        'partial_mark',
        'left_item',
        'right_item',
        'order',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'partial_mark' => 'decimal:2',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
