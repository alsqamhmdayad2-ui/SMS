@extends('layouts.app')

@section('title', 'رصد الدرجات - ' . $subject->name . ' - ' . $section->name)

@push('styles')
<style>
    :root {
        --wizard-primary: #1e3a5f;
        --wizard-success: #2d6a4f;
        --wizard-accent:  #f59e0b;
    }

    .wizard-header {
        background: linear-gradient(135deg, var(--wizard-primary) 0%, var(--wizard-success) 100%);
        border-radius: 16px; padding: 1.5rem 2rem; color: white; margin-bottom: 1.5rem;
    }
    .wizard-header h1 { font-size: 1.5rem; font-weight: 700; margin: 0; }
    .wizard-header p  { margin: 0.3rem 0 0; opacity: 0.85; font-size: 0.88rem; }

    .steps-bar {
        display: flex; align-items: center; margin-bottom: 1.5rem;
        background: white; border-radius: 12px; padding: 0.85rem 1.5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
    }
    .step-item { display: flex; align-items: center; gap: 0.5rem; flex: 1; font-size: 0.85rem; color: #adb5bd; font-weight: 500; }
    .step-item.active { color: var(--wizard-primary); }
    .step-item.done   { color: var(--wizard-success); }
    .step-num { width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; background: #e9ecef; color: #6c757d; flex-shrink: 0; }
    .step-item.active .step-num { background: var(--wizard-primary); color: white; }
    .step-item.done   .step-num { background: var(--wizard-success); color: white; }
    .step-divider { width: 32px; height: 2px; background: #dee2e6; margin: 0 0.4rem; flex-shrink: 0; }

    .breadcrumb-nav {
        display: flex; align-items: center; gap: 0.4rem;
        margin-bottom: 1.25rem; font-size: 0.88rem; color: #6c757d;
    }
    .breadcrumb-nav a { color: var(--wizard-primary); text-decoration: none; font-weight: 500; }
    .breadcrumb-nav a:hover { text-decoration: underline; }
    .breadcrumb-nav .sep { color: #dee2e6; }

    /* Semester tabs */
    .semester-tabs {
        display: flex; gap: 0.5rem; margin-bottom: 1.25rem; flex-wrap: wrap;
    }
    .semester-tab {
        padding: 0.45rem 1.25rem; border-radius: 30px; border: 2px solid #dee2e6;
        text-decoration: none; color: #6c757d; font-size: 0.88rem; font-weight: 500;
        transition: all .2s; white-space: nowrap;
    }
    .semester-tab.active, .semester-tab:hover {
        border-color: var(--wizard-primary); background: var(--wizard-primary); color: white;
        text-decoration: none;
    }

    /* Marks Table */
    .marks-wrapper {
        background: white; border-radius: 16px;
        box-shadow: 0 2px 16px rgba(0,0,0,.07); overflow: hidden;
    }
    .marks-toolbar {
        padding: 1rem 1.5rem; border-bottom: 1px solid #f1f3f5;
        display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
    }
    .marks-title { font-weight: 700; font-size: 1rem; color: #1a1a2e; }
    .marks-subtitle { font-size: 0.8rem; color: #6c757d; }

    .table-responsive { overflow-x: auto; }

    table.marks-table {
        width: 100%; border-collapse: separate; border-spacing: 0;
        font-size: 0.875rem;
    }
    .marks-table thead th {
        background: #f8fafc; color: #475569; font-weight: 600;
        padding: 0.85rem 0.6rem; text-align: center;
        border-bottom: 2px solid #e2e8f0; white-space: nowrap;
        position: sticky; top: 0; z-index: 2;
    }
    .marks-table thead th.col-student {
        text-align: right; min-width: 180px; padding-right: 1rem;
    }
    .marks-table thead th .max-badge {
        display: block; font-size: 0.68rem; font-weight: 400;
        color: #94a3b8; margin-top: 2px;
    }
    .marks-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .15s; }
    .marks-table tbody tr:hover { background: #f8fafc; }
    .marks-table tbody tr:last-child { border-bottom: none; }
    .marks-table td { padding: 0.55rem 0.5rem; text-align: center; vertical-align: middle; }
    .marks-table td.td-student {
        text-align: right; padding-right: 1rem;
        font-weight: 600; color: #1e293b; min-width: 180px;
    }
    .student-num { font-size: 0.75rem; color: #94a3b8; margin-left: 0.4rem; }

    /* Mark inputs */
    .mark-input {
        width: 62px; height: 36px; text-align: center;
        border: 1.5px solid #e2e8f0; border-radius: 8px;
        font-size: 0.9rem; font-weight: 600; color: #1e293b;
        transition: all .15s; outline: none; background: #f8fafc;
        padding: 0 4px;
    }
    .mark-input:focus {
        border-color: var(--wizard-primary); background: white;
        box-shadow: 0 0 0 3px rgba(30,58,95,.1);
    }
    .mark-input:disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
    .mark-input.is-over  { border-color: #ef4444; background: #fff5f5; color: #ef4444; }
    .mark-input.is-valid { border-color: #10b981; }

    /* Total cell */
    .total-cell { font-weight: 800; font-size: 1rem; }
    .total-cell.passing   { color: #15803d; }
    .total-cell.failing   { color: #dc2626; }
    .grade-badge {
        display: inline-block; padding: 0.2rem 0.6rem;
        border-radius: 20px; font-size: 0.72rem; font-weight: 700; white-space: nowrap;
    }
    .grade-excellent { background: #dcfce7; color: #15803d; }
    .grade-vgood     { background: #dbeafe; color: #1d4ed8; }
    .grade-good      { background: #fef3c7; color: #b45309; }
    .grade-accepted  { background: #ffedd5; color: #c2410c; }
    .grade-fail      { background: #fee2e2; color: #b91c1c; }

    /* Locked overlay */
    .locked-banner {
        display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.5rem;
        background: linear-gradient(90deg, #fef3c7, #fef9c3);
        border-bottom: 2px solid #fbbf24; color: #92400e; font-weight: 600;
    }
    .locked-banner i { font-size: 1.2rem; }

    /* Buttons */
    .btn-save-all {
        background: linear-gradient(135deg, var(--wizard-primary), #2563eb);
        color: white; border: none; border-radius: 10px;
        padding: 0.6rem 1.75rem; font-weight: 600; font-size: 0.9rem;
        cursor: pointer; transition: all .2s; display: flex; align-items: center; gap: 0.5rem;
    }
    .btn-save-all:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(30,58,95,.3); }
    .btn-save-all:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    .btn-lock {
        background: transparent; border: 2px solid #fbbf24; color: #92400e;
        border-radius: 10px; padding: 0.5rem 1.25rem; font-weight: 600;
        font-size: 0.85rem; cursor: pointer; transition: all .2s;
        display: flex; align-items: center; gap: 0.4rem;
    }
    .btn-lock:hover { background: #fbbf24; }
    .btn-unlock {
        background: transparent; border: 2px solid #10b981; color: #065f46;
        border-radius: 10px; padding: 0.5rem 1.25rem; font-weight: 600;
        font-size: 0.85rem; cursor: pointer; transition: all .2s;
        display: flex; align-items: center; gap: 0.4rem;
    }
    .btn-unlock:hover { background: #d1fae5; }

    /* Stats bar */
    .stats-bar {
        display: flex; gap: 1rem; padding: 0.75rem 1.5rem;
        border-top: 1px solid #f1f5f9; flex-wrap: wrap;
    }
    .stat-pill {
        display: flex; align-items: center; gap: 0.4rem;
        font-size: 0.8rem; color: #64748b; font-weight: 500;
    }
    .stat-pill .dot { width: 8px; height: 8px; border-radius: 50%; }
    .dot-pass { background: #10b981; }
    .dot-fail { background: #ef4444; }
    .dot-empty{ background: #e2e8f0; }

    /* Save toast */
    #saveToast {
        position: fixed; bottom: 1.5rem; left: 50%; transform: translateX(-50%);
        background: #1e3a5f; color: white; padding: 0.75rem 2rem;
        border-radius: 40px; font-weight: 600; font-size: 0.9rem;
        z-index: 9999; box-shadow: 0 8px 24px rgba(0,0,0,.2);
        display: none; align-items: center; gap: 0.5rem;
    }
    #saveToast.show { display: flex; animation: slideUp .3s ease; }
    @keyframes slideUp { from { opacity:0; transform: translateX(-50%) translateY(12px); } to { opacity:1; transform: translateX(-50%) translateY(0); } }

    @media (max-width: 768px) {
        .mark-input { width: 52px; height: 32px; font-size: 0.82rem; }
    }
</style>
@endpush

@section('content')

{{-- ── الهيدر ── --}}
<div class="wizard-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1>
                <i class="fa-solid fa-pen-to-square me-2"></i>
                رصد درجات: {{ $subject->name }}
            </h1>
            <p>
                {{ $section->schoolClass->grade->name ?? '' }}
                &nbsp;|&nbsp; الصف: <strong>{{ $section->schoolClass->name ?? '' }}</strong>
                &nbsp;|&nbsp; الشعبة: <strong>{{ trim(str_replace(['الشعبة', 'شعبة'], '', $section->name)) }}</strong>
                &nbsp;|&nbsp; الفصل: <strong>{{ $semester->name ?? 'غير محدد' }}</strong>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if($isLocked)
                <button class="btn-unlock" id="btnUnlock">
                    <i class="fa-solid fa-lock-open"></i> فتح الدرجات
                </button>
            @else
                <button class="btn-lock" id="btnLock">
                    <i class="fa-solid fa-lock"></i> قفل الفصل
                </button>
                <button class="btn-save-all" id="btnSaveAll">
                    <i class="fa-solid fa-floppy-disk"></i>
                    حفظ الكل
                </button>
            @endif
        </div>
    </div>
</div>

{{-- ── شريط الخطوات ── --}}
<div class="steps-bar">
    <div class="step-item done"><span class="step-num"><i class="fa-solid fa-check" style="font-size:.65rem"></i></span>اختيار الصف</div>
    <div class="step-divider"></div>
    <div class="step-item done"><span class="step-num"><i class="fa-solid fa-check" style="font-size:.65rem"></i></span>اختيار الشعبة</div>
    <div class="step-divider"></div>
    <div class="step-item done"><span class="step-num"><i class="fa-solid fa-check" style="font-size:.65rem"></i></span>اختيار المادة</div>
    <div class="step-divider"></div>
    <div class="step-item active"><span class="step-num">4</span>رصد الدرجات</div>
</div>

{{-- ── Breadcrumb ── --}}
<div class="breadcrumb-nav">
    <a href="{{ route('admin.marks.index') }}"><i class="fa-solid fa-house me-1"></i>الصفوف</a>
    <span class="sep"><i class="fa-solid fa-chevron-left fa-xs"></i></span>
    <a href="{{ route('admin.marks.sections', $section->schoolClass) }}">{{ $section->schoolClass->name ?? '' }}</a>
    <span class="sep"><i class="fa-solid fa-chevron-left fa-xs"></i></span>
    <a href="{{ route('admin.marks.subjects', $section) }}">شعبة {{ $section->name }}</a>
    <span class="sep"><i class="fa-solid fa-chevron-left fa-xs"></i></span>
    <span>{{ $subject->name }}</span>
</div>

{{-- ── تبديل الفصول ── --}}
@if($semesters->count() > 1)
<div class="semester-tabs">
    @foreach($semesters as $sem)
        <a href="{{ route('admin.marks.enter', [$section, $subject]) }}?semester_id={{ $sem->id }}"
           class="semester-tab {{ $sem->id == $semester->id ? 'active' : '' }}">
            {{ $sem->name }}
        </a>
    @endforeach
</div>
@endif

{{-- ── جدول الرصد ── --}}
<div class="marks-wrapper">

    {{-- Locked banner --}}
    @if($isLocked)
    <div class="locked-banner">
        <i class="fa-solid fa-lock"></i>
        <div>
            <strong>درجات هذا الفصل مقفلة</strong>
            &mdash; لا يمكن تعديلها. يمكنك فتحها عبر زر "فتح الدرجات" أعلاه.
        </div>
    </div>
    @endif

    <div class="marks-toolbar">
        <div>
            <div class="marks-title">
                <i class="fa-solid fa-table me-1 text-primary"></i>
                جدول الرصد &mdash; {{ $students->count() }} طالب
            </div>
            <div class="marks-subtitle">المجموع الكلي من 100 درجة</div>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <button class="btn btn-sm btn-outline-secondary" id="btnFillZero" title="ملء الخلايا الفارغة بصفر">
                <i class="fa-solid fa-fill-drip me-1"></i>تصفير الفارغ
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="marks-table" id="marksTable">
            <thead>
                <tr>
                    <th class="col-student">#&nbsp; اسم الطالب</th>
                    <th>
                        نشاط
                        <span class="max-badge">/ 10</span>
                    </th>
                    <th>
                        حضور
                        <span class="max-badge">/ 10</span>
                    </th>
                    <th>
                        واجبات
                        <span class="max-badge">/ 10</span>
                    </th>
                    <th>
                        شهري 1
                        <span class="max-badge">/ 10</span>
                    </th>
                    <th>
                        نصفي
                        <span class="max-badge">/ 20</span>
                    </th>
                    <th>
                        شهري 2
                        <span class="max-badge">/ 10</span>
                    </th>
                    <th>
                        نهائي
                        <span class="max-badge">/ 30</span>
                    </th>
                    <th>
                        المجموع
                        <span class="max-badge">/ 100</span>
                    </th>
                    <th>التقدير</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $i => $student)
                    @php
                        $m = $marks->get($student->id);
                    @endphp
                    <tr data-student-id="{{ $student->id }}">
                        <td class="td-student">
                            <span class="student-num">{{ $i + 1 }}</span>
                            {{ $student->full_name }}
                        </td>
                        <td>
                            <input type="number" class="mark-input" data-field="activity" data-max="10"
                                   value="{{ $m ? number_format($m->activity, 0) : '' }}"
                                   min="0" max="10" step="0.5" placeholder="—"
                                   {{ $isLocked ? 'disabled' : '' }}>
                        </td>
                        <td>
                            <input type="number" class="mark-input" data-field="attendance" data-max="10"
                                   value="{{ $m && $m->attendance !== null ? (fmod($m->attendance, 1) == 0 ? number_format($m->attendance, 0) : number_format($m->attendance, 1)) : ($autoAttendance[$student->id] ?? '') }}"
                                   min="0" max="10" step="0.5" placeholder="—"
                                   title="{{ isset($autoAttendance) && isset($autoAttendance[$student->id]) && (!$m || $m->attendance === null) ? 'قيمة تلقائية محسوبة من نظام الحضور' : '' }}"
                                   {{ $isLocked ? 'disabled' : '' }}
                                   {{ isset($autoAttendance) && isset($autoAttendance[$student->id]) && (!$m || $m->attendance === null) ? 'style=background:#e0f2fe;border-color:#38bdf8' : '' }}>
                        </td>
                        <td>
                            <input type="number" class="mark-input" data-field="homework" data-max="10"
                                   value="{{ $m ? number_format($m->homework, 0) : '' }}"
                                   min="0" max="10" step="0.5" placeholder="—"
                                   {{ $isLocked ? 'disabled' : '' }}>
                        </td>
                        <td>
                            <input type="number" class="mark-input" data-field="monthly1" data-max="10"
                                   value="{{ $m ? number_format($m->monthly1, 0) : '' }}"
                                   min="0" max="10" step="0.5" placeholder="—"
                                   {{ $isLocked ? 'disabled' : '' }}>
                        </td>
                        <td>
                            <input type="number" class="mark-input" data-field="midterm" data-max="20"
                                   value="{{ $m ? number_format($m->midterm, 0) : '' }}"
                                   min="0" max="20" step="0.5" placeholder="—"
                                   {{ $isLocked ? 'disabled' : '' }}>
                        </td>
                        <td>
                            <input type="number" class="mark-input" data-field="monthly2" data-max="10"
                                   value="{{ $m ? number_format($m->monthly2, 0) : '' }}"
                                   min="0" max="10" step="0.5" placeholder="—"
                                   {{ $isLocked ? 'disabled' : '' }}>
                        </td>
                        <td>
                            <input type="number" class="mark-input" data-field="final_exam" data-max="30"
                                   value="{{ $m ? number_format($m->final_exam, 0) : '' }}"
                                   min="0" max="30" step="0.5" placeholder="—"
                                   {{ $isLocked ? 'disabled' : '' }}>
                        </td>
                        <td>
                            <span class="total-cell {{ ($m && $m->total >= 50) ? 'passing' : (($m && $m->total < 50) ? 'failing' : '') }}"
                                  data-total>
                                {{ $m ? number_format($m->total, 1) : '—' }}
                            </span>
                        </td>
                        <td>
                            @if($m && $m->total !== null)
                                @php
                                    $t = $m->total;
                                    if($t >= 90)      { $gc = 'grade-excellent'; $gl = 'ممتاز'; }
                                    elseif($t >= 80)  { $gc = 'grade-vgood';     $gl = 'جيد جداً'; }
                                    elseif($t >= 70)  { $gc = 'grade-good';      $gl = 'جيد'; }
                                    elseif($t >= 60)  { $gc = 'grade-accepted';  $gl = 'مقبول'; }
                                    else              { $gc = 'grade-fail';       $gl = 'راسب'; }
                                @endphp
                                <span class="grade-badge {{ $gc }}" data-grade>{{ $gl }}</span>
                            @else
                                <span class="grade-badge" style="background:#f1f5f9;color:#94a3b8;" data-grade>—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-users-slash me-2"></i>لا يوجد طلاب في هذه الشعبة
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Stats bar --}}
    <div class="stats-bar" id="statsBar">
        <div class="stat-pill">
            <span class="dot dot-pass"></span>
            <span id="statPass">— ناجح</span>
        </div>
        <div class="stat-pill">
            <span class="dot dot-fail"></span>
            <span id="statFail">— راسب</span>
        </div>
        <div class="stat-pill">
            <span class="dot dot-empty"></span>
            <span id="statEmpty">— لم يُرصد</span>
        </div>
        <div class="stat-pill ms-auto">
            <i class="fa-solid fa-chart-line text-primary"></i>
            <span id="statAvg">متوسط: —</span>
        </div>
    </div>
</div>

{{-- Toast notification --}}
<div id="saveToast">
    <i class="fa-solid fa-circle-check"></i>
    <span id="toastMsg">تم الحفظ بنجاح!</span>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const SECTION_ID  = {{ $section->id }};
    const SUBJECT_ID  = {{ $subject->id }};
    const SEMESTER_ID = {{ $semester->id ?? 'null' }};
    const YEAR_ID     = {{ $currentAcademicYear->id ?? 'null' }};
    const IS_LOCKED   = {{ $isLocked ? 'true' : 'false' }};
    const CSRF        = document.querySelector('meta[name="csrf-token"]').content;

    const SAVE_URL     = "{{ route('admin.marks.save-all') }}";
    const LOCK_URL     = "{{ route('admin.marks.toggle-lock') }}";

    // ── Helpers ──
    function getGradeLabel(t) {
        if (t >= 90) return { label: 'ممتاز',   cls: 'grade-excellent' };
        if (t >= 80) return { label: 'جيد جداً', cls: 'grade-vgood' };
        if (t >= 70) return { label: 'جيد',      cls: 'grade-good' };
        if (t >= 60) return { label: 'مقبول',    cls: 'grade-accepted' };
        return             { label: 'راسب',       cls: 'grade-fail' };
    }

    function showToast(msg, isError = false) {
        const t = document.getElementById('saveToast');
        document.getElementById('toastMsg').textContent = msg;
        t.style.background = isError ? '#dc2626' : '#1e3a5f';
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 3000);
    }

    // ── Real-time total calculation ──
    function recalcRow(row) {
        const fields   = ['activity','attendance','homework','monthly1','midterm','monthly2','final_exam'];
        const maxMap   = { activity:10, attendance:10, homework:10, monthly1:10, midterm:20, monthly2:10, final_exam:30 };
        let total      = 0;
        let hasValue   = false;
        let allFilled  = true;

        fields.forEach(f => {
            const inp = row.querySelector(`[data-field="${f}"]`);
            if (!inp) return;
            const val = parseFloat(inp.value);
            const max = maxMap[f];
            if (!isNaN(val) && inp.value.trim() !== '') {
                total += val;
                hasValue = true;
                inp.classList.toggle('is-over', val > max);
                inp.classList.toggle('is-valid', val >= 0 && val <= max);
            } else {
                allFilled = false;
                inp.classList.remove('is-over', 'is-valid');
            }
        });

        const totalEl = row.querySelector('[data-total]');
        const gradeEl = row.querySelector('[data-grade]');

        if (hasValue) {
            totalEl.textContent = total.toFixed(1);
            totalEl.className = 'total-cell ' + (total >= 50 ? 'passing' : 'failing');
            
            if (allFilled) {
                const g = getGradeLabel(total);
                gradeEl.textContent = g.label;
                gradeEl.className = `grade-badge ${g.cls}`;
                gradeEl.style = '';
            } else {
                gradeEl.textContent = 'قيد الرصد';
                gradeEl.className = 'grade-badge';
                gradeEl.style = 'background:#f1f5f9;color:#64748b;';
            }
        } else {
            totalEl.textContent = '—';
            totalEl.className = 'total-cell';
            gradeEl.textContent = '—';
            gradeEl.className = 'grade-badge';
            gradeEl.style = 'background:#f1f5f9;color:#94a3b8;';
        }

        updateStats();
    }

    // ── Attach listeners ──
    document.querySelectorAll('.mark-input').forEach(inp => {
        inp.addEventListener('input', () => recalcRow(inp.closest('tr')));
        // Tab navigation
        inp.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                // Move to same field in next row
                const rows = [...document.querySelectorAll('#marksTable tbody tr')];
                const cur  = inp.closest('tr');
                const idx  = rows.indexOf(cur);
                const field = inp.dataset.field;
                if (idx < rows.length - 1) {
                    const next = rows[idx + 1].querySelector(`[data-field="${field}"]`);
                    if (next) next.focus();
                }
            }
        });
    });

    // ── Initial Calculation ──
    document.querySelectorAll('#marksTable tbody tr[data-student-id]').forEach(row => {
        recalcRow(row);
    });

    // ── Stats ──
    function updateStats() {
        const rows  = [...document.querySelectorAll('#marksTable tbody tr[data-student-id]')];
        let pass = 0, fail = 0, empty = 0, sum = 0, cnt = 0;
        rows.forEach(r => {
            const t = r.querySelector('[data-total]').textContent;
            if (t === '—') { empty++; return; }
            const v = parseFloat(t);
            if (v >= 50) pass++; else fail++;
            sum += v; cnt++;
        });
        document.getElementById('statPass').textContent  = `${pass} ناجح`;
        document.getElementById('statFail').textContent  = `${fail} راسب`;
        document.getElementById('statEmpty').textContent = `${empty} لم يُرصد`;
        document.getElementById('statAvg').textContent   = cnt > 0 ? `متوسط: ${(sum/cnt).toFixed(1)}` : 'متوسط: —';
    }
    updateStats();

    // ── Fill zeros ──
    document.getElementById('btnFillZero')?.addEventListener('click', () => {
        document.querySelectorAll('.mark-input:not(:disabled)').forEach(inp => {
            if (inp.value === '') {
                inp.value = 0;
                recalcRow(inp.closest('tr'));
            }
        });
    });

    // ── Collect marks from table ──
    function collectMarks() {
        const rows = [...document.querySelectorAll('#marksTable tbody tr[data-student-id]')];
        return rows.map(row => {
            const sid = row.dataset.studentId;
            const get = (f) => {
                const v = row.querySelector(`[data-field="${f}"]`)?.value;
                return v !== '' && v !== undefined ? parseFloat(v) : null;
            };
            return {
                student_id: sid,
                activity:   get('activity'),
                attendance: get('attendance'),
                homework:   get('homework'),
                monthly1:   get('monthly1'),
                midterm:    get('midterm'),
                monthly2:   get('monthly2'),
                final_exam: get('final_exam'),
            };
        });
    }

    // ── Save All ──
    const btnSave = document.getElementById('btnSaveAll');
    btnSave?.addEventListener('click', async () => {
        if (!SEMESTER_ID || !YEAR_ID) {
            alert('يجب تفعيل عام دراسي وفصل دراسي أولاً.');
            return;
        }
        btnSave.disabled = true;
        btnSave.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري الحفظ...';
        try {
            const resp = await fetch(SAVE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({
                    section_id: SECTION_ID,
                    subject_id: SUBJECT_ID,
                    semester_id: SEMESTER_ID,
                    academic_year_id: YEAR_ID,
                    marks: collectMarks(),
                }),
            });
            const data = await resp.json();
            if (resp.ok && data.status === 'success') {
                showToast('✅ ' + data.message);
            } else {
                showToast(data.message || 'حدث خطأ أثناء الحفظ', true);
            }
        } catch (e) {
            showToast('خطأ في الاتصال بالخادم', true);
        } finally {
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> حفظ الكل';
        }
    });

    // ── Lock/Unlock ──
    async function toggleLock(lock) {
        if (!confirm(lock ? 'هل تريد قفل درجات هذا الفصل؟ لن يمكن تعديلها.' : 'هل تريد فتح درجات هذا الفصل للتعديل؟')) return;
        try {
            const resp = await fetch(LOCK_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({
                    section_id: SECTION_ID, subject_id: SUBJECT_ID,
                    semester_id: SEMESTER_ID, academic_year_id: YEAR_ID,
                    lock: lock,
                }),
            });
            const data = await resp.json();
            if (resp.ok) {
                showToast(data.message);
                setTimeout(() => location.reload(), 1200);
            } else {
                showToast(data.message || 'حدث خطأ', true);
            }
        } catch(e) { showToast('خطأ في الاتصال', true); }
    }

    document.getElementById('btnLock')?.addEventListener('click',   () => toggleLock(true));
    document.getElementById('btnUnlock')?.addEventListener('click', () => toggleLock(false));

})();
</script>
@endpush
