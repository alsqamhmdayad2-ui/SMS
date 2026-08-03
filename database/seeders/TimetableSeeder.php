<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TimetableSeeder extends Seeder
{
    public function run(): void
    {
        // Get the required academic structures
        $academicYear = AcademicYear::where('status', true)->first();
        $semester = Semester::where('status', true)->first();
        $section = Section::with(['schoolClass.grade'])->first();

        if (!$academicYear || !$semester || !$section) {
            $this->command->warn('Required academic structure missing. Skipping TimetableSeeder.');
            return;
        }

        $class = $section->schoolClass;
        $grade = $class->grade;

        $subjects = Subject::all();
        $teachers = Teacher::all();

        if ($subjects->isEmpty() || $teachers->isEmpty()) {
            $this->command->warn('Subjects or Teachers missing. Skipping TimetableSeeder.');
            return;
        }

        // Days of week (Fixed Classroom philosophy: 6 days, starting Saturday)
        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
        
        // Define standard times for periods
        $periodTimes = [
            1 => ['08:00', '08:45'],
            2 => ['08:45', '09:30'],
            3 => ['09:45', '10:30'], // After 15m break
            4 => ['10:30', '11:15'],
            5 => ['11:30', '12:15'], // After 15m break
            6 => ['12:15', '13:00'],
        ];

        $entries = [];

        foreach ($days as $day) {
            for ($period = 1; $period <= 6; $period++) {
                // Randomly pick a subject and teacher for each period
                // In a real scenario, this would be assigned logically, but random is fine for seeding
                $subject = $subjects->random();
                $teacher = $teachers->random();
                
                $entries[] = [
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester->id,
                    'grade_id' => $grade->id,
                    'class_id' => $class->id,
                    'section_id' => $section->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'day_of_week' => $day,
                    'period_number' => $period,
                    'start_time' => $periodTimes[$period][0],
                    'end_time' => $periodTimes[$period][1],

                    'status' => true,
                    'created_by' => 1, // assuming Admin user ID is 1
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        Timetable::insert($entries);
    }
}
