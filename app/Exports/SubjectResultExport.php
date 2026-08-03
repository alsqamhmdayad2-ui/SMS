<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SubjectResultExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected array $data
    ) {}

    public function headings(): array
    {
        $headings = ['#', 'Rank', 'Student Name'];

        if (!empty($this->data['components'])) {
            foreach ($this->data['components'] as $comp) {
                $headings[] = $comp->name . ' (' . $comp->weight_percentage . '%)';
            }
        }

        $headings[] = 'Total %';

        return $headings;
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->data['students'] as $index => $studentData) {
            $row = [
                $index + 1,
                $studentData['rank'],
                $studentData['student']->name,
            ];

            foreach ($this->data['components'] as $comp) {
                $compScore = $studentData['components'][$comp->code] ?? null;
                $row[] = $compScore ? $compScore['contribution'] : '-';
            }

            $row[] = $studentData['total'] . '%';

            $rows[] = $row;
        }

        return $rows;
    }

    public function title(): string
    {
        return $this->data['subject']->name ?? 'Subject Results';
    }
}
