@extends('layouts.app')
@section('title', 'نشر النتائج')

@section('content')

<x-page-header title="نشر النتائج">
    <x-slot name="actions">
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#publishModal">
            <i class="fas fa-bullhorn"></i> نشر نتائج جديدة
        </button>
    </x-slot>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'التقييم والدرجات'],
    ['name' => 'نشر النتائج']
]" />

<div class="">

    <x-alerts />

    <!-- Existing Publications -->
    <x-shared.card shadow="sm">
        <x-table.data-table hover="true">
            <x-slot:header>
                <th>Academic Period</th>
                <th>Class/Section</th>
                <th>Type & Scope</th>
                <th>Status</th>
                <th>Published By</th>
                <th>Date</th>
                <th class="text-end">Actions</th>
            </x-slot:header>
            <x-slot:body>
                @forelse($publications as $pub)
                <tr>
                    <td>
                        <strong>{{ $pub->academicYear->name }}</strong><br>
                        <span class="text-sms-muted small">{{ $pub->semester->name ?? 'Full Year' }}</span>
                    </td>
                    <td>
                        <strong>{{ $pub->grade->name }}</strong><br>
                        <span class="text-sms-muted small">Section {{ $pub->section->name }}</span>
                    </td>
                    <td>
                        @if($pub->published_type === 'subject')
                            <x-shared.badge type="info" class="text-dark">Subject</x-shared.badge><br>
                            <span class="small fw-bold">{{ $pub->subject->name ?? 'N/A' }}</span>
                        @elseif($pub->published_type === 'section')
                            <x-shared.badge type="primary">Full Section</x-shared.badge>
                        @else
                            <x-shared.badge type="secondary">Full Semester</x-shared.badge>
                        @endif
                    </td>
                    <td>
                        @if($pub->status === 'draft')
                            <x-shared.badge type="warning" class="text-dark"><i class="bi bi-pencil"></i> Draft</x-shared.badge>
                        @elseif($pub->status === 'published')
                            <x-shared.badge type="success"><i class="bi bi-check-circle"></i> Published</x-shared.badge>
                        @elseif($pub->status === 'archived')
                            <x-shared.badge type="secondary"><i class="bi bi-archive"></i> Archived</x-shared.badge>
                        @else
                            <x-shared.badge type="light" class="text-dark">{{ ucfirst($pub->status) }}</x-shared.badge>
                        @endif
                    </td>
                    <td>{{ $pub->publisher->name ?? 'System' }}</td>
                    <td>{{ $pub->published_at ? $pub->published_at->format('Y-m-d H:i') : '-' }}</td>
                    <td class="text-end">
                        <form action="{{ route('admin.result-publications.update-status', $pub) }}" method="POST" class="d-inline">
                            @csrf @method('PATCH')
                            @if($pub->status === 'published')
                                <input type="hidden" name="status" value="draft">
                                <button type="submit" class="btn btn-sm btn-warning" title="Revert to Draft"><i class="bi bi-arrow-counterclockwise"></i></button>
                            @else
                                <input type="hidden" name="status" value="published">
                                <button type="submit" class="btn btn-sm btn-success" title="Publish"><i class="bi bi-check2-all"></i></button>
                            @endif
                        </form>
                        <form action="{{ route('admin.result-publications.destroy', $pub) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this publication record?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-sms-muted">
                        <x-shared.empty-state icon="megaphone" title="No publications" message="No publication records found." />
                    </td>
                </tr>
                @endforelse
            </x-slot:body>
        </x-table.data-table>
        
        @if($publications->hasPages())
        <div class="mt-3">
            {{ $publications->links() }}
        </div>
        @endif
    </x-shared.card>
</div>

<!-- Publish Modal -->
<x-shared.modal id="publishModal" title="<i class='bi bi-megaphone'></i> Publish Results" headerClass="bg-sms-primary text-white">
    <form action="{{ route('admin.result-publications.store') }}" method="POST">
        @csrf
        <x-slot:body>
            <div class="alert alert-info py-2 small">
                <i class="bi bi-info-circle"></i> Publishing results makes them visible to students and locks further grade modifications.
            </div>

            <div class="mb-3">
                <x-form.select name="published_type" id="publishedType" label="Publication Type" required="true" onchange="toggleSubject()">
                    <option value="subject">Single Subject</option>
                    <option value="section">Entire Section</option>
                    <!-- <option value="semester">Entire Semester</option> -->
                </x-form.select>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <x-form.select name="academic_year_id" label="Academic Year" required="true">
                        @foreach($academicYears as $y)
                        <option value="{{ $y->id }}">{{ $y->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-6">
                    <x-form.select name="semester_id" label="Semester">
                        <option value="">Full Year</option>
                        @foreach($semesters as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <x-form.select name="grade_id" label="Grade" required="true">
                        <option value="">Select Grade...</option>
                        @foreach($grades as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-6">
                    <label class="form-label fw-semibold text-sms-main">Section (For Now) <span class="text-sms-danger">*</span></label>
                    <select name="section_id" class="form-select" required>
                        <option value="">Select Section...</option>
                        @php $sections = \App\Models\Section::with('schoolClass')->get(); @endphp
                        @foreach($sections as $sec)
                        <option value="{{ $sec->id }}">{{ $sec->schoolClass->name ?? '' }} - {{ $sec->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3" id="subjectDiv">
                <x-form.select name="subject_id" id="subjectSelect" label="Subject" required="true">
                    <option value="">Select Subject...</option>
                    @foreach($subjects as $sub)
                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold text-sms-main">Notes (Optional)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="E.g., Final results for Math"></textarea>
            </div>

        </x-slot:body>
        <x-slot:footer>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Validate & Publish</button>
        </x-slot:footer>
    </form>
</x-shared.modal>

@push('scripts')
<script>
    function toggleSubject() {
        const type = document.getElementById('publishedType').value;
        const subjDiv = document.getElementById('subjectDiv');
        const subjSel = document.getElementById('subjectSelect');
        if (type === 'subject') {
            subjDiv.style.display = 'block';
            subjSel.setAttribute('required', 'required');
        } else {
            subjDiv.style.display = 'none';
            subjSel.removeAttribute('required');
            subjSel.value = '';
        }
    }
</script>
@endpush
@endsection
