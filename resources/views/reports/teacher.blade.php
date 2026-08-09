@extends('layouts.reports.pdf')
@section('title', 'تقرير المعلم - ' . $teacher->name)

@section('content')
<div style="text-align: center; margin-bottom: 20px;">
    <h2>تقرير أداء المعلم</h2>
    <h3>المعلم: {{ $teacher->name }}</h3>
</div>

@foreach($sections as $sectionData)
    <div style="margin-top: 30px; page-break-inside: avoid;">
        <h4 style="background-color: #f2f2f2; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd;">
            المادة: {{ $sectionData['subject']->name }} 
            @if(isset($sectionData['students'][0]))
                | الصف: {{ $sectionData['students'][0]['student']->section->schoolClass->grade->name ?? '' }} 
                | الشعبة: {{ $sectionData['students'][0]['student']->section->name ?? '' }}
            @endif
        </h4>
        
        <table style="width: 100%; margin-bottom: 15px; border: none; font-size: 13px;" dir="rtl">
            <tr>
                <td style="border: none; padding: 5px;"><strong>إجمالي الطلاب:</strong> {{ $sectionData['statistics']['total'] }}</td>
                <td style="border: none; padding: 5px;"><strong>نسبة النجاح:</strong> {{ $sectionData['statistics']['pass_rate'] }}%</td>
                <td style="border: none; padding: 5px;"><strong>المتوسط %:</strong> {{ $sectionData['statistics']['average'] }}%</td>
                <td style="border: none; padding: 5px;"><strong>أعلى درجة:</strong> {{ $sectionData['statistics']['highest'] }}%</td>
                <td style="border: none; padding: 5px;"><strong>أدنى درجة:</strong> {{ $sectionData['statistics']['lowest'] }}%</td>
            </tr>
        </table>

        <table style="width: 100%; font-size: 13px;" dir="rtl">
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th>اسم الطالب</th>
                    <th style="text-align: center;">النسبة</th>
                    <th style="text-align: center;">التقدير</th>
                    <th style="text-align: center;">GPA</th>
                    <th style="text-align: center;">الحالة</th>
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
                                <span style="color: #28a745;">ناجح</span>
                            @elseif($studentData['status'] === 'fail')
                                <span style="color: #dc3545;">راسب</span>
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
