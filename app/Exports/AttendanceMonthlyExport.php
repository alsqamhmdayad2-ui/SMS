<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\DTOs\AttendanceFilterData;

class AttendanceMonthlyExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected array $data,
        protected AttendanceFilterData $filters,
        protected string $month
    ) {}

    public function headings(): array
    {
        return [
            '#',
            'التاريخ',
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

        foreach ($this->data['daily_data'] as $index => $row) {
            $rows[] = [
                $index + 1,
                $row['date'],
                $row['total'],
                $row['present'],
                $row['absent'],
                $row['late'],
                $row['excused'],
                $row['rate'] . '%',
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'التقرير الشهري';
    }
}
