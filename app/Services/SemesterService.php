<?php

namespace App\Services;

use App\Models\Semester;

class SemesterService
{
    public function getAll()
    {
        return Semester::with('academicYear')->latest()->get();
    }

    public function create(array $data)
    {
        return Semester::create($data);
    }

    public function update(Semester $semester, array $data)
    {
        $semester->update($data);
        return $semester;
    }

    public function delete(Semester $semester)
    {
        return $semester->delete();
    }
}
