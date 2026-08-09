@extends('layouts.reports.pdf')
@section('title', 'كشف درجات الطالب - ' . $student->name)

@section('content')
<div style="text-align: center; margin-bottom: 20px;">
    <h2>كشف درجات الطالب</h2>
    <h3>العام الدراسي: {{ $student->academicYear->name ?? '' }}</h3>
</div>

<table style="width: 100%; margin-bottom: 20px; border: none;" dir="rtl">
    <tr>
        <td style="border: none; padding: 0;">
            <strong>الاسم:</strong> {{ $student->name }}<br>
            <strong>الرقم الأكاديمي:</strong> {{ $student->student_id }}<br>
            <strong>الصف:</strong> {{ $student->grade->name ?? '' }}<br>
            <strong>الشعبة:</strong> {{ $student->section->name ?? '' }}
        </td>
        <td style="border: none; padding: 0; text-align: right;">
            @if(isset($template) && $template->show_qr && isset($verification_uuid))
                <div class="qr-code">
                    <p style="font-size: 10px; color: #777;">امسح للتحقق</p>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode(route('verify.report', $verification_uuid)) }}" alt="QR Code">
                </div>
            @endif
        </td>
    </tr>
</table>

@if(!$can_generate_official)
    <div style="background-color: #fff3cd; color: #856404; padding: 10px; border: 1px solid #ffeeba; margin-bottom: 20px; text-align: center;">
        <strong>تقرير غير رسمي:</strong> بعض المواد لم يتم نشر درجاتها رسمياً بعد. هذا التقرير لا يعتبر نهائياً.
    </div>
@endif

<table dir="rtl">
    <thead>
        <tr>
            <th>المادة</th>
            <th style="text-align: center;">النسبة</th>
            <th style="text-align: center;">التقدير</th>
            <th style="text-align: center;">GPA</th>
            <th style="text-align: center;">الحالة</th>
        </tr>
    </thead>
    <tbody>
        @foreach($subjects as $subjectResult)
            <tr>
                <td>
                    <strong>{{ $subjectResult['subject']->name }}</strong>
                    @if(!$subjectResult['is_published'])
                        <span style="color: #dc3545; font-size: 12px; margin-right: 10px;">(قيد الانتظار)</span>
                    @endif
                </td>
                <td style="text-align: center;">{{ $subjectResult['total_percentage'] }}%</td>
                <td style="text-align: center;">{{ $subjectResult['letter_grade'] ?? '-' }}</td>
                <td style="text-align: center;">{{ $subjectResult['gpa_points'] ?? '-' }}</td>
                <td style="text-align: center;">
                    @if($subjectResult['is_passing'] === true)
                        <span style="color: #28a745;">ناجح</span>
                    @elseif($subjectResult['is_passing'] === false)
                        <span style="color: #dc3545;">راسب</span>
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
