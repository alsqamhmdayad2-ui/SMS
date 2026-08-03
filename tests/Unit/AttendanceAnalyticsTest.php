<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AttendanceAnalyticsService;
use App\Enums\AttendanceStatus;
use App\DTOs\AttendanceFilterData;

class AttendanceAnalyticsTest extends TestCase
{
    public function test_service_instantiates()
    {
        $service = new AttendanceAnalyticsService();
        $this->assertInstanceOf(AttendanceAnalyticsService::class, $service);
    }

    public function test_filter_dto_parsing()
    {
        $data = [
            'academic_year_id' => '1',
            'section_id'       => '2',
            'status'           => 'open',
            'empty_field'      => ''
        ];

        $dto = AttendanceFilterData::fromRequest($data);

        $this->assertEquals(1, $dto->academicYearId);
        $this->assertEquals(2, $dto->sectionId);
        $this->assertEquals('open', $dto->status);
        $this->assertNull($dto->semesterId); // Not provided
    }
}
