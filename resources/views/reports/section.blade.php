@extends('layouts.reports.pdf')
@section('title', 'Section Results')

@section('content')
<div style="text-align: center; margin-bottom: 20px;">
    <h2>Section Results Report</h2>
    @if(isset($students[0]))
        <h3>Grade: {{ $students[0]['student']->section->grade->name ?? '' }} | Section: {{ $students[0]['student']->section->name ?? '' }}</h3>
    @endif
</div>

@if(!$can_generate_official)
    <div style="background-color: #fff3cd; color: #856404; padding: 10px; border: 1px solid #ffeeba; margin-bottom: 20px; text-align: center;">
        <strong>DRAFT REPORT:</strong> Some subjects in this section have not been officially published yet.
    </div>
@endif

<div style="margin-bottom: 20px;">
    <h4>Class Statistics</h4>
    <table style="width: 100%;">
        <tr>
            <th>Total Students</th>
            <td>{{ $statistics['total_students'] }}</td>
            <th>Pass Rate</th>
            <td>{{ $statistics['pass_rate'] }}%</td>
        </tr>
        <tr>
            <th>Highest Average</th>
            <td>{{ $statistics['highest_average'] }}%</td>
            <th>Class Average GPA</th>
            <td>{{ $statistics['class_average'] }}</td>
        </tr>
        <tr>
            <th>Lowest Average</th>
            <td>{{ $statistics['lowest_average'] }}%</td>
            <th></th>
            <td></td>
        </tr>
    </table>
</div>

<h4>Student Results</h4>
<table style="width: 100%; font-size: 12px;">
    <thead>
        <tr>
            <th style="width: 30px;">#</th>
            <th>Name</th>
            @foreach($subjects as $subject)
                <th style="text-align: center;">
                    <div style="width: 60px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $subject->name }}">
                        {{ substr($subject->name, 0, 8) }}.
                    </div>
                </th>
            @endforeach
            <th style="text-align: center;">Avg %</th>
            <th style="text-align: center;">GPA</th>
            <th style="text-align: center;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $studentData)
            <tr>
                <td style="text-align: center;">{{ $studentData['rank'] }}</td>
                <td>{{ $studentData['student']->name }}</td>
                
                @foreach($studentData['subjects'] as $subjectScore)
                    <td style="text-align: center;">
                        @if($subjectScore['percentage'] !== null)
                            {{ $subjectScore['percentage'] }}%
                            <br>
                            <small style="color: #666;">{{ $subjectScore['letter_grade'] }}</small>
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                @endforeach
                
                <td style="text-align: center; font-weight: bold;">{{ $studentData['average'] }}%</td>
                <td style="text-align: center;">{{ $studentData['gpa'] ?? '-' }}</td>
                <td style="text-align: center;">
                    @if($studentData['status'] === 'passed')
                        <span style="color: #28a745;">Pass</span>
                    @elseif($studentData['status'] === 'failed')
                        <span style="color: #dc3545;">Fail</span>
                    @else
                        Inc.
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@if(!isset($is_print_mode))
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .content { background: white; padding: 40px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 1200px; margin: 0 auto; overflow-x: auto; }
    </style>
@endif
@endsection
