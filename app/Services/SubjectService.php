<?php

namespace App\Services;

use App\Models\Subject;

class SubjectService
{
    public function getAllSubjects($search = null)
    {
        $query = Subject::with('classes');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }

        return $query->latest()->paginate(15);
    }

    public function createSubject(array $data)
    {
        return Subject::create($data);
    }

    public function updateSubject(Subject $subject, array $data)
    {
        $subject->update($data);
        return $subject;
    }

    public function deleteSubject(Subject $subject)
    {
        return $subject->delete();
    }

    public function restoreSubject(Subject $subject)
    {
        return $subject->restore();
    }
}
