<?php

namespace App\Exporters;

use App\Models\ReportExport;
use App\Enums\ReportType;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class ExcelExporter
{
    /**
     * Export data as Excel (.xlsx).
     */
    public function exportXlsx($exportClass, string $fileName, ReportType $reportType, array $filters = [])
    {
        $startTime = microtime(true);

        $response = Excel::download($exportClass, $fileName . '.xlsx');

        $durationMs = round((microtime(true) - $startTime) * 1000);
        $this->logExport($reportType, 'XLSX', $filters, $fileName . '.xlsx', $durationMs);

        return $response;
    }

    /**
     * Export data as CSV.
     */
    public function exportCsv($exportClass, string $fileName, ReportType $reportType, array $filters = [])
    {
        $startTime = microtime(true);

        $response = Excel::download($exportClass, $fileName . '.csv', \Maatwebsite\Excel\Excel::CSV);

        $durationMs = round((microtime(true) - $startTime) * 1000);
        $this->logExport($reportType, 'CSV', $filters, $fileName . '.csv', $durationMs);

        return $response;
    }

    protected function logExport(ReportType $reportType, string $format, array $filters, string $fileName, int $durationMs): void
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
