<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الشهادة المدرسية</title>
    <style>
        @page {
            margin: 25px;
            header: page-header;
            footer: page-footer;
        }
        body {
            font-family: 'dejavu sans', sans-serif;
            font-size: 14px;
            color: #000;
            line-height: 1.4;
        }
        .page-break {
            page-break-after: always;
        }
        .certificate-container {
            border: 4px solid #1e3a5f;
            padding: 20px;
            min-height: 950px;
            position: relative;
        }
        .inner-border {
            border: 1px solid #1e3a5f;
            padding: 15px;
            min-height: 920px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h3, .header h4, .header h5 {
            margin: 5px 0;
            font-weight: bold;
        }
        .header-logo {
            width: 80px;
            margin-bottom: 10px;
        }
        
        .student-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .student-info td {
            padding: 6px;
            border: 1px solid #000;
            font-size: 13px;
        }
        .student-info .label {
            background-color: #f2f2f2;
            font-weight: bold;
            width: 15%;
            text-align: center;
        }
        .student-info .value {
            width: 35%;
            text-align: center;
        }

        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .grades-table th, .grades-table td {
            border: 1px solid #000;
            padding: 8px 4px;
            text-align: center;
            font-size: 13px;
        }
        .grades-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .subject-col {
            text-align: right !important;
            padding-right: 10px !important;
            font-weight: bold;
            width: 25%;
        }

        .footer-section {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .footer-section td {
            border: 1px solid #000;
            padding: 10px;
            vertical-align: top;
        }
        .footer-title {
            font-weight: bold;
            margin-bottom: 30px;
        }
        .signature-area {
            text-align: center;
            margin-top: 20px;
        }
        
        .result-row td {
            font-weight: bold;
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

    @foreach($studentsData as $data)
    <div class="certificate-container">
        <div class="inner-border">
            
            <div class="header">
                <h3>بسم الله الرحمن الرحيم</h3>
                <h4>دولة فلسطين</h4>
                <h4>وزارة التربية والتعليم العالي</h4>
                <h4>{{ \App\Models\SchoolSetting::first()->school_name ?? 'المدرسة الأساسية' }}</h4>
                <h5>النتائج المدرسية للعام الدراسي {{ $academicYear->name }}</h5>
                <h5>{{ $section->schoolClass->name }} - {{ $section->name }}</h5>
                @if($certificateType === 'semester')
                <h5 style="color: #666;">(نتيجة {{ $semester->name }})</h5>
                @endif
            </div>

            <table class="student-info">
                <tr>
                    <td class="label">اسم الطالب</td>
                    <td class="value"><strong>{{ $data['student']->full_name }}</strong></td>
                    <td class="label">رقم الهوية</td>
                    <td class="value">{{ $data['student']->national_id ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">تاريخ الميلاد</td>
                    <td class="value">{{ $data['student']->date_of_birth ? $data['student']->date_of_birth->format('Y/m/d') : '-' }}</td>
                    <td class="label">الجنسية</td>
                    <td class="value">{{ $data['student']->nationality ?? 'فلسطينية' }}</td>
                </tr>
            </table>

            <table class="grades-table">
                <thead>
                    <tr>
                        <th>المبحث</th>
                        <th>النهاية<br>العظمى</th>
                        <th>النهاية<br>الصغرى</th>
                        
                        @if($certificateType === 'annual')
                            <th>الفصل الأول<br>(50)</th>
                            <th>الفصل الثاني<br>(50)</th>
                            <th>المجموع<br>(100)</th>
                        @else
                            <th>المجموع<br>(100)</th>
                        @endif
                        
                        <th>التقدير</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['subjects'] as $sub)
                    <tr>
                        <td class="subject-col">{{ $sub['name'] }}</td>
                        <td>100</td>
                        <td>50</td>
                        
                        @if($certificateType === 'annual')
                            <td>
                                {{ $sub['sem1_50'] ?? '-' }}
                                <br><small style="font-size:10px;">({{ $sub['sem1_grade'] }})</small>
                            </td>
                            <td>
                                {{ $sub['sem2_50'] ?? '-' }}
                                <br><small style="font-size:10px;">({{ $sub['sem2_grade'] }})</small>
                            </td>
                            <td><strong>{{ $sub['annual_total'] }}</strong></td>
                            <td><strong>{{ $sub['annual_grade'] }}</strong></td>
                        @else
                            {{-- Semester Specific --}}
                            @php 
                                $semVal = request()->semester_id == 1 ? $sub['sem1_100'] : $sub['sem2_100'];
                                $semGrd = request()->semester_id == 1 ? $sub['sem1_grade'] : $sub['sem2_grade'];
                            @endphp
                            <td><strong>{{ $semVal ?? '-' }}</strong></td>
                            <td><strong>{{ $semGrd }}</strong></td>
                        @endif
                        
                        <td></td>
                    </tr>
                    @endforeach
                    
                    <tr class="result-row">
                        <td colspan="3" style="text-align: left; padding-left:15px;">المعدل العام: ( {{ $data['percentage'] }} % )</td>
                        <td colspan="{{ $certificateType === 'annual' ? '5' : '3' }}">النتيجة النهائية: {{ $data['is_passing'] ? 'ناجـح' : 'راسـب' }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="footer-section">
                <tr>
                    <td style="width: 50%;">
                        <div class="footer-title">ملحوظات مربي/ة الصف:</div>
                        <div style="border-bottom: 1px dotted #000; margin-top: 30px;"></div>
                        <div style="border-bottom: 1px dotted #000; margin-top: 30px;"></div>
                        
                        <div class="signature-area" style="margin-top:40px;">
                            توقيع مربي/ة الصف: ..........................
                        </div>
                    </td>
                    <td style="width: 50%;">
                        <div class="footer-title">ملحوظات مدير/ة المدرسة:</div>
                        <div style="border-bottom: 1px dotted #000; margin-top: 30px;"></div>
                        <div style="border-bottom: 1px dotted #000; margin-top: 30px;"></div>
                        
                        <div class="signature-area">
                            خاتم المدرسة<br><br><br>
                            توقيع مدير/ة المدرسة: ..........................
                        </div>
                    </td>
                </tr>
            </table>
            
            <div style="margin-top: 15px; font-size: 11px; text-align: right; color: #444;">
                <strong>تنبيهات هامة:</strong><br>
                1) العلامات والتقديرات: ممتاز من 90-100، جيد جداً 80-89، جيد 70-79، مقبول 60-69، ضعيف 50-59، راسب 49 فما دون.<br>
                2) تاريخ إصدار الشهادة: {{ date('Y/m/d') }}
            </div>

        </div>
    </div>
    
    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
    @endforeach

</body>
</html>
