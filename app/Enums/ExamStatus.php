<?php

namespace App\Enums;

enum ExamStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case CLOSED = 'closed';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::CLOSED => 'Closed',
            self::ARCHIVED => 'Archived',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::DRAFT => 'secondary',
            self::PUBLISHED => 'success',
            self::CLOSED => 'danger',
            self::ARCHIVED => 'dark',
        };
    }
}
