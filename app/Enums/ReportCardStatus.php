<?php

namespace App\Enums;

enum ReportCardStatus: string
{
    case Draft = 'DRAFT';
    case Generated = 'GENERATED';
    case Published = 'PUBLISHED';
    case Revoked = 'REVOKED';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Draft',
            self::Generated => 'Generated',
            self::Published => 'Published',
            self::Revoked => 'Revoked',
        };
    }
    
    public function color(): string
    {
        return match($this) {
            self::Draft => 'secondary',
            self::Generated => 'info',
            self::Published => 'success',
            self::Revoked => 'danger',
        };
    }
}
