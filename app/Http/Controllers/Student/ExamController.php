<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;

class ExamController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'حساب الطالب غير مكتمل البيانات.');
        }

        // All exams for student's section
        $allExams = \App\Models\Exam::with('subject')
            ->where('section_id', $student->section_id)
            ->whereIn('status', ['published', 'locked'])
            ->orderBy('exam_date', 'desc')
            ->get();

        // Get student's results
        $results = \App\Models\ExamResult::where('student_id', $student->id)
            ->get()
            ->keyBy('exam_id');

        $availableExams = [];
        $completedExams = [];

        foreach ($allExams as $exam) {
            if ($results->has($exam->id)) {
                $completedExams[] = [
                    'exam' => $exam,
                    'result' => $results->get($exam->id)
                ];
            } else {
                if ($exam->status->value === 'published') {
                    $availableExams[] = $exam;
                }
            }
        }

        return view('panels.student.exams', compact('student', 'availableExams', 'completedExams'));
    }
}
