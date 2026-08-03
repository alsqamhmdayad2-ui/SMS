@extends('layouts.app')
@section('title', 'Official Report Cards')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Official Report Cards & GPA</h2>
                <p class="text-muted">Generate, publish, and manage official locked report cards.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateModal">
                <i class="fas fa-magic"></i> Generate for Section
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.report-cards.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year_id" class="form-select">
                        <option value="">All Years...</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Section</label>
                    <select name="section_id" class="form-select">
                        <option value="">All Sections...</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Period</th>
                        <th>Section</th>
                        <th>GPA</th>
                        <th>Status</th>
                        <th>Published At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportCards as $card)
                        <tr>
                            <td>
                                <strong>{{ $card->student_name_snapshot }}</strong><br>
                                <small class="text-muted">{{ $card->student->student_id ?? '' }}</small>
                            </td>
                            <td>{{ ucfirst($card->report_period) }}<br><small>{{ $card->academic_year_name_snapshot }}</small></td>
                            <td>{{ $card->section_name_snapshot }}</td>
                            <td>
                                <strong>{{ $card->gpa }}</strong><br>
                                <small class="text-muted">{{ $card->total_percentage }}%</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $card->status->color() }}">{{ $card->status->label() }}</span>
                                @if($card->is_locked)
                                    <i class="fas fa-lock text-warning ms-1" title="Locked"></i>
                                @endif
                            </td>
                            <td>{{ $card->published_at ? $card->published_at->format('Y-m-d') : '-' }}</td>
                            <td>
                                @if($card->status === App\Enums\ReportCardStatus::Generated)
                                    <form action="{{ route('admin.report-cards.publish', $card->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Publish & Lock" onclick="return confirm('Publishing will lock this record. Proceed?')">
                                            <i class="fas fa-check"></i> Publish
                                        </button>
                                    </form>
                                @endif
                                
                                @if($card->status === App\Enums\ReportCardStatus::Published)
                                    <a href="{{ route('admin.report-cards.pdf', $card->id) }}" class="btn btn-sm btn-danger" target="_blank" title="Download Official PDF">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                    
                                    <form action="{{ route('admin.report-cards.revoke', $card->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Revoke Document" onclick="return confirm('Are you sure you want to REVOKE this official document? This will invalidate the QR code.')">
                                            <i class="fas fa-ban"></i> Revoke
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">No report cards generated yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $reportCards->links() }}
        </div>
    </div>
</div>

<!-- Generate Modal -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.report-cards.generate') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Generate Report Cards</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Note: You can only generate report cards if <strong>all subjects</strong> for the section have been officially published.
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year_id" class="form-select" required>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Semester (Optional)</label>
                    <select name="semester_id" class="form-select">
                        <option value="">Full Year</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Section</label>
                    <select name="section_id" class="form-select" required>
                        <option value="">Select Section...</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Report Period</label>
                    <select name="report_period" class="form-select" required>
                        <option value="semester">Semester</option>
                        <option value="annual">Annual Final</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Generate Now</button>
            </div>
        </form>
    </div>
</div>
@endsection
