<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DTOs\ReportFilterData;
use App\Enums\ReportType;
use App\Services\Reports\ReportFactory;
use App\Exporters\PdfExporter;
use App\Exporters\ExcelExporter;
use App\Exporters\PrintExporter;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Student;

class ReportController extends Controller
{
    public function __construct(
        protected PdfExporter $pdfExporter,
        protected ExcelExporter $excelExporter,
        protected PrintExporter $printExporter
    ) {}

    public function index(Request $request)
    {
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();
        $grades = Grade::all();
        $classes = SchoolClass::all();
        $sections = Section::all();
        $subjects = Subject::all();
        $teachers = Teacher::all();
        $students = Student::all();

        return view('panels.admin.reports.index', compact(
            'academicYears', 'semesters', 'grades', 'classes', 'sections', 'subjects', 'teachers', 'students'
        ));
    }

    public function generate(Request $request, string $type)
    {
        $reportType = ReportType::tryFrom($type);
        if (!$reportType) {
            abort(404, 'Report type not found');
        }

        $filters = ReportFilterData::fromRequest($request->all());
        $service = ReportFactory::make($reportType);

        if (!$service->validateAccess($filters)) {
            abort(403, 'Missing required filters to generate this report');
        }

        $data = $service->getData($filters);
        $viewTemplate = $service->getViewTemplate();

        $action = $request->input('action', 'view');

        if ($action === 'pdf') {
            return $this->pdfExporter->export($data, $viewTemplate, $reportType, $request->all());
        } elseif ($action === 'pdf_stream') {
            return $this->pdfExporter->stream($data, $viewTemplate, $reportType, $request->all());
        } elseif ($action === 'excel' || $action === 'csv') {
            // Map ReportType to specific Excel Export class
            $exportClass = $this->resolveExcelExportClass($reportType, $data);
            if (!$exportClass) {
                abort(400, 'Excel export not supported for this report type');
            }

            $fileName = $reportType->value . '_report_' . now()->format('Y-m-d_His');
            if ($action === 'csv') {
                return $this->excelExporter->exportCsv($exportClass, $fileName, $reportType, $request->all());
            }
            return $this->excelExporter->exportXlsx($exportClass, $fileName, $reportType, $request->all());
        } elseif ($action === 'print') {
            return $this->printExporter->render($data, $viewTemplate, $reportType, $request->all());
        }

        // Default view (HTML preview in browser)
        return view($viewTemplate, $data);
    }

    protected function resolveExcelExportClass(ReportType $type, array $data)
    {
        return match($type) {
            ReportType::Section => new \App\Exports\SectionResultExport($data),
            ReportType::Subject => new \App\Exports\SubjectResultExport($data),
            // Add others as needed
            default => null,
        };
    }
}
