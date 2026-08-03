<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Enums\ReportType;
use App\Services\Reports\ReportFactory;
use App\Services\Reports\StudentReportService;
use App\Services\Reports\SectionReportService;
use App\Services\Reports\StatisticsReportService;

class ReportFactoryTest extends TestCase
{
    public function test_it_resolves_student_report_service()
    {
        $service = ReportFactory::make(ReportType::Student);
        $this->assertInstanceOf(StudentReportService::class, $service);
    }

    public function test_it_resolves_section_report_service()
    {
        $service = ReportFactory::make(ReportType::Section);
        $this->assertInstanceOf(SectionReportService::class, $service);
    }

    public function test_it_resolves_statistics_report_service()
    {
        $service = ReportFactory::make(ReportType::Statistics);
        $this->assertInstanceOf(StatisticsReportService::class, $service);
    }
}
