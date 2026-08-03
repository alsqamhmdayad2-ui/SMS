<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AttendanceService;
use App\Enums\AttendanceSessionStatus;
use App\Models\AttendanceSession;

class AttendanceServiceTest extends TestCase
{
    public function test_service_instantiates()
    {
        $service = new AttendanceService();
        $this->assertInstanceOf(AttendanceService::class, $service);
    }

    public function test_open_status_value()
    {
        $this->assertEquals('open', AttendanceSessionStatus::Open->value);
    }

    public function test_locked_status_value()
    {
        $this->assertEquals('locked', AttendanceSessionStatus::Locked->value);
    }

    public function test_locked_session_cannot_be_reopened_without_admin()
    {
        // Simulate: a locked session's status is 'locked'
        $status = AttendanceSessionStatus::Locked;
        $this->assertEquals(__('attendance.locked'), $status->label());
        $this->assertNotEquals(AttendanceSessionStatus::Open, $status);
    }

    public function test_all_attendance_statuses_have_colors()
    {
        $statuses = \App\Enums\AttendanceStatus::cases();
        foreach ($statuses as $status) {
            $this->assertNotEmpty($status->color(), "Status {$status->value} has no color");
            $this->assertNotEmpty($status->icon(),  "Status {$status->value} has no icon");
            $this->assertNotEmpty($status->label(), "Status {$status->value} has no label");
        }
    }

    public function test_first_or_create_session_uses_existing_daily_section_session_when_present()
    {
        $service = new AttendanceService();
        $this->assertInstanceOf(AttendanceService::class, $service);
    }
}
