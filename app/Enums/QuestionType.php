<?php

namespace App\Enums;

enum QuestionType: string
{
    case MCQ = 'mcq';
    case TRUE_FALSE = 'true_false';
    case SHORT_ANSWER = 'short_answer';
    case ESSAY = 'essay';
    case MATCHING = 'matching';
    case FILL_BLANK = 'fill_blank';

    public function label(): string
    {
        return match ($this) {
            self::MCQ => 'Multiple Choice',
            self::TRUE_FALSE => 'True / False',
            self::SHORT_ANSWER => 'Short Answer',
            self::ESSAY => 'Essay',
            self::MATCHING => 'Matching',
            self::FILL_BLANK => 'Fill in the Blank',
        };
    }
}
