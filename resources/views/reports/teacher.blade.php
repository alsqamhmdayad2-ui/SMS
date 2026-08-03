@extends('layouts.reports.pdf')
@section('title', 'Teacher Report - ' . $teacher->name)

@section('content')
<div style="text-align: center; margin-bottom: 20px;">
    <h2>Teacher Performance Report</h2>
    <h3>Teacher: {{ $teacher->name }}</h3>
</div>

@foreach($sections as $sectionData)
    <div style="margin-top: 30px; page-break-inside: avoid;">
        <h4 style="background-color: #f2f2f2; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd;">
            Subject: {{ $sectionData['subject']->name }} 
            @if(isset($sectionData['students'][0]))
                | Grade: {{ $sectionData['students'][0]['student']->section->grade->name ?? '' }} 
                | Section: {{ $sectionData['students'][0]['student']->section->name ?? '' }}
            @endif
        </h4>
        
        <table style="width: 100%; margin-bottom: 15px; border: none; font-size: 13px;">
            <tr>
                <td style="border: none; padding: 5px;"><strong>Total Students:</strong> {{ $sectionData['statistics']['total'] }}</td>
                <td style="border: none; padding: 5px;"><strong>Pass Rate:</strong> {{ $sectionData['statistics']['pass_rate'] }}%</td>
                <td style="border: none; padding: 5px;"><strong>Average %:</strong> {{ $sectionData['statistics']['average'] }}%</td>
                <td style="border: none; padding: 5px;"><strong>Highest:</strong> {{ $sectionData['statistics']['highest'] }}%</td>
                <td style="border: none; padding: 5px;"><strong>Lowest:</strong> {{ $sectionData['statistics']['lowest'] }}%</td>
            </tr>
        </table>

        <table style="width: 100%; font-size: 13px;">
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th>Student Name</th>
                    <th style="text-align: center;">Percentage</th>
                    <th style="text-align: center;">Grade</th>
                    <th style="text-align: center;">GPA</th>
                    <th style="text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sectionData['students'] as $studentData)
                    <tr>
                        <td style="text-align: center;">{{ $studentData['rank'] }}</td>
                        <td>{{ $studentData['student']->name }}</td>
                        <td style="text-align: center;">{{ $studentData['percentage'] !== null ? $studentData['percentage'] . '%' : '-' }}</td>
                        <td style="text-align: center;">{{ $studentData['letter_grade'] }}</td>
                        <td style="text-align: center;">{{ $studentData['gpa'] ?? '-' }}</td>
                        <td style="text-align: center;">
                            @if($studentData['status'] === 'pass')
                                <span style="color: #28a745;">Pass</span>
                            @elseif($studentData['status'] === 'fail')
                                <span style="color: #dc3545;">Fail</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endforeach

@if(count($sections) == 0)
    <p style="text-align: center; color: #777;">No assigned subjects/exams found for this teacher in the selected criteria.</p>
@endif

@if(!isset($is_print_mode))
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .content { background: white; padding: 40px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 900px; margin: 0 auto; overflow-x: auto; }
    </style>
@endif
@endsection
