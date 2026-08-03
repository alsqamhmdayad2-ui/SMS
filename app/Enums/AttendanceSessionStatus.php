<?php

namespace App\Enums;

enum AttendanceSessionStatus: string
{
    case Open   = 'open';
    case Locked = 'locked';

    public function label(): string
    {
        return match($this) {
            self::Open   => __('attendance.open'),
            self::Locked => __('attendance.locked'),
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Open   => 'success',
            self::Locked => 'secondary',
        };
    }
}
