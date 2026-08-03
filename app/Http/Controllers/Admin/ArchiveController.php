<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ParentModel;
use App\Models\Subject;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'students');

        $data = [];
        if ($tab === 'students') {
            $data = Student::onlyTrashed()->with(['grade', 'schoolClass', 'section'])->get();
        } elseif ($tab === 'teachers') {
            $data = Teacher::onlyTrashed()->get();
        } elseif ($tab === 'parents') {
            $data = ParentModel::onlyTrashed()->get();
        } elseif ($tab === 'subjects') {
            $data = Subject::onlyTrashed()->get();
        }

        return view('panels.admin.archive.index', compact('tab', 'data'));
    }

    public function restore(Request $request, $type, $id)
    {
        $model = $this->getModel($type);
        if (!$model) return back()->withErrors('نوع العنصر غير صالح');

        $item = $model::onlyTrashed()->findOrFail($id);
        $item->restore();

        return back()->with('success', 'تم استرجاع العنصر بنجاح.');
    }

    public function forceDelete(Request $request, $type, $id)
    {
        $model = $this->getModel($type);
        if (!$model) return back()->withErrors('نوع العنصر غير صالح');

        $item = $model::onlyTrashed()->findOrFail($id);
        $item->forceDelete();

        return back()->with('success', 'تم حذف العنصر نهائياً.');
    }

    private function getModel($type)
    {
        return match($type) {
            'students' => Student::class,
            'teachers' => Teacher::class,
            'parents'  => ParentModel::class,
            'subjects' => Subject::class,
            default    => null,
        };
    }
}
