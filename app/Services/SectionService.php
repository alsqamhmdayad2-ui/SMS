<?php

namespace App\Services;

use App\Models\Section;

class SectionService
{
    public function getAllSections()
    {
        return Section::with('schoolClass.grade')->latest()->paginate(10);
    }

    public function createSection(array $data)
    {
        return Section::create($data);
    }

    public function updateSection(Section $section, array $data)
    {
        $section->update($data);
        return $section;
    }

    public function deleteSection(Section $section)
    {
        return $section->delete();
    }
}
