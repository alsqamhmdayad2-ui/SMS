<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\DTOs\AttendanceFilterData;

class AttendanceDailyExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected array $data,
        protected AttendanceFilterData $filters,
        protected string $date
    ) {}

    public function headings(): array
    {
        return [
            '#',
            'الحصة',
            'الصف/الشعبة',
            'المادة',
            'المعلم',
            'إجمالي الطلاب',
            'نسبة الحضور %'
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->data['sessions'] as $index => $session) {
            $total = $session->records->count();
            $rows[] = [
                $index + 1,
                $session->period_number,
                ($session->section->schoolClass->name ?? '') . ' - ' . ($session->section->name ?? 'N/A'),
                $session->subject->name ?? 'N/A',
                $session->teacher->name ?? 'N/A',
                $total,
                ($session->rate ?? 0) . '%',
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'التقرير اليومي';
    }
}
