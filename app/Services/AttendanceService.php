<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\Student;
use App\Enums\AttendanceSessionStatus;
use App\Enums\AttendanceStatus;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    /**
     * Create a new attendance session.
     * Validates for duplicate sessions before creating.
     */
    /**
     * Get the daily section attendance session.
     * Returns null if it doesn't exist.
     */
    public function getSession(array $data): ?AttendanceSession
    {
        $date = Carbon::parse($data['date'])->toDateString();

        $session = AttendanceSession::where('academic_year_id', $data['academic_year_id'])
            ->where('semester_id', $data['semester_id'])
            ->where('section_id', $data['section_id'])
            ->where('date', $date)
            ->first();

        if ($session) {
            return $session->load('records.student');
        }

        return null;
    }

    /**
     * Create a new attendance session.
     * Validates for duplicate sessions before creating.
     */
    public function createSession(array $data, int $userId): AttendanceSession
    {
        $date = Carbon::parse($data['date'])->toDateString();

        // 1. Conflict check: same section + day + academic year/semester (ignores period)
        $trashedSession = AttendanceSession::onlyTrashed()
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('semester_id', $data['semester_id'])
            ->where('section_id', $data['section_id'])
            ->where('date', $date)
            ->first();

        if ($trashedSession) {
            $trashedSession->forceDelete();
        }

        $exists = AttendanceSession::where('academic_year_id', $data['academic_year_id'])
            ->where('semester_id', $data['semester_id'])
            ->where('section_id', $data['section_id'])
            ->where('date', $date)
            ->exists();

        // 2. Create the session or get existing (handles race conditions automatically)
        $session = AttendanceSession::firstOrCreate([
            'academic_year_id' => $data['academic_year_id'],
            'semester_id'      => $data['semester_id'],
            'section_id'       => $data['section_id'],
            'date'             => $date,
        ], [
            'period_number'    => $data['period_number'],
            'subject_id'       => $data['subject_id'],
            'teacher_id'       => $data['teacher_id'] ?? null,
            'timetable_id'     => $data['timetable_id'] ?? null,
            'status'           => AttendanceSessionStatus::Open,
            'created_by'       => $userId,
        ]);

        // 3. Pre-populate all active students only if it was just created
        if ($session->wasRecentlyCreated) {
            $students = Student::where('section_id', $data['section_id'])
                ->where('status', 'active')
                ->get();

            $records = $students->map(fn($student) => [
                'attendance_session_id' => $session->id,
                'student_id'            => $student->id,
                'status'                => AttendanceStatus::Present->value,
                'marked_by'             => $userId,
                'marked_at'             => now(),
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            AttendanceRecord::insert($records->toArray());
        }

        return $session->load('records.student');
    }

    /**
     * Bulk-update attendance statuses for a session.
     * Throws if session is LOCKED.
     */
    public function updateAttendance(AttendanceSession $session, array $records, int $userId): AttendanceSession
    {
        if ($session->isLocked()) {
            throw ValidationException::withMessages([
                'session' => 'Cannot modify attendance: this session is locked. Contact an administrator to unlock it.',
            ]);
        }

        foreach ($records as $recordData) {
            AttendanceRecord::where('attendance_session_id', $session->id)
                ->where('student_id', $recordData['student_id'])
                ->update([
                    'status'     => $recordData['status'],
                    'remarks'    => $recordData['remarks'] ?? null,
                    'updated_by' => $userId,
                    'updated_at' => now(),
                ]);
        }

        return $session->fresh('records.student');
    }

    /**
     * Lock a session (admin or auto-lock).
     */
    public function lockSession(AttendanceSession $session, int $userId): AttendanceSession
    {
        if ($session->isLocked()) {
            throw ValidationException::withMessages(['session' => 'Session is already locked.']);
        }

        $session->update([
            'status'    => AttendanceSessionStatus::Locked,
            'locked_at' => now(),
            'locked_by' => $userId,
        ]);

        return $session->fresh();
    }

    /**
     * Unlock a session (admin only).
     */
    public function unlockSession(AttendanceSession $session): AttendanceSession
    {
        $session->update([
            'status'    => AttendanceSessionStatus::Open,
            'locked_at' => null,
            'locked_by' => null,
        ]);

        return $session->fresh();
    }

    /**
     * Get all sessions for a section with their records.
     */
    public function getSectionAttendance(int $sectionId, int $academicYearId, ?int $semesterId = null)
    {
        return AttendanceSession::with(['records.student', 'subject', 'teacher'])
            ->where('section_id', $sectionId)
            ->where('academic_year_id', $academicYearId)
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->orderBy('date')
            ->orderBy('period_number')
            ->get();
    }

    /**
     * Get all attendance records for a specific student.
     */
    public function getStudentAttendance(int $studentId, int $academicYearId, ?int $semesterId = null)
    {
        return AttendanceRecord::with(['session.subject', 'session.teacher', 'session.section'])
            ->where('student_id', $studentId)
            ->whereHas('session', function ($q) use ($academicYearId, $semesterId) {
                $q->where('academic_year_id', $academicYearId)
                  ->when($semesterId, fn($q2) => $q2->where('semester_id', $semesterId));
            })
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Admin override: change a single student's attendance with mandatory reason.
     * Creates a full audit trail regardless of session lock state.
     */
    public function adminOverride(
        AttendanceSession $session,
        int $studentId,
        string $newStatus,
        string $reason,
        int $adminUserId
    ): \App\Models\AttendanceRecord {
        $record = AttendanceRecord::where('attendance_session_id', $session->id)
            ->where('student_id', $studentId)
            ->firstOrFail();

        $oldStatus = $record->status->value;

        // 1. Log the override for audit trail
        \App\Models\AttendanceOverride::create([
            'attendance_record_id'  => $record->id,
            'attendance_session_id' => $session->id,
            'student_id'            => $studentId,
            'old_status'            => $oldStatus,
            'new_status'            => $newStatus,
            'overridden_by'         => $adminUserId,
            'overridden_at'         => now(),
            'reason'                => $reason,
        ]);

        // 2. Update the actual record
        $record->update([
            'status'     => $newStatus,
            'updated_by' => $adminUserId,
            'updated_at' => now(),
        ]);

        return $record->fresh();
    }
}

