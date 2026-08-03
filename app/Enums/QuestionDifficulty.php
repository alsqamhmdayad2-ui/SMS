<?php

namespace App\Enums;

enum QuestionDifficulty: string
{
    case EASY = 'easy';
    case MEDIUM = 'medium';
    case HARD = 'hard';

    public function label(): string
    {
        return match ($this) {
            self::EASY => 'Easy',
            self::MEDIUM => 'Medium',
            self::HARD => 'Hard',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::EASY => 'success',
            self::MEDIUM => 'warning',
            self::HARD => 'danger',
        };
    }
}
