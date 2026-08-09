<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AnnualReportExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(protected array $data) {}

    public function headings(): array
    {
        if (($this->data['type'] ?? '') === 'student') {
            $headings = ['الفصل'];
            foreach ($this->data['subjects'] as $sub) {
                $headings[] = $sub->name;
            }
            $headings[] = 'المتوسط %';
            $headings[] = 'التقدير';
            return $headings;
        }

        // Section type
        $headings = ['#', 'اسم الطالب'];
        foreach ($this->data['semesters'] as $sem) {
            $headings[] = 'متوسط ' . $sem->name;
        }
        $headings[] = 'المتوسط السنوي %';
        $headings[] = 'التقدير';
        $headings[] = 'النتيجة';
        return $headings;
    }

    public function array(): array
    {
        if (($this->data['type'] ?? '') === 'student') {
            $rows = [];
            foreach ($this->data['semesters'] as $semData) {
                $row = [$semData['semester']->name];
                foreach ($semData['subjects'] as $s) {
                    $row[] = $s['percentage'] !== null ? $s['percentage'] . '%' : '-';
                }
                $row[] = $semData['average'] !== null ? $semData['average'] . '%' : '-';
                $row[] = $semData['letter_grade'] ?? '-';
                $rows[] = $row;
            }
            $rows[] = [
                'المجموع السنوي',
                ...array_fill(0, count($this->data['subjects']), ''),
                $this->data['annual_avg'] !== null ? $this->data['annual_avg'] . '%' : '-',
                $this->data['annual_grade'] ?? '-',
            ];
            return $rows;
        }

        // Section type
        $rows = [];
        foreach ($this->data['students'] as $i => $row) {
            $r = [
                $row['rank'] ?? ($i + 1),
                $row['student']->name ?? 'N/A',
            ];
            foreach ($this->data['semesters'] as $sem) {
                $avg = $row['semester_averages'][$sem->id] ?? null;
                $r[] = $avg !== null ? $avg . '%' : '-';
            }
            $r[] = $row['annual_avg'] !== null ? $row['annual_avg'] . '%' : '-';
            $r[] = $row['letter_grade'] ?? '-';
            $r[] = ($row['is_passing'] ?? null) === true ? 'ناجح' : (($row['is_passing'] === false) ? 'راسب' : '-');
            $rows[] = $r;
        }
        return $rows;
    }

    public function title(): string
    {
        return 'التقرير السنوي';
    }
}
