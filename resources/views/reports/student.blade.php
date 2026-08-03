@extends('layouts.reports.pdf')
@section('title', 'Student Report Card - ' . $student->name)

@section('content')
<div style="text-align: center; margin-bottom: 20px;">
    <h2>Student Report Card</h2>
    <h3>Academic Year: {{ $student->academicYear->name ?? '' }}</h3>
</div>

<table style="width: 100%; margin-bottom: 20px; border: none;">
    <tr>
        <td style="border: none; padding: 0;">
            <strong>Name:</strong> {{ $student->name }}<br>
            <strong>Student ID:</strong> {{ $student->student_id }}<br>
            <strong>Grade:</strong> {{ $student->grade->name ?? '' }}<br>
            <strong>Section:</strong> {{ $student->section->name ?? '' }}
        </td>
        <td style="border: none; padding: 0; text-align: right;">
            @if(isset($template) && $template->show_qr && isset($verification_uuid))
                <div class="qr-code">
                    <p style="font-size: 10px; color: #777;">Scan to Verify</p>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode(route('verify.report', $verification_uuid)) }}" alt="QR Code">
                </div>
            @endif
        </td>
    </tr>
</table>

@if(!$can_generate_official)
    <div style="background-color: #fff3cd; color: #856404; padding: 10px; border: 1px solid #ffeeba; margin-bottom: 20px; text-align: center;">
        <strong>DRAFT REPORT:</strong> Some subjects have not been officially published yet. This report is not considered final.
    </div>
@endif

<table>
    <thead>
        <tr>
            <th>Subject</th>
            <th style="text-align: center;">Percentage</th>
            <th style="text-align: center;">Grade</th>
            <th style="text-align: center;">GPA</th>
            <th style="text-align: center;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($subjects as $subjectResult)
            <tr>
                <td>
                    <strong>{{ $subjectResult['subject']->name }}</strong>
                    @if(!$subjectResult['is_published'])
                        <span style="color: #dc3545; font-size: 12px; margin-left: 10px;">(Pending)</span>
                    @endif
                </td>
                <td style="text-align: center;">{{ $subjectResult['total_percentage'] }}%</td>
                <td style="text-align: center;">{{ $subjectResult['letter_grade'] ?? '-' }}</td>
                <td style="text-align: center;">{{ $subjectResult['gpa_points'] ?? '-' }}</td>
                <td style="text-align: center;">
                    @if($subjectResult['is_passing'] === true)
                        <span style="color: #28a745;">Pass</span>
                    @elseif($subjectResult['is_passing'] === false)
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
    <h3>Summary</h3>
    <table>
        <tr>
            <th>Total Subjects</th>
            <td>{{ $summary['total_subjects'] }}</td>
            <th>Overall Average</th>
            <td>{{ $summary['average_percentage'] }}%</td>
        </tr>
        <tr>
            <th>Subjects Passed</th>
            <td>{{ $summary['passed'] }}</td>
            <th>Overall GPA</th>
            <td>{{ $summary['overall_gpa'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Subjects Failed</th>
            <td>{{ $summary['failed'] }}</td>
            <th>Final Status</th>
            <td><strong>{{ ucfirst($summary['status']) }}</strong></td>
        </tr>
    </table>
</div>

@if(!isset($is_print_mode))
    <style>
        /* Hide layout elements if viewing directly in browser instead of PDF/Print */
        body { padding: 20px; background-color: #f8f9fa; }
        .content { background: white; padding: 40px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
    </style>
@endif
@endsection
