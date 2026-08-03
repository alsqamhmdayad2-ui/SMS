<?php

namespace App\Services;

use App\Models\SchoolClass;

class SchoolClassService
{
    public function getAll()
    {
        // Avoid N+1 Query Problem by eager loading grade
        return SchoolClass::with(['grade', 'academicYear'])->latest()->paginate(10);
    }

    public function create(array $data)
    {
        if (!isset($data['status'])) {
            $data['status'] = false;
        }
        return SchoolClass::create($data);
    }

    public function update(SchoolClass $schoolClass, array $data)
    {
        if (!isset($data['status'])) {
            $data['status'] = false;
        }
        return $schoolClass->update($data);
    }

    public function delete(SchoolClass $schoolClass)
    {
        return $schoolClass->delete();
    }
}
