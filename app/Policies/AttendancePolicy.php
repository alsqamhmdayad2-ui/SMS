<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AttendanceSession;

class AttendancePolicy
{
    /**
     * Teacher: can only view/mark sessions that belong to them.
     */
    public function viewOwnSessions(User $user, AttendanceSession $session): bool
    {
        return $user->teacher?->id === $session->teacher_id;
    }

    /**
     * Teacher: can mark attendance only if session is open and belongs to them.
     */
    public function markAttendance(User $user, AttendanceSession $session): bool
    {
        return $user->teacher?->id === $session->teacher_id
            && $session->isOpen();
    }

    /**
     * Admin: can override any record regardless of lock state.
     */
    public function override(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('principal');
    }

    /**
     * Admin: can unlock locked sessions.
     */
    public function unlock(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('principal');
    }

    /**
     * Admin/Teacher: can view reports.
     */
    public function viewReports(User $user): bool
    {
        return $user->hasRole('admin')
            || $user->hasRole('principal')
            || $user->hasRole('teacher');
    }

    /**
     * Student: can view their own attendance.
     */
    public function viewOwnAttendance(User $user): bool
    {
        return $user->student !== null;
    }

    /**
     * Parent: can view their child's attendance.
     */
    public function viewChildAttendance(User $user): bool
    {
        return $user->hasRole('parent');
    }
}
