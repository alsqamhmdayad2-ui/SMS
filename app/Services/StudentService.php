<?php

namespace App\Services;

use App\Models\Student;

class StudentService
{
    public function getAll()
    {
        return Student::with(['grade', 'schoolClass', 'section', 'parent'])->latest()->paginate(10);
    }

    public function create(array $data)
    {
        return Student::create($data);
    }

    public function update(Student $student, array $data)
    {
        return $student->update($data);
    }

    public function delete(Student $student)
    {
        return $student->delete();
    }
}
