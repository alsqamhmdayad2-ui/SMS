<?php

namespace App\Enums;

enum BloomLevel: string
{
    case REMEMBER = 'remember';
    case UNDERSTAND = 'understand';
    case APPLY = 'apply';
    case ANALYZE = 'analyze';
    case EVALUATE = 'evaluate';
    case CREATE = 'create';

    public function label(): string
    {
        return match ($this) {
            self::REMEMBER => 'Remember',
            self::UNDERSTAND => 'Understand',
            self::APPLY => 'Apply',
            self::ANALYZE => 'Analyze',
            self::EVALUATE => 'Evaluate',
            self::CREATE => 'Create',
        };
    }
}
