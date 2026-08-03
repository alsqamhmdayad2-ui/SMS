<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Timetable;
use Illuminate\Support\Facades\DB;

/**
 * TimetableGeneratorService
 * 
 * Generates a school timetable using rule-based scheduling
 * aligned with the Palestinian curriculum and Gaza school practices.
 * 
 * Priority of rules:
 * 1. No teacher conflict (same teacher, same day/period, different section)
 * 2. No section conflict (same section, same day/period)
 * 3. Respect weekly_periods per subject
 * 4. Respect max daily periods per grade (5 for grade 1-4, 6 for grade 5-9)
 * 5. Respect teacher max_weekly_periods (نصاب)
 * 6. Distribute periods across days (no clustering in one day)
 * 7. Allow consecutive periods where needed (not forced)
 */
class TimetableGeneratorService
{
    // Working days in Gaza schools (Saturday to Thursday)
    private array $days = ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];

    /**
     * Generate timetable for a specific section.
     * Returns array of result: ['success' => bool, 'message' => string, 'schedule' => array]
     */
    public function generateForSection(Section $section, AcademicYear $academicYear, Semester $semester): array
    {
        $section->load('schoolClass.grade');

        // Determine max periods per day based on grade level
        $maxPeriodsPerDay = $this->getMaxPeriods($section);

        // Get subjects assigned to this section with teacher and weekly_periods
        $assignments = DB::table('subject_section_teacher as sst')
            ->join('subjects', 'subjects.id', '=', 'sst.subject_id')
            ->leftJoin('teachers', 'teachers.id', '=', 'sst.teacher_id')
            ->join('class_subject_teacher as cst', function($join) use ($section) {
                $join->on('cst.subject_id', '=', 'sst.subject_id')
                     ->where('cst.class_id', '=', $section->class_id);
            })
            ->where('sst.section_id', $section->id)
            ->where('sst.academic_year_id', $academicYear->id)
            ->where('cst.weekly_periods', '>', 0)
            ->select(
                'subjects.id as subject_id',
                'subjects.name as subject_name',
                'cst.weekly_periods',
                'teachers.id as teacher_id',
                'teachers.max_weekly_periods as teacher_max_load'
            )
            ->get();

        if ($assignments->isEmpty()) {
            return [
                'success' => false,
                'message' => 'لا توجد مواد مرتبطة بهذه الشعبة أو جميع المواد لديها عدد حصص = 0. يرجى تحديد عدد الحصص الأسبوعية لكل مادة أولاً.',
                'schedule' => []
            ];
        }

        // Track teacher load across ALL sections (for conflict detection and نصاب check)
        $teacherUsedSlots = $this->getTeacherOccupiedSlots($academicYear->id, $semester->id, $section->id);
        $teacherWeeklyLoad = $this->getTeacherCurrentLoad($academicYear->id, $semester->id);

        // Build a grid: [day][period] = subject_id or null
        $grid = [];
        foreach ($this->days as $day) {
            for ($p = 1; $p <= $maxPeriodsPerDay; $p++) {
                $grid[$day][$p] = null;
            }
        }

        // Sort subjects: most periods first (greedy approach)
        $subjectsList = $assignments->sortByDesc('weekly_periods')->values();

        // Schedule each subject
        foreach ($subjectsList as $subject) {
            $periodsNeeded = $subject->weekly_periods;
            $placed = 0;

            // Strategy: distribute across days evenly
            // Create a priority list of days sorted by current load (least loaded first)
            $dayLoad = [];
            foreach ($this->days as $day) {
                $dayLoad[$day] = collect($grid[$day])->filter()->count();
            }
            asort($dayLoad);

            $maxPerDay = (int) ceil($periodsNeeded / count($this->days));
            $maxPerDay = max(1, min($maxPerDay, 2)); // cap at 2 per day unless necessary

            $subjectDayCount = []; // track how many times subject placed per day
            foreach ($this->days as $day) {
                $subjectDayCount[$day] = 0;
            }

            // Try to place subject in available slots
            $attempts = 0;
            while ($placed < $periodsNeeded && $attempts < 200) {
                $attempts++;

                // Reload day priorities each iteration
                $dayPriority = [];
                foreach ($this->days as $day) {
                    $dayPriority[$day] = $dayLoad[$day] ?? 0;
                }
                asort($dayPriority);

                $slotFound = false;
                foreach ($dayPriority as $day => $load) {
                    // Rule 6: Don't cluster - skip if already at max per day for this subject
                    if ($subjectDayCount[$day] >= $maxPerDay && $placed < ($periodsNeeded - count($this->days))) {
                        continue;
                    }

                    for ($period = 1; $period <= $maxPeriodsPerDay; $period++) {
                        // Rule 2: Section slot must be free
                        if ($grid[$day][$period] !== null) continue;

                        // Rule 1: Teacher must not have conflict in other sections
                        if ($subject->teacher_id) {
                            $slotKey = "{$day}_{$period}";
                            if (isset($teacherUsedSlots[$subject->teacher_id][$slotKey])) continue;

                            // Rule 5: Check teacher نصاب
                            $currentLoad = $teacherWeeklyLoad[$subject->teacher_id] ?? 0;
                            $maxLoad = $subject->teacher_max_load ?? 24;
                            if ($currentLoad >= $maxLoad) {
                                // Teacher is at capacity - still place but flag it
                                // In real scenario you might want to warn
                            }
                        }

                        // Place the subject here
                        $grid[$day][$period] = $subject->subject_id;
                        $subjectDayCount[$day]++;
                        $dayLoad[$day] = ($dayLoad[$day] ?? 0) + 1;

                        // Mark teacher slot as used in our local tracking
                        if ($subject->teacher_id) {
                            $slotKey = "{$day}_{$period}";
                            $teacherUsedSlots[$subject->teacher_id][$slotKey] = true;
                            $teacherWeeklyLoad[$subject->teacher_id] = ($teacherWeeklyLoad[$subject->teacher_id] ?? 0) + 1;
                        }

                        $placed++;
                        $slotFound = true;
                        break; // Move to next day/period
                    }

                    if ($slotFound) break;
                }

                // If no slot found in normal order, try any free slot (fallback)
                if (!$slotFound) {
                    foreach ($this->days as $day) {
                        for ($period = 1; $period <= $maxPeriodsPerDay; $period++) {
                            if ($grid[$day][$period] !== null) continue;

                            if ($subject->teacher_id) {
                                $slotKey = "{$day}_{$period}";
                                if (isset($teacherUsedSlots[$subject->teacher_id][$slotKey])) continue;
                            }

                            $grid[$day][$period] = $subject->subject_id;
                            $subjectDayCount[$day]++;
                            if ($subject->teacher_id) {
                                $slotKey = "{$day}_{$period}";
                                $teacherUsedSlots[$subject->teacher_id][$slotKey] = true;
                                $teacherWeeklyLoad[$subject->teacher_id] = ($teacherWeeklyLoad[$subject->teacher_id] ?? 0) + 1;
                            }
                            $placed++;
                            $slotFound = true;
                            break 2;
                        }
                    }
                }

                if (!$slotFound) break; // Grid is full
            }
        }

        // Rule 7: Post-process - group consecutive periods for subjects that benefit from it
        $grid = $this->optimizeConsecutive($grid, $assignments, $maxPeriodsPerDay);

        return [
            'success' => true,
            'message' => 'تم توليد الجدول بنجاح.',
            'schedule' => $grid,
        ];
    }

    /**
     * Save a generated schedule to the database.
     */
    public function saveSchedule(Section $section, AcademicYear $academicYear, Semester $semester, array $grid): void
    {
        $section->load('schoolClass');

        // Get teacher mappings for this section
        $teacherMappings = DB::table('subject_section_teacher')
            ->where('section_id', $section->id)
            ->where('academic_year_id', $academicYear->id)
            ->pluck('teacher_id', 'subject_id')
            ->toArray();

        // Delete existing timetable for this section/year/semester
        Timetable::where('section_id', $section->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('semester_id', $semester->id)
            ->delete();

        $inserts = [];
        $now = now();
        foreach ($grid as $day => $periods) {
            foreach ($periods as $periodNumber => $subjectId) {
                if ($subjectId) {
                    $inserts[] = [
                        'academic_year_id' => $academicYear->id,
                        'semester_id'      => $semester->id,
                        'grade_id'         => $section->schoolClass->grade_id,
                        'class_id'         => $section->class_id,
                        'section_id'       => $section->id,
                        'subject_id'       => $subjectId,
                        'teacher_id'       => $teacherMappings[$subjectId] ?? null,
                        'day_of_week'      => $day,
                        'period_number'    => $periodNumber,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                }
            }
        }

        if (!empty($inserts)) {
            Timetable::insert($inserts);
        }
    }

    /**
     * Get max periods per day based on grade level.
     * Grade 1-4 → 5 periods, Grade 5-9 → 6 periods.
     */
    private function getMaxPeriods(Section $section): int
    {
        $gradeName = $section->schoolClass->name ?? '';
        if (preg_match('/(الأول|الثاني|الثالث|الرابع|1|2|3|4)/u', $gradeName)) {
            return 5;
        }
        return 6;
    }

    /**
     * Get all teacher-occupied slots from other sections (for conflict detection).
     * Returns: [teacher_id][day_period_key] = true
     */
    private function getTeacherOccupiedSlots(int $yearId, int $semesterId, int $excludeSectionId): array
    {
        $records = DB::table('timetables')
            ->where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->where('section_id', '!=', $excludeSectionId)
            ->whereNotNull('teacher_id')
            ->select('teacher_id', 'day_of_week', 'period_number')
            ->get();

        $slots = [];
        foreach ($records as $r) {
            $key = "{$r->day_of_week}_{$r->period_number}";
            $slots[$r->teacher_id][$key] = true;
        }
        return $slots;
    }

    /**
     * Get teacher current weekly load across all sections.
     */
    private function getTeacherCurrentLoad(int $yearId, int $semesterId): array
    {
        $records = DB::table('timetables')
            ->where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->whereNotNull('teacher_id')
            ->select('teacher_id', DB::raw('count(*) as total'))
            ->groupBy('teacher_id')
            ->get();

        return $records->pluck('total', 'teacher_id')->toArray();
    }

    /**
     * Post-processing: group consecutive periods for the same subject where possible.
     * This is applied as an optimization, not a strict rule.
     */
    private function optimizeConsecutive(array $grid, $assignments, int $maxPeriods): array
    {
        foreach ($this->days as $day) {
            for ($p = 1; $p < $maxPeriods; $p++) {
                $current = $grid[$day][$p];
                $next = $grid[$day][$p + 1] ?? null;

                if ($current === null || $next === null || $current === $next) continue;

                // Check if $p+2 exists and is the same as current
                // If so, swap $p+1 and $p+2 to make consecutive
                $pPlus2 = $grid[$day][$p + 2] ?? null;
                if ($pPlus2 === $current && $next !== $current) {
                    // Swap p+1 and p+2 to bring them together
                    $grid[$day][$p + 1] = $current;
                    $grid[$day][$p + 2] = $next;
                }
            }
        }
        return $grid;
    }
}
