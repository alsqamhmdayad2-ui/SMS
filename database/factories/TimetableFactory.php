<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimetableFactory extends Factory
{
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'semester_id'      => Semester::factory(),
            'grade_id'         => Grade::factory(),
            'class_id'         => SchoolClass::factory(),
            'section_id'       => Section::factory(),
            'subject_id'       => Subject::factory(),
            'teacher_id'       => Teacher::factory(),
            'day_of_week'      => $this->faker->randomElement(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday']),
            'period_number'    => $this->faker->numberBetween(1, 6),
            'status'           => 1,
        ];
    }
}
