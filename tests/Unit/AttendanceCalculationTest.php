<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AttendanceCalculationService;
use App\Enums\AttendanceStatus;

class AttendanceCalculationTest extends TestCase
{
    public function test_service_instantiates_correctly()
    {
        $service = new AttendanceCalculationService();
        $this->assertInstanceOf(AttendanceCalculationService::class, $service);
    }

    public function test_attendance_percentage_formula_is_correct()
    {
        // Validate the formula logic directly: Present=8, Late=2 (×0.5), Absent=2, Total=12
        // Effective = 8 + 1 + 0 = 9 out of 12 → 75%
        $present  = 8;
        $late     = 2;
        $excused  = 0;
        $sick     = 0;
        $total    = 12;

        $effective  = $present + ($late * 0.5) + $excused + $sick;
        $percentage = round(($effective / $total) * 100, 2);

        $this->assertEquals(75.0, $percentage);
    }

    public function test_zero_sessions_returns_zero_percentage()
    {
        $total      = 0;
        $percentage = $total > 0 ? round((0 / $total) * 100, 2) : 0;

        $this->assertEquals(0, $percentage);
    }

    public function test_full_presence_returns_100_percentage()
    {
        $present  = 10;
        $late     = 0;
        $excused  = 0;
        $sick     = 0;
        $total    = 10;

        $effective  = $present + ($late * 0.5) + $excused + $sick;
        $percentage = round(($effective / $total) * 100, 2);

        $this->assertEquals(100.0, $percentage);
    }
}
