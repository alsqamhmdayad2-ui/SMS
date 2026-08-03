<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\GpaCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GpaCalculationTest extends TestCase
{
    /**
     * A basic unit test to ensure GPA is instantiated.
     */
    public function test_service_instantiates()
    {
        $service = new GpaCalculationService();
        $this->assertInstanceOf(GpaCalculationService::class, $service);
    }
}
