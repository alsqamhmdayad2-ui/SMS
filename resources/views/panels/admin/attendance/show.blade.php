@extends('layouts.app')
@section('title', __('attendance.session_details') . ' - ' . ($session->subject->name ?? ''))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold text-sms-primary">
                {{ __('attendance.daily_section_attendance') ?? 'Daily Attendance' }} : {{ $session->section?->schoolClass?->name ?? '' }} — {{ $session->section?->name ?? '—' }}
            </h2>
            <p class="text-sms-muted mb-0">
                <i class="fas fa-calendar-day me-1"></i>{{ $session->date?->translatedFormat('l، j F Y') ?? '—' }}
                · <i class="fas fa-user-tie me-1"></i>{{ __('attendance.recorded_by') }}: {{ $session->teacher->name ?? '—' }}
                @if($session->period_number)
                    <span class="opacity-75">({{ __('attendance.period_label') }} {{ $session->period_number }} - {{ $session->subject->name ?? '' }})</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            @if($session->status === App\Enums\AttendanceSessionStatus::Locked)
                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#unlockModal">
                    <i class="fas fa-lock-open me-1"></i> {{ __('attendance.unlock_session') }}
                </button>
            @else
                <form action="{{ route('admin.attendance-sessions.lock', $session->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-secondary" onclick="return confirm('{{ __('attendance.lock_confirm') }}')">
                        <i class="fas fa-lock me-1"></i> {{ __('attendance.lock_session') }}
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.attendance-sessions.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i> {{ __('attendance.back') }}
            </a>
        </div>
    </div>

    <x-alerts />

    <x-shared.card class="mb-4 border-0 shadow-sm bg-sms-light" shadow="sm">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h6 class="mb-1 fw-semibold">{{ __('attendance.session_status') }}</h6>
                <p class="mb-0 text-sms-muted">{{ $session->status->label() }}</p>
            </div>
            <x-shared.badge :type="$session->status->color()">
                <i class="fas fa-{{ $session->status === App\Enums\AttendanceSessionStatus::Locked ? 'lock' : 'lock-open' }} me-1"></i>
                {{ $session->status->label() }}
            </x-shared.badge>
        </div>
        @if($session->is_locked && $session->locked_at)
            <div class="alert alert-secondary mt-3 mb-0 py-2">
                <i class="fas fa-lock me-1"></i>
                تم الإغلاق بواسطة <strong>{{ $session->lockedBy->name ?? 'النظام' }}</strong>
                بتاريخ {{ $session->locked_at?->translatedFormat('h:i A، D j F') ?? '—' }}
            </div>
        @endif
    </x-shared.card>

    <div class="row g-3 mb-4">
        @php
            $statsMap = [
                ['label' => __('attendance.present'), 'key' => 'present', 'color' => 'success', 'icon' => 'fas fa-check-circle'],
                ['label' => __('attendance.absent'),  'key' => 'absent',  'color' => 'danger',  'icon' => 'fas fa-times-circle'],
                ['label' => __('attendance.late'),    'key' => 'late',    'color' => 'warning', 'icon' => 'fas fa-clock'],
                ['label' => __('attendance.excused'), 'key' => 'excused', 'color' => 'info',    'icon' => 'fas fa-file-alt'],
                ['label' => __('attendance.sick'),    'key' => 'sick',    'color' => 'secondary','icon' => 'fas fa-heartbeat'],
            ];
        @endphp
        @foreach($statsMap as $s)
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-sms-light">
                    <div class="card-body text-center py-3">
                        <i class="{{ $s['icon'] }} fa-2x text-{{ $s['color'] }} mb-2"></i>
                        <div class="fs-4 fw-bold">{{ $stats[$s['key']] ?? 0 }}</div>
                        <div class="small text-sms-muted">{{ $s['label'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-sms-primary text-white">
                <div class="card-body text-center py-3">
                    <i class="fas fa-percent fa-2x mb-2 opacity-75"></i>
                    <div class="fs-4 fw-bold">{{ $stats['present_percentage'] ?? 0 }}%</div>
                    <div class="small opacity-75">{{ __('attendance.attendance_rate') }}</div>
                </div>
            </div>
        </div>
    </div>

    <x-shared.card shadow="sm" class="border-0">
        <x-slot:header>
            <h6 class="m-0 fw-semibold">{{ __('attendance.student_records') }}</h6>
        </x-slot:header>
        <x-table.data-table hover="true">
            <x-slot:header>
                <th>#</th>
                <th>{{ __('attendance.student') }}</th>
                <th>{{ __('attendance.current_status') }}</th>
                <th>{{ __('attendance.marked_by') }}</th>
                <th>{{ __('attendance.history') }}</th>
                <th>{{ __('attendance.override') }}</th>
            </x-slot:header>
            <x-slot:body>
                @foreach($session->records as $record)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($record->student->photo)
                                    <img src="{{ asset('storage/'.$record->student->photo) }}" class="rounded-circle" width="35" height="35" style="object-fit:cover;">
                                @else
                                    <div class="rounded-circle bg-sms-secondary text-white d-flex align-items-center justify-content-center" style="width:35px;height:35px;font-size:14px;">
                                        {{ strtoupper(substr($record->student->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-semibold small">{{ $record->student->name }}</div>
                                    <div class="text-sms-muted" style="font-size:11px;">{{ $record->student->student_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <x-shared.badge :type="$record->status->color()" class="px-3 py-2">
                                <i class="{{ $record->status->icon() }} me-1"></i>
                                {{ $record->status->label() }}
                            </x-shared.badge>
                        </td>
                        <td>
                            <small class="text-sms-muted">
                                {{ $record->markedBy->name ?? '—' }}<br>
                                {{ $record->marked_at?->format('h:i A') }}
                            </small>
                        </td>
                        <td>
                            @if($record->overrides->isNotEmpty())
                                <div style="font-size:12px; max-height:80px; overflow-y:auto;">
                                    @foreach($record->overrides as $override)
                                        <div class="border-start border-2 border-warning ps-2 mb-1">
                                            <span class="badge bg-{{ \App\Enums\AttendanceStatus::tryFrom($override->old_status)?->color() ?? 'secondary' }} me-1">
                                                {{ $override->old_status_label }}
                                            </span>
                                            <i class="fas fa-arrow-right text-sms-muted mx-1"></i>
                                            <span class="badge bg-{{ \App\Enums\AttendanceStatus::tryFrom($override->new_status)?->color() ?? 'secondary' }}">
                                                {{ $override->new_status_label }}
                                            </span>
                                            <br>
                                            <span class="text-sms-muted">{{ $override->overriddenBy->name ?? '—' }} · {{ $override->overridden_at?->format('h:i A') ?? '—' }}</span><br>
                                            <em class="text-sms-secondary">{{ $override->reason }}</em>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-sms-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#overrideModal" data-student-id="{{ $record->student_id }}" data-student-name="{{ $record->student->name }}" data-current-status="{{ $record->status->value }}">
                                <i class="fas fa-edit"></i> {{ __('attendance.override') }}
                            </button>
                        </td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-table.data-table>
    </x-shared.card>
</div>

<form action="{{ route('admin.attendance-sessions.override', $session->id) }}" method="POST">
    @csrf
    <x-shared.modal id="overrideModal" title="<i class='fas fa-edit me-2 text-danger'></i>{{ __('attendance.override_attendance') }}" headerClass="border-0" footerClass="border-0">
        <x-slot:body>
            <input type="hidden" name="student_id" id="overrideStudentId">
            <div class="alert alert-warning py-2 small">
                <i class="fas fa-exclamation-triangle me-1"></i>
                {{ __('attendance.overriding_student') }} <strong id="overrideStudentName"></strong>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('attendance.new_status') }}</label>
                <select name="status" class="form-select" id="overrideStatus" required>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('attendance.reason') }} <span class="text-danger">*</span></label>
                <textarea name="reason" class="form-control" rows="3" required minlength="5"></textarea>
                <div class="form-text">{{ __('attendance.reason_required') }}</div>
            </div>
        </x-slot:body>
        <x-slot:footer>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-save me-1"></i> {{ __('attendance.save_override') }}
            </button>
        </x-slot:footer>
    </x-shared.modal>
</form>

<form action="{{ route('admin.attendance-sessions.unlock', $session->id) }}" method="POST">
    @csrf
    <x-shared.modal id="unlockModal" title="<i class='fas fa-lock-open me-2 text-warning'></i>{{ __('attendance.unlock_session') }}" headerClass="border-0" footerClass="border-0">
        <x-slot:body>
            <div class="alert alert-warning py-2 small">
                إلغاء الإغلاق يسمح للمعلم بتعديل الحضور مجدداً. يرجى كتابة السبب.
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('attendance.unlock_reason') }} <span class="text-danger">*</span></label>
                <textarea name="unlock_reason" class="form-control" rows="3" required minlength="5"></textarea>
            </div>
        </x-slot:body>
        <x-slot:footer>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-warning text-dark">
                <i class="fas fa-lock-open me-1"></i> {{ __('attendance.confirm_unlock') }}
            </button>
        </x-slot:footer>
    </x-shared.modal>
</form>

@push('scripts')
<script>
    document.getElementById('overrideModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        document.getElementById('overrideStudentId').value = btn.dataset.studentId;
        document.getElementById('overrideStudentName').textContent = btn.dataset.studentName;
        document.getElementById('overrideStatus').value = btn.dataset.currentStatus;
    });
</script>
@endpush
@endsection
