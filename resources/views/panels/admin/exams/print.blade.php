<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة امتحان: {{ $exam->title }}</title>
    <!-- Google Fonts for premium arabic typography -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #fff;
            color: #000;
            margin: 0;
            padding: 20px;
            direction: rtl;
            font-size: 14px;
            line-height: 1.6;
        }
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 30px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 3px double #000;
        }
        .header-table td {
            padding: 5px;
            vertical-align: middle;
        }
        .school-logo {
            width: 80px;
            height: 80px;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
        }
        .exam-meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #000;
        }
        .exam-meta-table td {
            border: 1px solid #000;
            padding: 8px;
            width: 25%;
        }
        .exam-meta-table td.label {
            font-weight: bold;
            background-color: #f5f5f5;
        }
        .instructions-box {
            border: 1px dashed #000;
            padding: 15px;
            margin-bottom: 30px;
            background-color: #fafafa;
        }
        .instructions-title {
            font-weight: bold;
            margin-bottom: 5px;
            text-decoration: underline;
        }
        .question-item {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .question-header {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .question-mark {
            font-size: 13px;
            font-weight: normal;
        }
        .mcq-options {
            list-style-type: none;
            padding-right: 20px;
            margin-top: 10px;
        }
        .mcq-options li {
            margin-bottom: 8px;
        }
        .mcq-option-box {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 1px solid #000;
            border-radius: 50%;
            margin-left: 10px;
            vertical-align: middle;
        }
        .matching-list {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .matching-list td {
            padding: 10px;
            width: 45%;
        }
        .matching-list td.separator {
            width: 10%;
            text-align: center;
        }
        .matching-box {
            border: 1px solid #000;
            padding: 8px;
            min-height: 30px;
        }
        .matching-line {
            display: inline-block;
            width: 100px;
            border-bottom: 1px solid #000;
            margin-right: 10px;
        }
        .essay-space {
            border: 1px solid #ccc;
            height: 150px;
            margin-top: 10px;
            border-radius: 4px;
        }
        .short-answer-space {
            border-bottom: 1px dotted #000;
            width: 300px;
            height: 30px;
            margin-top: 10px;
        }
        .fill-blank-text {
            font-size: 15px;
            line-height: 2;
        }
        .blank-line {
            display: inline-block;
            width: 100px;
            border-bottom: 1px solid #000;
            margin: 0 10px;
        }
        @media print {
            body {
                padding: 0;
            }
            .print-container {
                border: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: left;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; font-weight: bold; background-color: #007bff; color: #fff; border: none; border-radius: 5px; cursor: pointer;">
            طباعة الامتحان (Print)
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; font-weight: bold; background-color: #6c757d; color: #fff; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;">
            إغلاق (Close)
        </button>
    </div>

    <div class="print-container">
        <!-- School Header -->
        <table class="header-table">
            <tr>
                <td style="width: 35%;">
                    <div style="font-weight: bold; font-size: 16px;">وزارة التربية والتعليم</div>
                    <div style="font-weight: 600;">مدرسة التميز النموذجية</div>
                </td>
                <td style="width: 30%; text-align: center;">
                    <div class="school-logo" style="margin: 0 auto;">شعار المدرسة</div>
                </td>
                <td style="width: 35%; text-align: left; direction: ltr;">
                    <div style="font-weight: bold; font-size: 16px;">Ministry of Education</div>
                    <div style="font-weight: 600;">Al-Tamayouz Model School</div>
                </td>
            </tr>
        </table>

        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="margin: 0; font-weight: 800;">{{ $exam->title }}</h2>
            <span style="font-weight: 600;">امتحان مادة: {{ $exam->subject->name }}</span>
        </div>

        <!-- Exam Meta Info -->
        <table class="exam-meta-table">
            <tr>
                <td class="label">الصف والفرع</td>
                <td>{{ $exam->schoolClass->name }} ({{ $exam->sections->pluck('name')->join('، ') }})</td>
                <td class="label">العام الدراسي</td>
                <td>{{ $exam->academicYear->name }}</td>
            </tr>
            <tr>
                <td class="label">الفصل الدراسي</td>
                <td>{{ $exam->semester->name }}</td>
                <td class="label">اسم المعلم</td>
                <td>{{ $exam->teacher->name }}</td>
            </tr>
            <tr>
                <td class="label">تاريخ الامتحان</td>
                <td>{{ $exam->exam_date?->format('Y-m-d') ?? '—' }}</td>
                <td class="label">زمن الامتحان</td>
                <td>{{ $exam->duration_formatted ?? 'غير محدد' }}</td>
            </tr>
            <tr>
                <td class="label">الدرجة الكلية</td>
                <td style="font-weight: bold; color: #000;">{{ $exam->total_marks }} درجة</td>
                <td class="label">اسم الطالب</td>
                <td>................................................</td>
            </tr>
        </table>

        <!-- Instructions -->
        @if(!empty($exam->instructions))
            <div class="instructions-box">
                <div class="instructions-title">تعليمات الامتحان:</div>
                <div style="white-space: pre-line;">{{ $exam->instructions }}</div>
            </div>
        @endif

        <!-- Questions List -->
        <div style="margin-top: 40px;">
            @forelse($exam->questions as $index => $q)
                <div class="question-item">
                    <div class="question-header">
                        <div>
                            <span>السؤال {{ $index + 1 }}: </span>
                            <span class="badge bg-light border text-dark ms-2" style="font-size: 12px; font-weight: normal;">
                                [{{ $q->type->label() }}]
                            </span>
                        </div>
                        <div class="question-mark">
                            الدرجة: ({{ (float)($q->pivot->mark_override ?? $q->mark) }})
                        </div>
                    </div>
                    <p class="fill-blank-text" style="font-weight: 600; margin: 10px 0;">{{ $q->question_text }}</p>

                    @if($q->type->value === 'mcq')
                        <ul class="mcq-options">
                            @foreach($q->options as $opt)
                                <li>
                                    <span class="mcq-option-box"></span>
                                    <span>{{ $opt->option_text }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @elseif($q->type->value === 'true_false')
                        <div style="margin-top: 15px; padding-right: 20px;">
                            <span style="margin-left: 30px;"><span class="mcq-option-box"></span> نعم / صح (True)</span>
                            <span><span class="mcq-option-box"></span> لا / خطأ (False)</span>
                        </div>
                    @elseif($q->type->value === 'matching')
                        <table class="matching-list">
                            <thead>
                                <tr style="font-weight: bold; background-color: #fafafa;">
                                    <td style="border: 1px solid #ccc;">العمود الأول (أ)</td>
                                    <td style="border: 1px solid #ccc; text-align: center;">الإجابة الصحيحة</td>
                                    <td style="border: 1px solid #ccc;">العمود الثاني (ب)</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($q->options as $opt)
                                    <tr>
                                        <td style="border: 1px solid #ccc;">{{ $opt->left_item }}</td>
                                        <td style="border: 1px solid #ccc; text-align: center;">
                                            ( <span style="display:inline-block; width: 30px; border-bottom: 1px solid #000;"></span> )
                                        </td>
                                        <td style="border: 1px solid #ccc;">{{ $opt->right_item }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @elseif($q->type->value === 'essay')
                        <div class="essay-space"></div>
                    @elseif($q->type->value === 'short_answer')
                        <div class="short-answer-space"></div>
                    @elseif($q->type->value === 'fill_blank')
                        <div style="margin-top: 15px; color: #666; font-style: italic;">
                            * اكتب الإجابة المناسبة في الفراغات الموضحة أعلاه.
                        </div>
                    @endif
                </div>
            @empty
                <div style="text-align: center; padding: 50px 0; color: #888;">
                    لا توجد أسئلة مضافة لهذا الامتحان بعد.
                </div>
            @endforelse
        </div>
    </div>

</body>
</html>
