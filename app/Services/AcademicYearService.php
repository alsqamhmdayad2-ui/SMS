<?php

namespace App\Services;

use App\Models\AcademicYear;

class AcademicYearService
{
    public function getAll()
    {
        return AcademicYear::latest()->paginate(10);
    }

    public function create(array $data)
    {
        return AcademicYear::create($data);
    }

    public function update(AcademicYear $academicYear, array $data)
    {
        return $academicYear->update($data);
    }

    public function delete(AcademicYear $academicYear)
    {
        if ($academicYear->classes()->count() > 0) {
            throw new \Exception('Cannot delete an academic year that has classes assigned to it.');
        }

        return $academicYear->delete();
    }
}
