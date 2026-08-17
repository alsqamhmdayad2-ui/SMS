<?php

namespace App\Http\Controllers\ParentPanel;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use App\Models\StudentDocument;
use App\Services\DocumentService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(
        private DocumentService $documentService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $parent = ParentModel::where('user_id', $user->id)
            ->with(['students'])
            ->firstOrFail();

        $children = $parent->students;
        
        if ($children->isEmpty()) {
            return view('panels.parent.documents', [
                'parent' => $parent,
                'children' => $children,
                'selectedChild' => null,
                'documents' => collect()
            ]);
        }

        $studentId = $request->query('student_id');
        
        if ($studentId) {
            // Authorization check + strict fetch
            $selectedChild = $parent->students()
                ->where('students.id', $studentId)
                ->firstOrFail();
        } else {
            // Default to first child
            $selectedChild = $children->first();
        }

        $documents = collect();
        if ($selectedChild) {
            $documents = $this->documentService->getStudentDocuments($selectedChild->id);
        }

        return view('panels.parent.documents', compact('parent', 'children', 'selectedChild', 'documents'));
    }

    public function download(StudentDocument $document)
    {
        $user = auth()->user();
        $parent = ParentModel::where('user_id', $user->id)->firstOrFail();

        // Security check: Ensure the document belongs to a student of this parent
        $isChildOfParent = $parent->students()->where('students.id', $document->student_id)->exists();
        
        if (!$isChildOfParent) {
            abort(403, 'غير مصرح لك بتحميل هذا المستند.');
        }

        $response = $this->documentService->downloadDocument($document);

        if (!$response) {
            return back()->with('error', 'عذراً، لم يتم العثور على الملف المطلوب.');
        }

        return $response;
    }
}
