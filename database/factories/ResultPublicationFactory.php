<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResultPublicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'semester_id' => Semester::factory(),
            'grade_id' => Grade::factory(),
            'section_id' => \App\Models\Section::factory(),
            'subject_id' => \App\Models\Subject::factory(),
            'published_type' => 'semester',
            'status' => 'published',
            'published_at' => now(),
            'published_by' => \App\Models\User::factory(),
            'published_at' => null,
            'published_by' => null,
        ];
    }
}
