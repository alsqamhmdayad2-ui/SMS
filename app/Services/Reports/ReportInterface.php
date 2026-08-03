<?php

namespace App\Services\Reports;

use App\DTOs\ReportFilterData;

interface ReportInterface
{
    /**
     * Get the raw data for the report.
     */
    public function getData(ReportFilterData $filters): array;

    /**
     * Get the view template path for the report.
     */
    public function getViewTemplate(): string;
    
    /**
     * Validate if the user can generate this report with given filters.
     */
    public function validateAccess(ReportFilterData $filters): bool;
}
