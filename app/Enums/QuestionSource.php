<?php

namespace App\Enums;

enum QuestionSource: string
{
    case BANK = 'bank';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::BANK => 'From Question Bank',
            self::CUSTOM => 'Custom (Exam-Specific)',
        };
    }
}
