@extends('layouts.reports.pdf')
@section('title', 'نتائج المادة - ' . $subject->name)

@section('content')
<div style="text-align: center; margin-bottom: 20px;">
    <h2>تقرير نتائج المادة</h2>
    <h3>المادة: {{ $subject->name }}</h3>
    @if(isset($students[0]))
        <h4>الصف: {{ $students[0]['student']->section->schoolClass->grade->name ?? '' }} | الشعبة: {{ $students[0]['student']->section->name ?? '' }}</h4>
    @endif
</div>

@if(!$is_published)
    <div style="background-color: #fff3cd; color: #856404; padding: 10px; border: 1px solid #ffeeba; margin-bottom: 20px; text-align: center;">
        <strong>تقرير غير رسمي:</strong> لم يتم نشر درجات هذه المادة رسمياً لهذه الشعبة بعد.
    </div>
@endif

<div style="margin-bottom: 20px;">
    <h4>الإحصاءات</h4>
    <table style="width: 100%;">
        <tr>
            <th>إجمالي الطلاب</th>
            <td>{{ $statistics['total_students'] }}</td>
            <th>متوسط النسبة</th>
            <td>{{ $statistics['average'] }}%</td>
        </tr>
        <tr>
            <th>أعلى درجة</th>
            <td>{{ $statistics['highest'] }}%</td>
            <th>أدنى درجة</th>
            <td>{{ $statistics['lowest'] }}%</td>
        </tr>
    </table>
</div>

<h4>الدرجات المفصلة</h4>
<table style="width: 100%; font-size: 13px;" dir="rtl">
    <thead>
        <tr>
            <th style="width: 30px;">#</th>
            <th>الاسم</th>
            @foreach($components as $comp)
                <th style="text-align: center;">{{ $comp->name }}</th>
            @endforeach
            <th style="text-align: center;">الإجمالي %</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $studentData)
            <tr>
                <td style="text-align: center;">{{ $studentData['rank'] }}</td>
                <td>{{ $studentData['student']->name }}</td>
                
                @foreach($components as $comp)
                    @php $score = $studentData['components'][$comp->code] ?? null; @endphp
                    <td style="text-align: center;">
                        @if($score && $score['contribution'] !== null)
                            {{ $score['contribution'] }}
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                @endforeach
                
                <td style="text-align: center; font-weight: bold;">{{ $studentData['total'] }}%</td>
            </tr>
        @endforeach
    </tbody>
</table>

@if(!isset($is_print_mode))
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .content { background: white; padding: 40px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 1000px; margin: 0 auto; overflow-x: auto; }
    </style>
@endif
@endsection
