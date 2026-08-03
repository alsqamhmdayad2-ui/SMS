<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::first();

        if ($year) {
            Semester::firstOrCreate([
                'academic_year_id' => $year->id,
                'name' => 'First Semester',
            ], [
                'start_date' => '2025-09-01',
                'end_date' => '2026-01-15',
                'status' => 1,
            ]);

            Semester::firstOrCreate([
                'academic_year_id' => $year->id,
                'name' => 'Second Semester',
            ], [
                'start_date' => '2026-02-01',
                'end_date' => '2026-06-01',
                'status' => 1,
            ]);
        }
    }
}
