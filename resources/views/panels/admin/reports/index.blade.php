@extends('layouts.app')
@section('title', 'Reports & Export Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">Reports & Export Dashboard</h2>
            <p class="text-muted">Generate official academic reports, transcripts, and statistical analysis.</p>
        </div>
    </div>

    <div class="row">
        <!-- Student Report -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 border-primary shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i> Student Report Card</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Generate official transcript for a single student including all subjects and GPA.</p>
                    <form action="{{ route('admin.reports.generate', 'student') }}" method="GET" target="_blank">
                        <div class="mb-3">
                            <label class="form-label text-primary">Academic Year</label>
                            <select name="academic_year_id" class="form-select" required>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-primary">Student</label>
                            <select name="student_id" class="form-select" required>
                                <option value="">Select Student...</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->student_id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="submit" name="action" value="view" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye"></i> View</button>
                            <button type="submit" name="action" value="pdf" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i> PDF</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Section Report -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 border-success shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i> Section Results</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Comprehensive results for an entire section, including ranking and averages.</p>
                    <form action="{{ route('admin.reports.generate', 'section') }}" method="GET" target="_blank">
                        <div class="mb-3">
                            <label class="form-label text-success">Academic Year</label>
                            <select name="academic_year_id" class="form-select" required>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-success">Section</label>
                            <select name="section_id" class="form-select" required>
                                <option value="">Select Section...</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="submit" name="action" value="view" class="btn btn-outline-success btn-sm"><i class="fas fa-eye"></i> View</button>
                            <button type="submit" name="action" value="excel" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Excel</button>
                            <button type="submit" name="action" value="pdf" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i> PDF</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Subject Report -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 border-info shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-book me-2"></i> Subject Report</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Detailed breakdown of assessment components for a specific subject in a section.</p>
                    <form action="{{ route('admin.reports.generate', 'subject') }}" method="GET" target="_blank">
                        <div class="mb-2">
                            <label class="form-label text-info">Academic Year</label>
                            <select name="academic_year_id" class="form-select form-select-sm" required>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label text-info">Section</label>
                            <select name="section_id" class="form-select form-select-sm" required>
                                <option value="">Select Section...</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-info">Subject</label>
                            <select name="subject_id" class="form-select form-select-sm" required>
                                <option value="">Select Subject...</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="submit" name="action" value="view" class="btn btn-outline-info btn-sm"><i class="fas fa-eye"></i> View</button>
                            <button type="submit" name="action" value="excel" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Excel</button>
                            <button type="submit" name="action" value="pdf" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i> PDF</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Teacher Report -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 border-warning shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i> Teacher Report</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Summary of student performance for all subjects/sections assigned to a teacher.</p>
                    <form action="{{ route('admin.reports.generate', 'teacher') }}" method="GET" target="_blank">
                        <div class="mb-3">
                            <label class="form-label text-warning">Academic Year</label>
                            <select name="academic_year_id" class="form-select" required>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-warning">Teacher</label>
                            <select name="teacher_id" class="form-select" required>
                                <option value="">Select Teacher...</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="submit" name="action" value="view" class="btn btn-outline-warning btn-sm"><i class="fas fa-eye"></i> View</button>
                            <button type="submit" name="action" value="pdf" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i> PDF</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 border-secondary shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> Academic Statistics</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">High-level analytics, pass rates, grade distribution, and honor students.</p>
                    <form action="{{ route('admin.reports.generate', 'statistics') }}" method="GET" target="_blank">
                        <div class="mb-3">
                            <label class="form-label">Academic Year</label>
                            <select name="academic_year_id" class="form-select" required>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Filter By (Optional)</label>
                            <select name="section_id" class="form-select mb-2">
                                <option value="">All Sections...</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="submit" name="action" value="view" class="btn btn-outline-secondary btn-sm"><i class="fas fa-eye"></i> View</button>
                            <button type="submit" name="action" value="pdf" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i> PDF</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
