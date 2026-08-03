<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportCardService;
use App\Models\SchoolSetting;

class VerificationController extends Controller
{
    public function __construct(
        protected ReportCardService $reportCardService
    ) {}

    public function verify($uuid)
    {
        $verificationResult = $this->reportCardService->verify($uuid);
        $school = SchoolSetting::first();

        return view('public.verify-report', [
            'result' => $verificationResult,
            'school' => $school
        ]);
    }
}
