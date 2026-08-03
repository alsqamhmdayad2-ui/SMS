<?php

namespace App\Services;

use App\Models\ParentModel;

class ParentService
{
    public function getAll()
    {
        return ParentModel::latest()->paginate(10);
    }

    public function create(array $data)
    {
        return ParentModel::create($data);
    }

    public function update(ParentModel $parent, array $data)
    {
        return $parent->update($data);
    }

    public function delete(ParentModel $parent)
    {
        return $parent->delete();
    }
}
