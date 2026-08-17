<?php

namespace App\Services;

use App\Models\StudentDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Database\Eloquent\Collection;

class DocumentService
{
    /**
     * Get all documents for a specific student.
     *
     * @param int $studentId
     * @return Collection
     */
    public function getStudentDocuments(int $studentId): Collection
    {
        return StudentDocument::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Download a document if it exists and belongs to the student.
     * Checks both 'local' (private) and 'public' disks.
     *
     * @param StudentDocument $document
     * @return StreamedResponse|null
     */
    public function downloadDocument(StudentDocument $document): ?StreamedResponse
    {
        $path = $document->file_path;

        // Check local disk first (preferred for sensitive docs)
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->download($path);
        }

        // Check public disk fallback
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path);
        }

        return null;
    }
}
