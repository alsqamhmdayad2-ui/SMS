@extends('layouts.reports.pdf')
@section('title', 'تقرير الإحصاءات الأكاديمية')

@section('content')
<div style="text-align: center; margin-bottom: 20px;">
    <h2>تقرير الإحصاءات الأكاديمية</h2>
</div>

<table style="width: 100%; margin-bottom: 30px;" dir="rtl">
    <tr>
        <th colspan="4" style="text-align: center; font-size: 16px;">نظرة عامة</th>
    </tr>
    <tr>
        <th>إجمالي الدرجات المسجلة</th>
        <td>{{ $overview['total_grades'] }}</td>
        <th>إجمالي الطلاب المقيّمين</th>
        <td>{{ $overview['total_students'] }}</td>
    </tr>
    <tr>
        <th>نسبة النجاح العامة</th>
        <td style="font-weight: bold; color: {{ $pass_rate >= 50 ? '#28a745' : '#dc3545' }};">{{ $pass_rate }}%</td>
        <th>متوسط المعدل التراكمي (GPA)</th>
        <td>{{ $average_gpa }}</td>
    </tr>
</table>

<div style="page-break-inside: avoid; margin-bottom: 30px;">
    <h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px;">توزيع التقديرات</h3>
    <table style="width: 100%;" dir="rtl">
        <thead>
            <tr>
                <th>التقدير</th>
                <th>النطاق</th>
                <th>العدد</th>
                <th>النسبة المئوية</th>
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
    <h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px;">أفضل المواد أداءً</h3>
    <table style="width: 100%;" dir="rtl">
        <thead>
            <tr>
                <th>المادة</th>
                <th style="text-align: center;">المتوسط %</th>
                <th style="text-align: center;">نسبة النجاح</th>
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
                <tr><td colspan="3" style="text-align: center;">لا توجد بيانات متاحة</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="page-break-inside: avoid; margin-bottom: 30px;">
    <h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px;">مواد تحتاج إلى تحسين</h3>
    <table style="width: 100%;" dir="rtl">
        <thead>
            <tr>
                <th>المادة</th>
                <th style="text-align: center;">المتوسط %</th>
                <th style="text-align: center;">نسبة النجاح</th>
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
                <tr><td colspan="3" style="text-align: center;">لا توجد بيانات متاحة</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="page-break-inside: avoid; margin-bottom: 30px;">
    <h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px;">لوحة الشرف (أفضل 10 طلاب)</h3>
    <table style="width: 100%;" dir="rtl">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>اسم الطالب</th>
                <th style="text-align: center;">التقدير (GPA)</th>
                <th style="text-align: center;">المتوسط %</th>
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
                <tr><td colspan="4" style="text-align: center;">لا توجد بيانات متاحة</td></tr>
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
