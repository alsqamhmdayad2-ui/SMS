<?php

namespace App\Services\Reports;

use App\Enums\ReportType;
use InvalidArgumentException;

class ReportFactory
{
    /**
     * Resolve the correct report service based on the ReportType enum.
     */
    public static function make(ReportType $type): ReportInterface
    {
        return match ($type) {
            ReportType::Student       => app(StudentReportService::class),
            ReportType::Section       => app(SectionReportService::class),
            ReportType::Subject       => app(SubjectReportService::class),
            ReportType::Teacher       => app(TeacherReportService::class),
            ReportType::Annual        => app(AnnualReportService::class),
            ReportType::Grade,
            ReportType::FailedStudents,
            ReportType::HonorStudents,
            ReportType::GPA,
            ReportType::PassRate,
            ReportType::Statistics => app(StatisticsReportService::class),
        };
    }
}
