<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Services\DocumentService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(
        private DocumentService $documentService
    ) {
    }

    public function index()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        $documents = $this->documentService->getStudentDocuments($student->id);

        return view('panels.student.documents', compact('documents'));
    }

    public function download(StudentDocument $document)
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        // Security check: Ensure the document belongs to the currently logged in student
        if ($document->student_id !== $student->id) {
            abort(403, 'غير مصرح لك بتحميل هذا المستند.');
        }

        $response = $this->documentService->downloadDocument($document);

        if (!$response) {
            return back()->with('error', 'عذراً، لم يتم العثور على الملف المطلوب.');
        }

        return $response;
    }
}
