<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SectionResultExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected array $data
    ) {}

    public function headings(): array
    {
        $headings = ['#', 'Rank', 'Student Name', 'Student ID'];

        // Add subject columns
        if (!empty($this->data['subjects'])) {
            foreach ($this->data['subjects'] as $subject) {
                $headings[] = $subject->name;
            }
        }

        $headings = array_merge($headings, ['Average %', 'GPA', 'Status']);

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
                $studentData['student']->id,
            ];

            // Add subject scores
            foreach ($studentData['subjects'] as $subjectScore) {
                $row[] = $subjectScore['percentage'] !== null ? $subjectScore['percentage'] . '%' : 'Pending';
            }

            $row[] = $studentData['average'] . '%';
            $row[] = $studentData['gpa'] ?? '-';
            $row[] = ucfirst($studentData['status']);

            $rows[] = $row;
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Section Results';
    }
}
