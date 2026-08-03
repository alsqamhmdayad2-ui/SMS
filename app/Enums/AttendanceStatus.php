<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Absent  = 'absent';
    case Late    = 'late';
    case Excused = 'excused';

    public function label(): string
    {
        return match($this) {
            self::Present => __('attendance.present'),
            self::Absent  => __('attendance.absent'),
            self::Late    => __('attendance.late'),
            self::Excused => __('attendance.excused'),
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Present => 'success',
            self::Absent  => 'danger',
            self::Late    => 'warning',
            self::Excused => 'info',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Present => 'fas fa-check-circle',
            self::Absent  => 'fas fa-times-circle',
            self::Late    => 'fas fa-clock',
            self::Excused => 'fas fa-file-alt',
        };
    }

    /**
     * Count as "present equivalent" for percentage calculations.
     * Late is half-counted, Excused is not penalized.
     */
    public function countsAsPresent(): bool
    {
        return in_array($this, [self::Present, self::Late, self::Excused]);
    }
}
