<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\DTOs\AttendanceFilterData;

class AttendanceSectionExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected array $data,
        protected AttendanceFilterData $filters
    ) {}

    public function headings(): array
    {
        return [
            '#',
            'اسم الطالب',
            'الرقم الأكاديمي',
            'إجمالي الحصص',
            'حاضر',
            'غائب',
            'متأخر',
            'معذور',
            'نسبة الحضور %'
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->data['students'] as $index => $row) {
            $rows[] = [
                $index + 1,
                $row['student']->name ?? 'N/A',
                $row['student']->academic_number ?? 'N/A',
                $row['total'] ?? 0,
                $row['present'] ?? 0,
                $row['absent'] ?? 0,
                $row['late'] ?? 0,
                $row['excused'] ?? 0,
                ($row['attendance_percentage'] ?? 0) . '%',
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'تقرير حضور الشعبة';
    }
}
