@extends('layouts.reports.pdf')
@section('title', 'Official Report Card - ' . $student->name)

@section('content')
<div style="text-align: center; margin-bottom: 20px;">
    <h2>OFFICIAL REPORT CARD</h2>
    <h3>Academic Year: {{ $reportCard->academic_year_name_snapshot }}</h3>
    @if($reportCard->semester_id)
        <h4>Semester: {{ $reportCard->semester->name ?? '' }}</h4>
    @endif
</div>

<table style="width: 100%; margin-bottom: 20px; border: none;">
    <tr>
        <td style="border: none; padding: 0;">
            <strong>Student Name:</strong> {{ $reportCard->student_name_snapshot }}<br>
            <strong>Student ID:</strong> {{ $student->student_id }}<br>
            <strong>Section:</strong> {{ $reportCard->section_name_snapshot }}<br>
            <strong>Period:</strong> {{ ucfirst($reportCard->report_period) }}
        </td>
        <td style="border: none; padding: 0; text-align: right;">
            @if($verification_uuid)
                <div class="qr-code">
                    <p style="font-size: 10px; color: #777; margin-bottom: 2px;">Scan to Verify Authenticity</p>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode(route('verify.report', $verification_uuid)) }}" alt="QR Code">
                    <p style="font-size: 8px; color: #999; margin-top: 2px;">{{ substr($verification_uuid, 0, 8) }}...</p>
                </div>
            @endif
        </td>
    </tr>
</table>

@if($reportCard->status === App\Enums\ReportCardStatus::Revoked)
    <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border: 2px solid #f5c6cb; margin-bottom: 20px; text-align: center; font-weight: bold; font-size: 18px;">
        WARNING: THIS DOCUMENT HAS BEEN REVOKED BY THE ADMINISTRATION AND IS NO LONGER VALID.
    </div>
@endif

<table>
    <thead>
        <tr>
            <th>Subject</th>
            <th style="text-align: center;">Percentage</th>
            <th style="text-align: center;">GPA Points</th>
            <th style="text-align: center;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($grades as $grade)
            <tr>
                <td><strong>{{ $grade->subject->name }}</strong></td>
                <td style="text-align: center;">{{ $grade->total_percentage }}%</td>
                <td style="text-align: center;">{{ $grade->gpa_points ?? '-' }}</td>
                <td style="text-align: center;">
                    @if($grade->is_passing === true)
                        <span style="color: #28a745;">Pass</span>
                    @elseif($grade->is_passing === false)
                        <span style="color: #dc3545;">Fail</span>
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div style="margin-top: 30px;">
    <h3>Final Results</h3>
    <table style="background-color: #fcfcfc;">
        <tr>
            <th style="width: 25%;">Overall GPA</th>
            <td style="width: 25%; font-weight: bold; font-size: 16px;">{{ $reportCard->gpa }}</td>
            <th style="width: 25%;">Total Percentage</th>
            <td style="width: 25%;">{{ $reportCard->total_percentage }}%</td>
        </tr>
        <tr>
            <th>Academic Status</th>
            <td>
                @if($reportCard->academic_status === 'Pass')
                    <strong style="color: #28a745;">PASS</strong>
                @elseif($reportCard->academic_status === 'Fail')
                    <strong style="color: #dc3545;">FAIL</strong>
                @else
                    <strong>{{ strtoupper($reportCard->academic_status) }}</strong>
                @endif
            </td>
            <th>Rank in Section</th>
            <td>{{ $reportCard->rank_in_section }}</td>
        </tr>
    </table>
</div>

<div style="margin-top: 20px; font-size: 11px; color: #666;">
    <p><strong>Official Document Statement:</strong></p>
    <p>This report card is an official document issued by {{ $school->school_name ?? 'the institution' }}. Any unauthorized alteration, forgery, or tampering will render this document invalid. You may verify the authenticity of this document by scanning the QR code provided at the top right corner.</p>
</div>
@endsection
