<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReportCard;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Section;
use App\Services\ReportCardService;
use App\Enums\ReportCardStatus;
use App\Exporters\PdfExporter;
use App\Enums\ReportType;
use App\Models\SchoolSetting;

class ReportCardController extends Controller
{
    public function __construct(
        protected ReportCardService $reportCardService,
        protected PdfExporter $pdfExporter
    ) {}

    public function index(Request $request)
    {
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();
        $sections = Section::all();

        $query = ReportCard::with(['student', 'academicYear', 'semester', 'section']);
        
        if ($request->has('academic_year_id') && $request->academic_year_id != '') {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        if ($request->has('section_id') && $request->section_id != '') {
            $query->where('section_id', $request->section_id);
        }

        $reportCards = $query->paginate(20);

        return view('panels.admin.report-cards.index', compact('reportCards', 'academicYears', 'semesters', 'sections'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'section_id' => 'required|exists:sections,id',
            'report_period' => 'required|in:semester,annual',
        ]);

        try {
            $this->reportCardService->generateForSection(
                $request->academic_year_id,
                $request->semester_id,
                $request->section_id,
                $request->report_period
            );

            return back()->with('success', 'تم إصدار الشهادات بنجاح لهذه الشعبة.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    public function publish(ReportCard $reportCard)
    {
        try {
            $this->reportCardService->publish($reportCard, auth()->id());
            return back()->with('success', 'تم اعتماد ونشر الشهادة رسمياً.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    public function revoke(ReportCard $reportCard)
    {
        try {
            $this->reportCardService->revoke($reportCard, auth()->id());
            return back()->with('success', 'تم إلغاء الشهادة بنجاح. رمز التحقق سيعتبر غير صالح الآن.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    public function pdf(ReportCard $reportCard)
    {
        if ($reportCard->status === ReportCardStatus::Draft || $reportCard->status === ReportCardStatus::Revoked) {
            abort(403, 'Cannot print a Draft or Revoked report card officially.');
        }

        // Gather data similar to StudentReportService but locked from snapshot
        // We will build a unified view data array for the official PDF
        
        // For brevity, assuming we load the existing subjects logic or 
        // we can fetch the finalized StudentSubjectGrade records
        $student = $reportCard->student;
        $grades = \App\Models\StudentSubjectGrade::with('subject')
            ->where('student_id', $reportCard->student_id)
            ->where('academic_year_id', $reportCard->academic_year_id)
            ->when($reportCard->semester_id, fn($q) => $q->where('semester_id', $reportCard->semester_id))
            ->get();
            
        $data = [
            'reportCard' => $reportCard,
            'student' => $student,
            'grades' => $grades,
            'school' => SchoolSetting::first(),
            'verification_uuid' => $reportCard->verification_uuid,
            'is_print_mode' => true,
        ];

        return $this->pdfExporter->stream($data, 'reports.official-report-card', ReportType::Student);
    }
}
