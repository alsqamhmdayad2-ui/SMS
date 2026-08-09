<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتائج الطالب: {{ $student->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
        body { background-color: #fff; font-family: 'Cairo', sans-serif; color: #000; direction: rtl; }
        .print-container { max-width: 820px; margin: 0 auto; padding: 40px 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px double #000; padding-bottom: 20px; }
        .school-name { font-size: 26px; font-weight: 700; margin-bottom: 4px; }
        .report-title { font-size: 18px; font-weight: 700; letter-spacing: 1px; margin-top: 10px; }
        .student-info th { width: 140px; text-align: right; font-weight: 600; }
        .student-info th, .student-info td { padding: 5px 8px; }
        .result-table th { background-color: #f0f0f0 !important; border-bottom: 2px solid #000; }
        .result-table td, .result-table th { border: 1px solid #ccc; vertical-align: middle; text-align: center; }
        .result-table td:first-child { text-align: right; }
        .summary-box { border: 2px solid #000; padding: 15px; margin-top: 25px; }
        .signatures { margin-top: 70px; display: flex; justify-content: space-between; flex-direction: row-reverse; }
        .signature-line { border-top: 1px solid #000; width: 200px; text-align: center; padding-top: 5px; font-weight: 600; }
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; }
            .print-container { padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="print-container">
        {{-- أزرار التحكم --}}
        <div class="text-start mb-4 no-print d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-lg">
                <i class="fas fa-print me-2"></i> طباعة
            </button>
            <button onclick="window.close()" class="btn btn-secondary btn-lg">إغلاق</button>
        </div>

        @if($result)
        {{-- الترويسة --}}
        <div class="header">
            <div class="school-name">كشف نتائج الطالب</div>
            <div class="report-title">{{ $academicYear->name ?? '' }}
                @if($semester) &nbsp;|&nbsp; {{ $semester->name }} @endif
            </div>
        </div>

        {{-- بيانات الطالب --}}
        <table class="student-info w-100 mb-4">
            <tr>
                <th>اسم الطالب:</th>
                <td style="font-size:17px;font-weight:700;">{{ $student->name }}</td>
                <th>الرقم الأكاديمي:</th>
                <td>{{ str_pad($student->student_id ?? $student->id, 6, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <th>الصف الدراسي:</th>
                <td>{{ $student->section->schoolClass->name ?? ($student->schoolClass->name ?? '—') }}</td>
                <th>الشعبة:</th>
                <td>{{ $student->section->name ?? '—' }}</td>
            </tr>
        </table>

        {{-- جدول النتائج --}}
        <table class="table result-table mt-3">
            <thead>
                <tr>
                    <th style="text-align:right;">المادة</th>
                    <th>النسبة المئوية</th>
                    <th>التقدير</th>
                    <th>المعدل (GPA)</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($result['subjects'] as $sub)
                <tr>
                    <td style="text-align:right;font-weight:600;">{{ $sub['subject']->name }}</td>
                    <td>{{ $sub['total_percentage'] }}%</td>
                    <td><strong>{{ $sub['letter_grade'] ?? '—' }}</strong></td>
                    <td>{{ $sub['gpa_points'] ?? '—' }}</td>
                    <td style="color:{{ $sub['is_passing'] ? '#1a7a1a' : '#cc0000' }};font-weight:600;">
                        {{ $sub['is_passing'] ? 'ناجح' : 'راسب' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ملخص النتيجة --}}
        <div class="summary-box">
            <div class="row text-center">
                <div class="col-4">
                    <div class="fw-bold mb-1">المعدل التراكمي (GPA)</div>
                    <div style="font-size:24px;font-weight:700;">{{ $result['summary']['overall_gpa'] ?? '—' }}</div>
                </div>
                <div class="col-4 border-start border-end border-dark">
                    <div class="fw-bold mb-1">المتوسط العام</div>
                    <div style="font-size:24px;font-weight:700;">{{ $result['summary']['average_percentage'] }}%</div>
                </div>
                <div class="col-4">
                    <div class="fw-bold mb-1">النتيجة النهائية</div>
                    <div style="font-size:22px;font-weight:700;
                         color:{{ $result['summary']['status'] === 'passed' ? '#1a7a1a' : ($result['summary']['status'] === 'failed' ? '#cc0000' : '#cc8800') }}">
                        @if($result['summary']['status'] === 'passed') ناجح
                        @elseif($result['summary']['status'] === 'failed') راسب
                        @else غير مكتمل @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 text-muted" style="font-size:12px;">
            * هذا الكشف تم إنشاؤه تلقائياً بواسطة النظام.
            المواد الناجحة: {{ $result['summary']['passed'] }} / {{ $result['summary']['total_subjects'] }}.
        </div>

        {{-- توقيعات --}}
        <div class="signatures">
            <div><div class="signature-line">مدير المدرسة</div></div>
            <div><div class="signature-line">المعلم المسؤول</div></div>
        </div>

        @else
        <div class="text-center mt-5">
            <h2>لا توجد بيانات نتيجة</h2>
            <p>تعذّر إنشاء كشف النتيجة لهذا الطالب.</p>
        </div>
        @endif
    </div>
</body>
</html>
