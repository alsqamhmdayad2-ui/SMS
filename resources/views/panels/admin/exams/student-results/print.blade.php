<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result: {{ $student->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #fff; font-family: 'Times New Roman', serif; color: #000; }
        .print-container { max-width: 800px; margin: 0 auto; padding: 40px 20px; }
        .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #000; padding-bottom: 20px; }
        .school-name { font-size: 28px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; }
        .report-title { font-size: 20px; font-weight: bold; letter-spacing: 2px; }
        .student-info { margin-bottom: 30px; }
        .student-info th { width: 150px; text-align: left; }
        .student-info th, .student-info td { padding: 5px; }
        .result-table th { background-color: #f8f9fa !important; border-bottom: 2px solid #000; }
        .result-table td, .result-table th { border: 1px solid #dee2e6; vertical-align: middle; }
        .summary-box { border: 2px solid #000; padding: 15px; margin-top: 30px; }
        .signatures { margin-top: 80px; display: flex; justify-content: space-between; }
        .signature-line { border-top: 1px solid #000; width: 200px; text-align: center; padding-top: 5px; font-weight: bold; }
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; }
            .print-container { padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Controls -->
        <div class="text-end mb-4 no-print">
            <button onclick="window.print()" class="btn btn-primary btn-lg">Print Document</button>
            <button onclick="window.close()" class="btn btn-secondary btn-lg ms-2">Close</button>
        </div>

        @if($result)
        <!-- Header -->
        <div class="header">
            <div class="school-name">Noor Al-Maaref School</div>
            <div>Academic Excellence & Character Building</div>
            <div class="mt-3 report-title">STUDENT ACADEMIC REPORT</div>
            <div class="mt-1">{{ $academicYear->name ?? '' }} | {{ $semester->name ?? 'Full Academic Year' }}</div>
        </div>

        <!-- Student Details -->
        <table class="student-info w-100">
            <tr>
                <th>Student Name:</th>
                <td style="font-size: 18px; font-weight: bold;">{{ $student->name }}</td>
                <th>Student ID:</th>
                <td>{{ str_pad($student->id, 6, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <th>Grade Level:</th>
                <td>{{ $student->grade->name ?? 'N/A' }}</td>
                <th>Class/Section:</th>
                <td>{{ $student->schoolClass->name ?? 'N/A' }} / {{ $student->section->name ?? 'N/A' }}</td>
            </tr>
        </table>

        <!-- Results Table -->
        <table class="table result-table mt-4">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th class="text-center">Percentage</th>
                    <th class="text-center">Letter Grade</th>
                    <th class="text-center">GPA</th>
                    <th class="text-center">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($result['subjects'] as $sub)
                <tr>
                    <td class="fw-bold">{{ $sub['subject']->name }}</td>
                    <td class="text-center">{{ $sub['total_percentage'] }}%</td>
                    <td class="text-center fw-bold">{{ $sub['letter_grade'] ?? '-' }}</td>
                    <td class="text-center">{{ $sub['gpa_points'] ?? '-' }}</td>
                    <td class="text-center">{{ $sub['is_passing'] ? 'Pass' : 'Fail' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary-box">
            <div class="row text-center">
                <div class="col-4">
                    <div class="fw-bold text-uppercase mb-1">Overall GPA</div>
                    <div style="font-size: 24px; font-weight: bold;">{{ $result['summary']['overall_gpa'] ?? '-' }}</div>
                </div>
                <div class="col-4 border-start border-end border-dark">
                    <div class="fw-bold text-uppercase mb-1">Average</div>
                    <div style="font-size: 24px; font-weight: bold;">{{ $result['summary']['average_percentage'] }}%</div>
                </div>
                <div class="col-4">
                    <div class="fw-bold text-uppercase mb-1">Final Status</div>
                    <div style="font-size: 24px; font-weight: bold; text-transform: uppercase;">
                        {{ $result['summary']['status'] }}
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 text-muted" style="font-size: 12px;">
            * This report is generated automatically by the School Management System. 
            Passed subjects: {{ $result['summary']['passed'] }} / {{ $result['summary']['total_subjects'] }}.
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div>
                <div class="signature-line">Class Teacher</div>
            </div>
            <div>
                <div class="signature-line">School Principal</div>
            </div>
        </div>

        @else
        <div class="text-center mt-5">
            <h2>No Result Data Available</h2>
            <p>Could not generate the report card for this student.</p>
        </div>
        @endif
    </div>

    <script>
        // Optional auto-print
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
