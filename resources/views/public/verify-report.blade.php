<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card Verification - {{ $school->school_name ?? 'School System' }}</title>
    <!-- Use Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .verification-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            max-width: 500px;
            width: 100%;
            padding: 40px 30px;
            text-align: center;
        }
        .logo-placeholder {
            width: 80px;
            height: 80px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .logo-placeholder i {
            font-size: 40px;
            color: #adb5bd;
        }
        .status-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        .valid-icon { color: #198754; }
        .invalid-icon { color: #dc3545; }
        .warning-icon { color: #ffc107; }
        
        .data-list {
            text-align: left;
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .data-item {
            margin-bottom: 15px;
        }
        .data-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .data-value {
            font-size: 16px;
            font-weight: 600;
            color: #212529;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="verification-card">
        
        @if(isset($school->logo))
            <img src="{{ asset($school->logo) }}" alt="School Logo" style="max-height: 80px; margin-bottom: 20px;">
        @else
            <div class="logo-placeholder">
                <i class="fas fa-school"></i>
            </div>
        @endif
        
        <h4 class="mb-4">{{ $school->school_name ?? 'School System' }}</h4>

        @if($result['is_valid'])
            <div class="status-icon valid-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="text-success mb-2">Valid Document</h2>
            <p class="text-muted">{{ $result['message'] }}</p>

            <div class="data-list">
                <div class="row">
                    <div class="col-12 data-item">
                        <div class="data-label">Student Name</div>
                        <div class="data-value">{{ $result['report_card']->student_name_snapshot }}</div>
                    </div>
                    <div class="col-6 data-item">
                        <div class="data-label">Academic Year</div>
                        <div class="data-value">{{ $result['report_card']->academic_year_name_snapshot }}</div>
                    </div>
                    <div class="col-6 data-item">
                        <div class="data-label">Section</div>
                        <div class="data-value">{{ $result['report_card']->section_name_snapshot }}</div>
                    </div>
                    <div class="col-6 data-item">
                        <div class="data-label">Overall GPA</div>
                        <div class="data-value">{{ $result['report_card']->gpa }}</div>
                    </div>
                    <div class="col-6 data-item">
                        <div class="data-label">Status</div>
                        <div class="data-value">
                            @if($result['report_card']->academic_status === 'Pass')
                                <span class="text-success">PASS</span>
                            @else
                                <span class="text-danger">{{ strtoupper($result['report_card']->academic_status) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 data-item">
                        <div class="data-label">Issue Date</div>
                        <div class="data-value">{{ $result['report_card']->published_at->format('F d, Y - h:i A') }}</div>
                    </div>
                </div>
            </div>

        @else
            @if(isset($result['report_card']) && $result['report_card']->status === App\Enums\ReportCardStatus::Revoked)
                <div class="status-icon warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h2 class="text-warning mb-2">Revoked Document</h2>
                <p class="text-muted">{{ $result['message'] }}</p>
                <div class="data-list text-center">
                    <p>This document was initially issued to <strong>{{ $result['report_card']->student_name_snapshot }}</strong> but has been subsequently revoked by the administration.</p>
                    <p>Please contact the school administration for an updated official document.</p>
                </div>
            @else
                <div class="status-icon invalid-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h2 class="text-danger mb-2">Invalid Document</h2>
                <p class="text-muted">{{ $result['message'] }}</p>
                <div class="data-list text-center">
                    <p>This QR code does not match any valid document in our system, or the document has been tampered with.</p>
                </div>
            @endif
        @endif

        <div class="mt-4 pt-3 border-top text-muted" style="font-size: 12px;">
            Secured by {{ config('app.name') }} Verification System
        </div>
    </div>
</div>

</body>
</html>
