@extends('layouts.reports.pdf')
@section('title', 'Academic Statistics Report')

@section('content')
<div style="text-align: center; margin-bottom: 20px;">
    <h2>Academic Statistics Report</h2>
</div>

<table style="width: 100%; margin-bottom: 30px;">
    <tr>
        <th colspan="4" style="text-align: center; font-size: 16px;">General Overview</th>
    </tr>
    <tr>
        <th>Total Grades Recorded</th>
        <td>{{ $overview['total_grades'] }}</td>
        <th>Total Students Assessed</th>
        <td>{{ $overview['total_students'] }}</td>
    </tr>
    <tr>
        <th>Overall Pass Rate</th>
        <td style="font-weight: bold; color: {{ $pass_rate >= 50 ? '#28a745' : '#dc3545' }};">{{ $pass_rate }}%</td>
        <th>Average GPA</th>
        <td>{{ $average_gpa }}</td>
    </tr>
</table>

<div style="page-break-inside: avoid; margin-bottom: 30px;">
    <h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px;">Grade Distribution</h3>
    <table style="width: 100%;">
        <thead>
            <tr>
                <th>Letter Grade</th>
                <th>Range</th>
                <th>Count</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grade_distribution as $dist)
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $dist['letter'] }}</td>
                    <td style="text-align: center;">{{ $dist['range'] }}</td>
                    <td style="text-align: center;">{{ $dist['count'] }}</td>
                    <td style="text-align: center;">{{ $dist['percentage'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div style="page-break-inside: avoid; margin-bottom: 30px;">
    <h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px;">Top Performing Subjects</h3>
    <table style="width: 100%;">
        <thead>
            <tr>
                <th>Subject</th>
                <th style="text-align: center;">Average %</th>
                <th style="text-align: center;">Pass Rate</th>
            </tr>
        </thead>
        <tbody>
            @forelse($top_subjects as $stat)
                <tr>
                    <td>{{ $stat['subject']->name }}</td>
                    <td style="text-align: center;">{{ $stat['average'] }}%</td>
                    <td style="text-align: center;">{{ $stat['pass_rate'] }}%</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align: center;">No data available</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="page-break-inside: avoid; margin-bottom: 30px;">
    <h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px;">Subjects Requiring Attention</h3>
    <table style="width: 100%;">
        <thead>
            <tr>
                <th>Subject</th>
                <th style="text-align: center;">Average %</th>
                <th style="text-align: center;">Pass Rate</th>
            </tr>
        </thead>
        <tbody>
            @forelse($weakest_subjects as $stat)
                <tr>
                    <td>{{ $stat['subject']->name }}</td>
                    <td style="text-align: center;">{{ $stat['average'] }}%</td>
                    <td style="text-align: center;">{{ $stat['pass_rate'] }}%</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align: center;">No data available</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="page-break-inside: avoid; margin-bottom: 30px;">
    <h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px;">Honor Roll (Top 10 Students)</h3>
    <table style="width: 100%;">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>Student Name</th>
                <th style="text-align: center;">GPA</th>
                <th style="text-align: center;">Average %</th>
            </tr>
        </thead>
        <tbody>
            @forelse($honor_students as $index => $honor)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $honor['student']->name }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $honor['gpa'] }}</td>
                    <td style="text-align: center;">{{ $honor['average'] }}%</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align: center;">No data available</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(!isset($is_print_mode))
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .content { background: white; padding: 40px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 900px; margin: 0 auto; overflow-x: auto; }
    </style>
@endif
@endsection
