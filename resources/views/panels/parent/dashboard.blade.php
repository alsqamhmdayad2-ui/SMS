@extends('layouts.app')
@section('title', 'لوحة تحكم ولي الأمر')

@push('styles')
<style>
    .welcome-banner {
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #3b82f6 100%);
        border-radius: 20px;
        position: relative;
        overflow: hidden;
        padding: 2rem 2.5rem;
        color: #fff;
    }
    .welcome-banner::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 220px; height: 220px;
        background: rgba(255,255,255,0.07);
        border-radius: 50%;
    }
    .welcome-banner::after {
        content: '';
        position: absolute;
        bottom: -80px; left: -40px;
        width: 280px; height: 280px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }
    .welcome-banner h2 { font-size: 1.7rem; font-weight: 700; margin-bottom: 0.3rem; }
    .welcome-banner p  { font-size: 1rem; opacity: 0.85; margin: 0; }
    .welcome-avatar {
        width: 72px; height: 72px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem;
        flex-shrink: 0;
        border: 3px solid rgba(255,255,255,0.35);
    }

    /* KPI Cards */
    .kpi-card {
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.2rem;
        border: none;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        transition: transform 0.22s ease, box-shadow 0.22s ease;
        background: #fff;
        height: 100%;
    }
    .kpi-card:hover { transform: translateY(-4px); box-shadow: 0 8px 28px rgba(0,0,0,0.12); }
    .kpi-icon {
        width: 56px; height: 56px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; flex-shrink: 0;
    }
    .kpi-icon.blue   { background: #dbeafe; color: #2563eb; }
    .kpi-icon.green  { background: #dcfce7; color: #16a34a; }
    .kpi-icon.amber  { background: #fef9c3; color: #ca8a04; }
    .kpi-icon.rose   { background: #ffe4e6; color: #e11d48; }
    .kpi-value { font-size: 2rem; font-weight: 800; line-height: 1; color: #1e293b; }
    .kpi-label { font-size: 0.82rem; color: #64748b; margin-top: 4px; font-weight: 500; }

    /* Child Cards */
    .child-card {
        border-radius: 18px;
        border: 2px solid #f1f5f9;
        background: #fff;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        transition: all 0.22s ease;
        overflow: hidden;
    }
    .child-card:hover { border-color: #2563eb; box-shadow: 0 8px 28px rgba(37,99,235,0.14); transform: translateY(-3px); }
    .child-card-header {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        padding: 1.4rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .child-avatar {
        width: 54px; height: 54px;
        background: linear-gradient(135deg, #2563eb, #60a5fa);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; color: #fff; font-weight: 700;
        flex-shrink: 0;
    }
    .child-name { font-weight: 700; font-size: 1.05rem; color: #1e293b; }
    .child-class { font-size: 0.8rem; color: #2563eb; font-weight: 500; }
    .child-stats { display: flex; gap: 1rem; padding: 1.1rem 1.4rem; border-top: 1px solid #f1f5f9; flex-wrap: wrap; }
    .child-stat { text-align: center; flex: 1; min-width: 60px; }
    .child-stat-val { font-size: 1.3rem; font-weight: 800; color: #1e293b; }
    .child-stat-lbl { font-size: 0.72rem; color: #94a3b8; }
    .child-actions { padding: 0.9rem 1.4rem; display: flex; gap: 0.6rem; flex-wrap: wrap; }

    /* Quick links */
    .quick-link {
        border-radius: 14px;
        padding: 1.1rem 1rem;
        text-align: center;
        text-decoration: none;
        color: inherit;
        border: 2px solid #f1f5f9;
        background: #fff;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.6rem;
    }
    .quick-link:hover { border-color: #2563eb; color: #2563eb; transform: translateY(-3px); box-shadow: 0 6px 20px rgba(37,99,235,0.12); }
    .quick-link i { font-size: 1.5rem; }
    .quick-link span { font-size: 0.82rem; font-weight: 600; }

    /* Table */
    .results-table thead th { background: #f8fafc; color: #475569; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; border-color: #e2e8f0; }
    .results-table tbody tr:hover { background: #eff6ff; }
    .results-table td { vertical-align: middle; border-color: #f1f5f9; font-size: 0.9rem; }

    /* Section title */
    .section-title { font-size: 1rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 0.6rem; }
    .section-title-bar { width: 4px; height: 22px; border-radius: 3px; background: #2563eb; }
</style>
@endpush

@section('content')

@php
    $parentName = $parent ? ($parent->full_name ?: ($parent->user->name ?? 'ولي الأمر')) : 'ولي الأمر';
    $firstLetter = mb_substr($parentName, 0, 1);
    $academicYear = \App\Models\AcademicYear::where('status', 1)->first();
@endphp

{{-- Welcome Banner --}}
<div class="welcome-banner mb-4 slide-up">
    <div class="d-flex align-items-center gap-3">
        <div class="welcome-avatar">{{ $firstLetter }}</div>
        <div>
            <h2>مرحباً، {{ $parentName }} 👋</h2>
            <p>
                <i class="fas fa-calendar-alt me-1"></i>
                {{ now()->translatedFormat('l، d F Y') }}
                @if($academicYear)
                    &nbsp;|&nbsp; السنة الدراسية: <strong>{{ $academicYear->name }}</strong>
                @endif
            </p>
        </div>
    </div>
</div>

{{-- KPI Row --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="kpi-card slide-up">
            <div class="kpi-icon blue"><i class="fas fa-child"></i></div>
            <div>
                <div class="kpi-value">{{ $totalChildren }}</div>
                <div class="kpi-label">عدد الأبناء</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card slide-up" style="animation-delay:.08s">
            <div class="kpi-icon green"><i class="fas fa-calendar-check"></i></div>
            <div>
                <div class="kpi-value">{{ $attendanceSummary }}%</div>
                <div class="kpi-label">متوسط الحضور</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card slide-up" style="animation-delay:.16s">
            <div class="kpi-icon rose"><i class="fas fa-chart-bar"></i></div>
            <div>
                <div class="kpi-value">{{ $recentResults->count() }}</div>
                <div class="kpi-label">نتائج متاحة</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card slide-up" style="animation-delay:.24s">
            <div class="kpi-icon amber"><i class="fas fa-book-open"></i></div>
            <div>
                <div class="kpi-value">{{ $academicYear ? $academicYear->semesters->count() : 0 }}</div>
                <div class="kpi-label">الفصول الدراسية</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Children Cards --}}
    <div class="col-12 col-xl-7">
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="section-title-bar"></div>
            <span class="section-title">الأبناء المسجلون</span>
        </div>

        @forelse($children as $child)
        @php
            $childLetter = mb_substr($child->name, 0, 1);
            $className = $child->schoolClass->name ?? '';
            $sectionName = str_replace(['الشعبة ', 'شعبة '], '', $child->section->name ?? '');
            $gradeName = $child->grade->name ?? '';
            $classDisplay = trim(str_replace('الصف', '', $className)) . ($sectionName ? ' ('.$sectionName.')' : '');
        @endphp
        <div class="child-card mb-3 slide-up">
            <div class="child-card-header">
                <div class="child-avatar">{{ $childLetter }}</div>
                <div>
                    <div class="child-name">{{ $child->name }}</div>
                    <div class="child-class">
                        @if($gradeName)<span class="me-2"><i class="fas fa-layer-group me-1"></i>{{ $gradeName }}</span>@endif
                        @if($classDisplay)<span><i class="fas fa-door-open me-1"></i>الصف: {{ $classDisplay }}</span>@endif
                    </div>
                </div>
                @if($child->gender)
                <span class="badge ms-auto {{ $child->gender === 'Male' ? 'bg-primary' : 'bg-pink' }} bg-opacity-10 text-{{ $child->gender === 'Male' ? 'primary' : 'danger' }} px-3 py-2">
                    <i class="fas fa-{{ $child->gender === 'Male' ? 'mars' : 'venus' }} me-1"></i>
                    {{ $child->gender === 'Male' ? 'ذكر' : 'أنثى' }}
                </span>
                @endif
            </div>
            <div class="child-actions">
                <a href="{{ route('parent.child.profile', $child->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fas fa-user me-1"></i> الملف الشخصي
                </a>
                <a href="{{ route('parent.attendance', ['student_id' => $child->id]) }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                    <i class="fas fa-calendar-check me-1"></i> الحضور
                </a>
                <a href="{{ route('parent.timetable', ['student_id' => $child->id]) }}" class="btn btn-sm btn-outline-info rounded-pill px-3">
                    <i class="fas fa-calendar-alt me-1"></i> الجدول
                </a>
                <a href="{{ route('parent.results') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                    <i class="fas fa-chart-bar me-1"></i> النتائج
                </a>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-child fs-1 mb-3 d-block opacity-25"></i>
            <p>لا يوجد أبناء مسجلون حالياً</p>
        </div>
        @endforelse
    </div>

    {{-- Right Column --}}
    <div class="col-12 col-xl-5">

        {{-- Quick Links --}}
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="section-title-bar" style="background:#16a34a"></div>
            <span class="section-title">الوصول السريع</span>
        </div>
        <div class="row g-2 mb-4">
            <div class="col-4">
                <a href="{{ route('parent.attendance') }}" class="quick-link">
                    <i class="fas fa-clipboard-user text-success"></i>
                    <span>متابعة الغياب</span>
                </a>
            </div>
            <div class="col-4">
                <a href="{{ route('parent.timetable') }}" class="quick-link">
                    <i class="fas fa-calendar-alt text-primary"></i>
                    <span>جدول الحصص</span>
                </a>
            </div>
            <div class="col-4">
                <a href="{{ route('parent.results') }}" class="quick-link">
                    <i class="fas fa-award text-warning"></i>
                    <span>النتائج</span>
                </a>
            </div>
            <div class="col-4">
                <a href="{{ route('parent.academic-history') }}" class="quick-link">
                    <i class="fas fa-history text-secondary"></i>
                    <span>السجل الأكاديمي</span>
                </a>
            </div>
            <div class="col-4">
                <a href="{{ route('parent.academic-calendar') }}" class="quick-link">
                    <i class="fas fa-calendar-day text-danger"></i>
                    <span>التقويم</span>
                </a>
            </div>
            <div class="col-4">
                <a href="{{ route('parent.profile') }}" class="quick-link">
                    <i class="fas fa-user-circle text-info"></i>
                    <span>الملف الشخصي</span>
                </a>
            </div>
        </div>

        {{-- Recent Results --}}
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="section-title-bar" style="background:#e11d48"></div>
            <span class="section-title">أحدث نتائج الأبناء</span>
        </div>
        <div class="card border-0 shadow-sm" style="border-radius:16px; overflow:hidden;">
            <div class="card-body p-0">
                @if($recentResults->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-folder-open fs-2 mb-3 d-block opacity-25"></i>
                        <p class="mb-0">لا توجد نتائج منشورة بعد</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table results-table mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">الابن</th>
                                    <th class="py-3">المادة</th>
                                    <th class="py-3">الدرجة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentResults as $result)
                                <tr>
                                    <td class="px-4 fw-semibold">{{ $result['child_name'] }}</td>
                                    <td>{{ $result['subject'] }}</td>
                                    <td>
                                        <span class="badge rounded-pill {{ $result['is_passing'] ? 'bg-success' : 'bg-danger' }} bg-opacity-15 {{ $result['is_passing'] ? 'text-success' : 'text-danger' }} px-3 py-2 fw-bold">
                                            {{ $result['percentage'] }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
