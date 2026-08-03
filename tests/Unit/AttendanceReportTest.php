<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AttendanceReportService;
use App\Services\AttendanceAnalyticsService;

class AttendanceReportTest extends TestCase
{
    public function test_service_instantiates()
    {
        $analytics = new AttendanceAnalyticsService();
        $service = new AttendanceReportService($analytics);
        $this->assertInstanceOf(AttendanceReportService::class, $service);
    }
}
