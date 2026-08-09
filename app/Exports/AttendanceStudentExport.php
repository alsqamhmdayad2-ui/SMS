<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\DTOs\AttendanceFilterData;

class AttendanceStudentExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected array $data,
        protected AttendanceFilterData $filters
    ) {}

    public function headings(): array
    {
        return [
            '#',
            'المادة',
            'إجمالي الحصص',
            'حاضر',
            'غائب',
            'متأخر',
            'نسبة الحضور %'
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->data['subject_breakdown'] as $index => $row) {
            $rows[] = [
                $index + 1,
                $row['subject']->name ?? 'N/A',
                $row['total'],
                $row['present'],
                $row['absent'],
                $row['late'],
                $row['rate'] . '%',
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'تقرير حضور الطالب';
    }
}
