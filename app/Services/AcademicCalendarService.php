<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\SchoolEvent;
use App\Models\Student;
use Carbon\Carbon;

class AcademicCalendarService
{
    /**
     * Get all calendar events for a specific student.
     *
     * @param Student $student
     * @return array
     */
    public function getStudentCalendarEvents(Student $student): array
    {
        $events = [];

        // 1. Get current active academic year
        $activeYear = AcademicYear::where('status', true)->first();

        if (!$activeYear) {
            return $events;
        }

        // Add Academic Year Start/End
        $events[] = [
            'title' => 'بداية العام الدراسي (' . $activeYear->name . ')',
            'start' => $activeYear->start_date->format('Y-m-d'),
            'allDay' => true,
            'color' => '#3b82f6', // blue
            'extendedProps' => ['type' => 'academic_year']
        ];
        
        $events[] = [
            'title' => 'نهاية العام الدراسي (' . $activeYear->name . ')',
            'start' => $activeYear->end_date->format('Y-m-d'),
            'allDay' => true,
            'color' => '#ef4444', // red
            'extendedProps' => ['type' => 'academic_year']
        ];

        // 2. Get Semesters
        foreach ($activeYear->semesters as $semester) {
            $events[] = [
                'title' => 'بداية ' . $semester->name,
                'start' => $semester->start_date->format('Y-m-d'),
                'allDay' => true,
                'color' => '#8b5cf6', // purple
                'extendedProps' => ['type' => 'semester']
            ];
            
            $events[] = [
                'title' => 'نهاية ' . $semester->name,
                'start' => $semester->end_date->format('Y-m-d'),
                'allDay' => true,
                'color' => '#ec4899', // pink
                'extendedProps' => ['type' => 'semester']
            ];
        }

        // 3. Get School Events
        $schoolEvents = SchoolEvent::where('academic_year_id', $activeYear->id)
            ->where('status', true)
            ->get();

        foreach ($schoolEvents as $event) {
            $color = '#10b981'; // green for normal events
            if ($event->event_type === 'holiday') {
                $color = '#f59e0b'; // amber for holidays
            } elseif ($event->event_type === 'meeting') {
                $color = '#6366f1'; // indigo
            }

            $formattedEvent = [
                'title' => $event->title,
                'start' => $event->start_date->format('Y-m-d') . ($event->start_time ? 'T' . $event->start_time->format('H:i:s') : ''),
                'allDay' => $event->is_all_day,
                'color' => $color,
                'extendedProps' => [
                    'type' => $event->event_type,
                    'description' => $event->description,
                ]
            ];

            if ($event->end_date) {
                // For FullCalendar, end date is exclusive, so we might need to add 1 day if it's an all day event, 
                // but let's keep it simple first or just use the exact date
                $formattedEvent['end'] = $event->end_date->format('Y-m-d') . ($event->end_time ? 'T' . $event->end_time->format('H:i:s') : '');
                
                // FullCalendar exclusive end date fix for allDay events spanning multiple days
                if ($event->is_all_day && $event->start_date->notEqualTo($event->end_date)) {
                    $formattedEvent['end'] = $event->end_date->addDay()->format('Y-m-d');
                }
            }

            $events[] = $formattedEvent;
        }

        // 4. Get Student Exams
        // Find exams related to the student's current grade, class, and section
        $exams = Exam::where('academic_year_id', $activeYear->id)
            ->where('grade_id', $student->grade_id)
            ->where(function ($query) use ($student) {
                $query->whereNull('class_id')
                      ->orWhere('class_id', $student->class_id);
            })
            ->where(function ($query) use ($student) {
                $query->whereDoesntHave('sections')
                      ->orWhereHas('sections', function ($q) use ($student) {
                          $q->where('sections.id', $student->section_id);
                      });
            })
            ->whereIn('status', ['scheduled', 'ongoing', 'completed'])
            ->with('subject')
            ->get();

        foreach ($exams as $exam) {
            $events[] = [
                'title' => 'امتحان: ' . $exam->title . ($exam->subject ? ' - ' . $exam->subject->name : ''),
                'start' => $exam->exam_date->format('Y-m-d') . ($exam->start_time ? 'T' . $exam->start_time : ''),
                'end' => $exam->exam_date->format('Y-m-d') . ($exam->end_time ? 'T' . $exam->end_time : ''),
                'allDay' => false,
                'color' => '#dc2626', // red
                'extendedProps' => [
                    'type' => 'exam',
                    'exam_type' => $exam->type,
                    'duration' => $exam->duration_minutes,
                ]
            ];
        }

        return $events;
    }
}
