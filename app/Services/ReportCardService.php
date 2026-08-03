<?php

namespace App\Services;

use App\Models\ReportCard;
use App\Models\Student;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Enums\ReportCardStatus;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ReportCardService
{
    public function __construct(
        protected ResultPublicationService $publicationService,
        protected GpaCalculationService $gpaService
    ) {}

    /**
     * Generate report cards for an entire section for a given period.
     */
    public function generateForSection($academicYearId, $semesterId, $sectionId, $reportPeriod = 'semester')
    {
        // 1. Verify all subjects are published
        if (!$this->publicationService->canGenerateOfficialReport($academicYearId, $semesterId, $sectionId)) {
            throw ValidationException::withMessages([
                'generate' => 'Cannot generate report cards: Some subjects in this section are still unpublished.'
            ]);
        }

        // 2. Calculate GPAs and Ranks
        $gpaData = $this->gpaService->calculateForSection($academicYearId, $semesterId, $sectionId, $reportPeriod);

        $section = Section::findOrFail($sectionId);
        $academicYear = AcademicYear::findOrFail($academicYearId);
        $students = Student::where('section_id', $sectionId)->get();

        $generatedCards = [];

        foreach ($students as $student) {
            $studentGpa = $gpaData[$student->id] ?? null;
            if (!$studentGpa) continue;

            $card = ReportCard::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYearId,
                    'semester_id' => $semesterId,
                    'report_period' => $reportPeriod,
                ],
                [
                    'section_id' => $section->id,
                    'student_name_snapshot' => $student->name,
                    'section_name_snapshot' => $section->name,
                    'academic_year_name_snapshot' => $academicYear->name,
                    'gpa' => $studentGpa['gpa'],
                    'total_percentage' => $studentGpa['total_percentage'],
                    'rank_in_section' => $studentGpa['rank_in_section'],
                    'academic_status' => $studentGpa['academic_status'],
                    'status' => ReportCardStatus::Generated,
                ]
            );

            $generatedCards[] = $card;
        }

        return $generatedCards;
    }

    /**
     * Publish and lock the report card, generating the verification UUID/Hash.
     */
    public function publish(ReportCard $reportCard, $userId)
    {
        if ($reportCard->status === ReportCardStatus::Published) {
            throw ValidationException::withMessages(['publish' => 'Report card is already published.']);
        }
        if ($reportCard->status === ReportCardStatus::Revoked) {
            throw ValidationException::withMessages(['publish' => 'Cannot publish a revoked report card. Generate a new one.']);
        }

        $uuid = (string) Str::uuid();
        $publishedAt = Carbon::now();

        // Secure Hash: ID + Student + GPA + Date + Secret
        $dataToHash = $reportCard->id . '|' . $reportCard->student_id . '|' . $reportCard->gpa . '|' . $publishedAt->timestamp . '|' . config('app.key');
        $hash = hash('sha256', $dataToHash);

        $reportCard->update([
            'status' => ReportCardStatus::Published,
            'is_locked' => true,
            'locked_at' => $publishedAt,
            'locked_by' => $userId,
            'published_at' => $publishedAt,
            'published_by' => $userId,
            'verification_uuid' => $uuid,
            'verification_hash' => $hash,
        ]);

        return $reportCard;
    }

    /**
     * Revoke an officially published report card (e.g. if an error was found).
     */
    public function revoke(ReportCard $reportCard, $userId)
    {
        if ($reportCard->status !== ReportCardStatus::Published) {
            throw ValidationException::withMessages(['revoke' => 'Only published report cards can be revoked.']);
        }

        $reportCard->update([
            'status' => ReportCardStatus::Revoked,
            // Keep the hash and UUID intact so the public QR scan shows it as REVOKED
        ]);

        return $reportCard;
    }

    /**
     * Verify the integrity of a report card using its UUID.
     */
    public function verify($uuid)
    {
        $reportCard = ReportCard::where('verification_uuid', $uuid)->first();
        
        if (!$reportCard) {
            return ['is_valid' => false, 'message' => 'Report Card not found.'];
        }

        if ($reportCard->status === ReportCardStatus::Revoked) {
            return ['is_valid' => false, 'message' => 'This Document has been REVOKED by the administration.', 'report_card' => $reportCard];
        }

        // Validate Hash to ensure no DB tampering
        $dataToHash = $reportCard->id . '|' . $reportCard->student_id . '|' . $reportCard->gpa . '|' . $reportCard->published_at->timestamp . '|' . config('app.key');
        $expectedHash = hash('sha256', $dataToHash);

        if ($expectedHash !== $reportCard->verification_hash) {
            return ['is_valid' => false, 'message' => 'Document integrity check failed. Possible tampering detected.'];
        }

        return ['is_valid' => true, 'message' => 'Official and Valid Document.', 'report_card' => $reportCard];
    }
}
