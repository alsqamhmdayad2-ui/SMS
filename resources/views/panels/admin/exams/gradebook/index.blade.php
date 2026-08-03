@extends('layouts.app')
@section('title', 'كشف الدرجات (Gradebook)')

@section('content')

<x-page-header title="كشف الدرجات" />

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'التقييم والدرجات'],
    ['name' => 'كشف الدرجات']
]" />

<div class="">

    <x-alerts />

    <!-- Filters -->
    <x-shared.card class="mb-4 bg-light" shadow="sm">
        <form action="{{ route('admin.gradebook.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <x-form.select name="academic_year_id" label="Academic Year" required="true">
                    <option value="">Select...</option>
                    @foreach($academicYears as $y)
                    <option value="{{ $y->id }}" {{ request('academic_year_id') == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-2">
                <x-form.select name="semester_id" label="Semester">
                    <option value="">All</option>
                    @foreach($semesters as $s)
                    <option value="{{ $s->id }}" {{ request('semester_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-2">
                <x-form.select name="grade_id" label="Grade" required="true">
                    <option value="">Select...</option>
                    @foreach($grades as $g)
                    <option value="{{ $g->id }}" {{ request('grade_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-2">
                <x-form.select name="section_id" label="Section" required="true">
                    <option value="">Select...</option>
                    @foreach($sections as $sec)
                    <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>{{ ($sec->schoolClass->name ?? '') . ' - ' . $sec->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-2">
                <x-form.select name="subject_id" label="Subject" required="true">
                    <option value="">Select...</option>
                    @foreach($subjects as $sub)
                    <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Load</button>
            </div>
        </form>
    </x-shared.card>

    @if($stats)
    <!-- Stats Cards -->
    <div class="row mb-4 g-3">
        <div class="col">
            <x-dashboard.stat-card class="text-center" title="Students" value="{{ $stats['total_students'] }}" />
        </div>
        <div class="col">
            <x-dashboard.stat-card class="text-center" title="Average" value="{{ $stats['average'] }}%" />
        </div>
        <div class="col">
            <x-dashboard.stat-card class="text-center" title="Highest" value="{{ $stats['highest'] }}%" />
        </div>
        <div class="col">
            <x-dashboard.stat-card class="text-center" title="Lowest" value="{{ $stats['lowest'] }}%" />
        </div>
        <div class="col">
            <x-dashboard.stat-card class="text-center" title="Median" value="{{ $stats['median'] }}%" />
        </div>
        <div class="col">
            <x-dashboard.stat-card class="text-center" title="Pass Rate" value="{{ $stats['pass_rate'] }}%" color="{{ $stats['pass_rate'] >= 80 ? 'success' : ($stats['pass_rate'] >= 50 ? 'warning' : 'danger') }}" />
        </div>
        <div class="col">
            <x-dashboard.stat-card class="text-center" title="Failed" value="{{ $stats['fail_count'] }}" color="danger" />
        </div>
    </div>

    <!-- Gradebook Table -->
    <x-shared.card shadow="sm">
        <x-table.data-table hover="true">
            <x-slot:header>
                <th>Rank</th>
                <th>Student</th>
                @foreach($components as $comp)
                <th class="text-center">{{ $comp->name }}<br><small class="fw-normal">({{ (float)$comp->weight_percentage }}%)</small></th>
                @endforeach
                <th class="text-center">Total</th>
                <th class="text-center">Grade</th>
                <th class="text-center">GPA</th>
                <th class="text-center">Status</th>
                <th class="text-center">Details</th>
            </x-slot:header>
            <x-slot:body>
                @forelse($gradebook as $row)
                @php
                    $colorClass = '';
                    if($row['total_percentage'] >= 90) $colorClass = 'grade-excellent';
                    elseif($row['total_percentage'] >= 75) $colorClass = 'grade-good';
                    elseif($row['total_percentage'] >= 60) $colorClass = 'grade-average';
                    elseif($row['total_percentage'] > 0) $colorClass = 'grade-fail';
                @endphp
                <tr class="{{ $colorClass }}">
                    <td class="fw-bold">
                        @if($row['rank'] == 1) 🥇
                        @elseif($row['rank'] == 2) 🥈
                        @elseif($row['rank'] == 3) 🥉
                        @else {{ $row['rank'] }}
                        @endif
                    </td>
                    <td>
                        <strong>{{ $row['student']->name }}</strong>
                        @if($row['is_finalized'])
                        <x-shared.badge type="secondary" class="ms-1"><i class="bi bi-lock-fill"></i> Finalized</x-shared.badge>
                        @endif
                    </td>
                    @foreach($row['components'] as $cs)
                    <td class="text-center">
                        @if($cs['total'] > 0)
                        <span class="fw-bold">{{ $cs['obtained'] }}</span><span class="text-sms-muted">/{{ $cs['total'] }}</span>
                        <br><small class="text-sms-muted">{{ $cs['percentage'] ?? 0 }}%</small>
                        @else
                        <span class="text-sms-muted">-</span>
                        @endif
                    </td>
                    @endforeach
                    <td class="text-center"><h5 class="mb-0"><x-shared.badge type="primary">{{ $row['total_percentage'] }}%</x-shared.badge></h5></td>
                    <td class="text-center">
                        @if($row['letter_grade'])
                        <x-shared.badge :type="$row['is_passing'] ? 'success' : 'danger'" class="fs-6">{{ $row['letter_grade'] }}</x-shared.badge>
                        @else - @endif
                    </td>
                    <td class="text-center fw-bold">{{ $row['gpa_points'] ?? '-' }}</td>
                    <td class="text-center">
                        @if($row['is_passing'] === true)
                        <x-shared.badge type="success">Pass</x-shared.badge>
                        @elseif($row['is_passing'] === false)
                        <x-shared.badge type="danger">Fail</x-shared.badge>
                        @else - @endif
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-info btn-breakdown"
                                data-student-id="{{ $row['student']->id }}"
                                data-student-name="{{ $row['student']->name }}">
                            <i class="bi bi-eye"></i> View
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="{{ $components->count() + 7 }}" class="text-center py-5 text-sms-muted">No data available.</td></tr>
                @endforelse
            </x-slot:body>
        </x-table.data-table>
    </x-shared.card>
    @elseif(request()->hasAny(['academic_year_id','subject_id','section_id']))
    <div class="text-center py-5">
        <x-shared.empty-state icon="search" title="No results found" message="No results found for the selected filters." />
    </div>
    @else
    <div class="text-center py-5">
        <x-shared.empty-state icon="funnel" title="Select filters above" message="Load the gradebook by selecting filters." />
    </div>
    @endif
</div>

<!-- Breakdown Modal -->
<x-shared.modal id="breakdownModal" size="lg" title="<i class='bi bi-clipboard-data'></i> <span id='breakdownStudentName'></span> — Assessment Breakdown">
    <div id="breakdownBody">
        <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
    </div>
</x-shared.modal>
@endsection

@push('styles')
<style>
tr.grade-excellent td { background-color: rgba(25,135,84,.06); }
tr.grade-good td { background-color: rgba(13,110,253,.06); }
tr.grade-average td { background-color: rgba(255,193,7,.06); }
tr.grade-fail td { background-color: rgba(220,53,69,.06); }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-breakdown').forEach(btn => {
        btn.addEventListener('click', function() {
            const studentId = this.dataset.studentId;
            const studentName = this.dataset.studentName;
            document.getElementById('breakdownStudentName').textContent = studentName;

            const modal = new bootstrap.Modal(document.getElementById('breakdownModal'));
            const body = document.getElementById('breakdownBody');
            body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
            modal.show();

            const params = new URLSearchParams(window.location.search);
            params.set('student_id', studentId);

            fetch('{{ route("admin.gradebook.student-breakdown") }}?' + params.toString(), {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                let html = '<div class="row g-3">';
                data.components.forEach(c => {
                    const pct = c.percentage !== null ? c.percentage + '%' : 'N/A';
                    const contribPct = c.contribution !== null ? c.contribution + '%' : 'N/A';
                    html += `
                    <div class="col-md-6">
                        <div class="card border h-100">
                            <div class="card-body">
                                <h6 class="fw-bold">${c.component.name} <span class="badge bg-secondary">${c.component.code}</span></h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Score: <strong>${c.obtained} / ${c.total}</strong></span>
                                    <span>Percentage: <strong>${pct}</strong></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Weight: <strong>${c.weight}%</strong></span>
                                    <span>Contribution: <strong class="text-primary">${contribPct}</strong></span>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar" style="width: ${c.percentage || 0}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                });
                html += '</div>';

                // Summary
                const passing = data.is_passing;
                html += `
                <hr>
                <div class="row text-center">
                    <div class="col-md-3"><div class="border rounded p-3"><div class="small text-sms-muted">Final Score</div><div class="fs-3 fw-bold text-primary">${data.total_percentage}%</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3"><div class="small text-sms-muted">Letter Grade</div><div class="fs-3 fw-bold">${data.letter_grade || '-'}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3"><div class="small text-sms-muted">GPA</div><div class="fs-3 fw-bold">${data.gpa_points || '-'}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3"><div class="small text-sms-muted">Status</div><div class="fs-3 fw-bold ${passing ? 'text-success' : 'text-danger'}">${passing ? 'Pass' : 'Fail'}</div></div></div>
                </div>`;

                if (data.is_finalized) {
                    html += '<div class="alert alert-secondary mt-3 text-center"><i class="bi bi-lock-fill"></i> This result has been finalized and locked.</div>';
                }

                body.innerHTML = html;
            })
            .catch(() => { body.innerHTML = '<div class="text-center text-danger py-4">Error loading breakdown.</div>'; });
        });
    });
});
</script>
@endpush
