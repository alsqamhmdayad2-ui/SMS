<?php

namespace App\Http\Controllers\ParentPanel;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;

class ChildrenController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $parent = ParentModel::where('user_id', $user->id)
            ->with(['students.grade', 'students.schoolClass', 'students.section'])
            ->first();

        $children = $parent ? $parent->students : collect();

        return view('panels.parent.children', compact('parent', 'children'));
    }

    public function show($id)
    {
        $user = auth()->user();
        $parent = ParentModel::where('user_id', $user->id)->firstOrFail();

        // SECURITY: Ensure the child belongs to this parent
        $student = $parent->students()
            ->with(['grade', 'schoolClass', 'section'])
            ->where('students.id', $id)
            ->firstOrFail();

        return view('panels.parent.child_profile', compact('parent', 'student'));
    }
}
