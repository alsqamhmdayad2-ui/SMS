<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ParentModel;
use App\Models\Subject;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\Grade;

class ArchiveController extends Controller
{
    /**
     * The models map for each tab type.
     */
    private function getModel(string $type): ?string
    {
        return match($type) {
            'students'  => Student::class,
            'teachers'  => Teacher::class,
            'parents'   => ParentModel::class,
            'subjects'  => Subject::class,
            'sections'  => Section::class,
            'classes'   => SchoolClass::class,
            'grades'    => Grade::class,
            default     => null,
        };
    }

    public function index(Request $request)
    {
        $tab    = $request->query('tab', 'students');
        $search = $request->query('search', '');

        // Counts for all tabs (badges)
        $counts = [
            'students'  => Student::onlyTrashed()->count(),
            'teachers'  => Teacher::onlyTrashed()->count(),
            'parents'   => ParentModel::onlyTrashed()->count(),
            'subjects'  => Subject::onlyTrashed()->count(),
            'sections'  => Section::onlyTrashed()->count(),
            'classes'   => SchoolClass::onlyTrashed()->count(),
            'grades'    => Grade::onlyTrashed()->count(),
        ];

        // Fetch data for current tab with optional search
        $data = match($tab) {
            'students' => Student::onlyTrashed()
                ->with(['schoolClass', 'section'])
                ->when($search, fn($q) => $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('family_name', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "%{$search}%"))
                ->latest('deleted_at')
                ->get(),

            'teachers' => Teacher::onlyTrashed()
                ->when($search, fn($q) => $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('family_name', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%"))
                ->latest('deleted_at')
                ->get(),

            'parents' => ParentModel::onlyTrashed()
                ->when($search, fn($q) => $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('family_name', 'like', "%{$search}%")
                    ->orWhere('national_id', 'like', "%{$search}%"))
                ->latest('deleted_at')
                ->get(),

            'subjects' => Subject::onlyTrashed()
                ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%"))
                ->latest('deleted_at')
                ->get(),

            'sections' => Section::onlyTrashed()
                ->with(['schoolClass.grade'])
                ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
                ->latest('deleted_at')
                ->get(),

            'classes' => SchoolClass::onlyTrashed()
                ->with(['grade'])
                ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
                ->latest('deleted_at')
                ->get(),

            'grades' => Grade::onlyTrashed()
                ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
                ->latest('deleted_at')
                ->get(),

            default => collect(),
        };

        return view('panels.admin.archive.index', compact('tab', 'data', 'counts', 'search'));
    }

    public function restore(Request $request, string $type, int $id)
    {
        $model = $this->getModel($type);
        if (!$model) return back()->withErrors('نوع العنصر غير صالح');

        $item = $model::onlyTrashed()->findOrFail($id);
        $item->restore();

        return back()->with('success', 'تم استرجاع العنصر بنجاح. ✅');
    }

    public function forceDelete(Request $request, string $type, int $id)
    {
        $model = $this->getModel($type);
        if (!$model) return back()->withErrors('نوع العنصر غير صالح');

        $item = $model::onlyTrashed()->findOrFail($id);
        $item->forceDelete();

        return back()->with('success', 'تم الحذف النهائي للعنصر.');
    }

    public function emptyTrash(Request $request, string $type)
    {
        $model = $this->getModel($type);
        if (!$model) return back()->withErrors('نوع العنصر غير صالح');

        $count = $model::onlyTrashed()->count();
        $model::onlyTrashed()->forceDelete();

        return back()->with('success', "تم حذف {$count} عنصر نهائياً من السلة.");
    }
}
