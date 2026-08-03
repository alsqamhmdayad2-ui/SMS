<?php

namespace App\Services;

use App\Models\Grade;

class GradeService
{
    public function getAll()
    {
        return Grade::latest()->paginate(10);
    }

    public function create(array $data)
    {
        if (!isset($data['status'])) {
            $data['status'] = false;
        }
        return Grade::create($data);
    }

    public function update(Grade $grade, array $data)
    {
        if (!isset($data['status'])) {
            $data['status'] = false;
        }
        return $grade->update($data);
    }

    public function delete(Grade $grade)
    {
        return $grade->delete();
    }
}
