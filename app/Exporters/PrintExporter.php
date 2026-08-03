<?php

namespace App\Exporters;

use App\Models\ReportExport;
use App\Enums\ReportType;
use Illuminate\Support\Facades\Auth;

class PrintExporter
{
    /**
     * Render a printable HTML view.
     */
    public function render(array $data, string $viewTemplate, ReportType $reportType, array $filters = []): \Illuminate\Contracts\View\View
    {
        $startTime = microtime(true);

        $data['is_print_mode'] = true;

        $view = view($viewTemplate, $data);

        $durationMs = round((microtime(true) - $startTime) * 1000);
        $this->logExport($reportType, 'PRINT', $filters, null, $durationMs);

        return $view;
    }

    protected function logExport(ReportType $reportType, string $format, array $filters, ?string $fileName, int $durationMs): void
    {
        if (Auth::check()) {
            ReportExport::create([
                'user_id' => Auth::id(),
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
