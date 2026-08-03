@extends('layouts.app')
@section('title', __('attendance.take_attendance'))

@section('content')
<div class="container-fluid">

    {{-- Session Header --}}
    <x-shared.card class="mb-4 bg-sms-primary text-white" shadow="sm">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="rounded-circle bg-white text-sms-primary d-flex align-items-center justify-content-center"
                     style="width:55px;height:55px;font-size:22px;">
                    <i class="fas fa-book-open"></i>
                </div>
            </div>
            <div class="col">
                <h4 class="mb-0 fw-bold">{{ $timetable->subject->name }}</h4>
                <p class="mb-0 opacity-75">
                    {{ $timetable->section?->grade?->name ?? '' }} &mdash; {{ $timetable->section?->name ?? '' }}
                    &nbsp;·&nbsp;
                    <i class="fas fa-user-tie me-1"></i>{{ auth()->user()->name }}
                </p>
            </div>
            <div class="col-auto text-end">
                <p class="mb-0 fw-semibold">{{ $today->translatedFormat('l، j F Y') }}</p>
                <p class="mb-0 opacity-75 fw-bold text-warning">
                    <i class="fas fa-calendar-check me-1"></i> سجل حضور الشعبة اليومي
                </p>
            </div>
        </div>
    </x-shared.card>

    <x-alerts />

    @php
        $currentTeacherId = auth()->user()?->teacher?->id;
        $canEdit = $currentTeacherId !== null && $session->teacher_id === $currentTeacherId && $session->isOpen();
    @endphp

    @if(!$canEdit)
        <x-shared.card class="mb-3 border-info" shadow="sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 fw-bold">{{ __('attendance.locked_title') }}</h6>
                    <p class="mb-0 text-sms-muted">تم تسجيل الحضور اليومي لهذه الشعبة بواسطة معلم الحصة الأولى. يمكنك فقط مشاهدة الحضور.</p>
                </div>
                <span class="badge bg-info text-white">
                    <i class="fas fa-eye me-1"></i> عرض فقط
                </span>
            </div>
        </x-shared.card>
    @endif

    <form action="{{ route('teacher.attendance.save', $session->id) }}" method="POST" id="attendanceForm">
        @csrf

        {{-- Toolbar: Bulk Actions + Search --}}
        <x-shared.card class="mb-3 p-2" shadow="sm">
            <div class="row align-items-center g-2">
                @if($canEdit)
                    <div class="col-auto">
                        <span class="text-sms-muted fw-semibold small">{{ __('attendance.mark_all') }}</span>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="markAll('present')">
                            <i class="fas fa-check me-1"></i> {{ __('attendance.present') }}
                        </button>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAll('absent')">
                            <i class="fas fa-times me-1"></i> {{ __('attendance.absent') }}
                        </button>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="markAll('late')">
                            <i class="fas fa-clock me-1"></i> {{ __('attendance.late') }}
                        </button>
                    </div>
                @endif
                <div class="col ms-auto">
                    <input type="text" class="form-control form-control-sm" id="studentSearch"
                           placeholder="{{ __('attendance.search_student') }}" oninput="filterStudents(this.value)">
                </div>
                <div class="col-auto text-sms-muted small fw-semibold">
                    <span id="presentCount">0</span> {{ __('attendance.present') }} &nbsp;|&nbsp;
                    <span id="absentCount">0</span> {{ __('attendance.absent') }} &nbsp;|&nbsp;
                    <span id="lateCount">0</span> {{ __('attendance.late') }}
                </div>
            </div>
        </x-shared.card>

        {{-- Students Table --}}
        <x-shared.card shadow="sm">
            <x-table.data-table hover="true" id="studentsTable">
                <x-slot:header>
                    <th style="width:60px;">#</th>
                    <th>{{ __('attendance.student') }}</th>
                    <th style="width:360px;">{{ __('attendance.status') }}</th>
                    <th style="width:200px;">{{ __('attendance.remarks') }}</th>
                </x-slot:header>
                <x-slot:body>
                    @foreach($records as $studentId => $record)
                        @php $student = $record->student; @endphp
                        <tr class="student-row" data-name="{{ strtolower($student->name) }}">
                            <td class="text-sms-muted">{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    {{-- Avatar --}}
                                    @if($student->photo)
                                        <img src="{{ asset('storage/'.$student->photo) }}"
                                             class="rounded-circle" width="40" height="40"
                                             style="object-fit:cover;" alt="{{ $student->name }}">
                                    @else
                                        <div class="rounded-circle bg-sms-secondary text-white d-flex align-items-center justify-content-center"
                                             style="width:40px;height:40px;font-size:16px;flex-shrink:0;">
                                            {{ strtoupper(mb_substr($student->name, 0, 1, 'UTF-8')) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $student->name }}</div>
                                        <small class="text-sms-muted">{{ $student->student_id ?? $student->student_number }}</small>
                                    </div>
                                </div>

                                <input type="hidden" name="records[{{ $student->id }}][student_id]" value="{{ $student->id }}">
                            </td>
                            <td>
                                <div class="d-flex gap-2 flex-nowrap status-group" data-index="{{ $student->id }}">
                                    @foreach($statuses as $status)
                                        <label class="status-label m-0 text-nowrap">
                                            <input type="radio"
                                                   name="records[{{ $student->id }}][status]"
                                                   value="{{ $status->value }}"
                                                   class="status-radio d-none"
                                                   data-color="{{ $status->color() }}"
                                                   {{ $record->status->value === $status->value ? 'checked' : '' }}
                                                   {{ $canEdit ? '' : 'disabled' }}>
                                            <span class="badge px-3 py-2 cursor-pointer rounded-pill border
                                                {{ $record->status->value === $status->value ? 'bg-'.$status->color().' border-'.$status->color().' text-white shadow fw-bold' : 'bg-light border-secondary-subtle text-secondary fw-medium opacity-75' }}"
                                                style="cursor:{{ $canEdit ? 'pointer' : 'default' }};transition:all .2s ease; font-size: 14px;">
                                                <i class="{{ $status->icon() }} me-1"></i>{{ $status->label() }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <input type="text"
                                       name="records[{{ $student->id }}][remarks]"
                                       value="{{ $record->remarks }}"
                                       class="form-control form-control-sm"
                                       placeholder="{{ __('attendance.optional_note') }}"
                                       {{ $canEdit ? '' : 'disabled' }}>
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-table.data-table>
        </x-shared.card>

        {{-- Footer Actions --}}
        <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
            <a href="{{ route('teacher.attendance.today') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> {{ __('attendance.back_to_classes') }}
            </a>
            <div class="d-flex gap-2">
                @if($canEdit)
                    <button type="submit" name="action" value="save" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i> {{ __('attendance.save_draft') }}
                    </button>
                @endif
            </div>
        </div>
    </form>

    @if($canEdit)
        {{-- Lock Form (separate POST) --}}
        <form action="{{ route('teacher.attendance.lock', $session->id) }}" method="POST" class="d-inline" id="lockForm">
            @csrf
        </form>
        <div class="d-flex justify-content-end mt-2 mb-5">
            <button type="button" class="btn btn-dark"
                    onclick="if(confirm('Locking attendance prevents further changes. Are you sure?')) { document.getElementById('lockForm').submit(); }">
                <i class="fas fa-lock me-1"></i> {{ __('attendance.lock_session') }}
            </button>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    // ─── Status Toggle Visual ───────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.status-label').forEach(label => {
            label.addEventListener('click', function () {
                const radio  = this.querySelector('input[type="radio"]');
                if (!radio || radio.disabled) return;
                
                const group  = this.closest('.status-group');
                const color  = radio.dataset.color;

                // Reset all in group
                group.querySelectorAll('.badge').forEach(b => {
                    b.className = 'badge px-3 py-2 cursor-pointer rounded-pill border bg-light border-secondary-subtle text-secondary fw-medium opacity-75';
                });

                // Activate selected
                const badge = this.querySelector('.badge');
                badge.className = `badge px-3 py-2 cursor-pointer rounded-pill border bg-${color} border-${color} text-white shadow fw-bold`;

                updateCounts();
            });
        });

        updateCounts();
    });

    // ─── Mark All ───────────────────────────────────────────────────────────
    function markAll(status) {
        document.querySelectorAll('.student-row:not([style*="display: none"])').forEach(row => {
            const radio = row.querySelector(`input[type="radio"][value="${status}"]`);
            if (radio && !radio.disabled) {
                radio.checked = true;
                radio.dispatchEvent(new Event('change'));

                const group = radio.closest('.status-group');
                group.querySelectorAll('.badge').forEach(b => {
                    b.className = 'badge px-3 py-2 cursor-pointer rounded-pill border bg-light border-secondary-subtle text-secondary fw-medium opacity-75';
                });
                const badge = radio.closest('label').querySelector('.badge');
                badge.className = `badge px-3 py-2 cursor-pointer rounded-pill border bg-${radio.dataset.color} border-${radio.dataset.color} text-white shadow fw-bold`;
            }
        });
        updateCounts();
    }

    // ─── Live Counters ──────────────────────────────────────────────────────
    function updateCounts() {
        const rows = document.querySelectorAll('.student-row:not([style*="display: none"])');
        let present = 0, absent = 0, late = 0;

        rows.forEach(row => {
            const checked = row.querySelector('input[type="radio"]:checked');
            if (checked) {
                if (checked.value === 'present') present++;
                else if (checked.value === 'absent') absent++;
                else if (checked.value === 'late') late++;
            }
        });

        document.getElementById('presentCount').textContent = present;
        document.getElementById('absentCount').textContent  = absent;
        document.getElementById('lateCount').textContent    = late;
    }

    // ─── Search Filter ──────────────────────────────────────────────────────
    function filterStudents(query) {
        const q = query.toLowerCase();
        document.querySelectorAll('.student-row').forEach(row => {
            const name = row.dataset.name;
            row.style.display = name.includes(q) ? '' : 'none';
        });
        updateCounts();
    }
</script>
@endpush
