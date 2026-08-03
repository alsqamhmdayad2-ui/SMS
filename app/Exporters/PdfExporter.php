<?php

namespace App\Exporters;

use App\Models\ReportExport;
use App\Models\ReportTemplate;
use App\Enums\ReportType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class PdfExporter
{
    /**
     * Generate a PDF from report data and a Blade view.
     */
    public function export(array $data, string $viewTemplate, ReportType $reportType, array $filters = []): \Illuminate\Http\Response
    {
        $startTime = microtime(true);

        // Get default template for this report type
        $template = ReportTemplate::where('type', $reportType->value)
            ->where('is_default', true)
            ->where('status', 'active')
            ->first();

        $data['template'] = $template;

        $pdf = Pdf::loadView($viewTemplate, $data);

        if ($template) {
            $pdf->setPaper($template->paper_size, $template->orientation);
            $pdf->setOption('margin-top', $template->margin_top);
            $pdf->setOption('margin-bottom', $template->margin_bottom);
            $pdf->setOption('margin-left', $template->margin_left);
            $pdf->setOption('margin-right', $template->margin_right);
        } else {
            $pdf->setPaper('a4', 'portrait');
        }

        $durationMs = round((microtime(true) - $startTime) * 1000);
        $fileName = $reportType->value . '_report_' . now()->format('Y-m-d_His') . '.pdf';

        // Log the export
        $this->logExport($reportType, 'PDF', $filters, $fileName, $durationMs, $template);

        return $pdf->download($fileName);
    }

    /**
     * Render PDF inline (for preview/print).
     */
    public function stream(array $data, string $viewTemplate, ReportType $reportType, array $filters = []): \Illuminate\Http\Response
    {
        $startTime = microtime(true);

        $template = ReportTemplate::where('type', $reportType->value)
            ->where('is_default', true)
            ->where('status', 'active')
            ->first();

        $data['template'] = $template;

        $pdf = Pdf::loadView($viewTemplate, $data);

        if ($template) {
            $pdf->setPaper($template->paper_size, $template->orientation);
        } else {
            $pdf->setPaper('a4', 'portrait');
        }

        $durationMs = round((microtime(true) - $startTime) * 1000);

        $this->logExport($reportType, 'PDF_STREAM', $filters, null, $durationMs, $template);

        return $pdf->stream($reportType->value . '_report.pdf');
    }

    protected function logExport(ReportType $reportType, string $format, array $filters, ?string $fileName, int $durationMs, ?ReportTemplate $template): void
    {
        if (Auth::check()) {
            ReportExport::create([
                'user_id' => Auth::id(),
                'template_id' => $template?->id,
                'report_type' => $reportType->value,
                'format' => $format,
                'filters' => $filters,
                'status' => 'success',
                'file_name' => $fileName,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'duration_ms' => $durationMs,
                'exported_at' => now(),
            ]);
        }
    }
}
