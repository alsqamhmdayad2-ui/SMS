@extends('layouts.app')

@section('content')
    <x-page-header title="السجل الأكاديمي" />

    <x-breadcrumb :breadcrumbs="[
        ['name' => 'لوحة التحكم', 'url' => route('student.dashboard')],
        ['name' => 'السجل الأكاديمي', 'url' => '']
    ]" />

    <style>
        .timeline-rtl {
            border-right: 3px solid #e9ecef;
            padding-right: 2rem;
            margin-right: 1rem;
            position: relative;
        }
        
        [dir="ltr"] .timeline-rtl {
            border-right: none;
            border-left: 3px solid #e9ecef;
            padding-right: 0;
            padding-left: 2rem;
            margin-right: 0;
            margin-left: 1rem;
        }

        .timeline-item-rtl {
            margin-bottom: 2rem;
            position: relative;
        }

        .timeline-item-rtl::before {
            content: '';
            position: absolute;
            right: -2.4rem;
            top: 1rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background-color: var(--bs-primary);
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px var(--bs-primary);
            z-index: 1;
        }
        
        [dir="ltr"] .timeline-item-rtl::before {
            right: auto;
            left: -2.4rem;
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history text-primary me-2"></i> التسلسل الأكاديمي للطالب
                    </h5>
                </div>
                <div class="card-body p-4">
                    @forelse($enrollments as $enrollment)
                        @if($loop->first)
                            <div class="timeline-rtl">
                        @endif
                        
                        @php
                            $statusTranslations = [
                                'promoted' => ['text' => 'تم الترفيع', 'color' => 'success', 'icon' => 'fa-level-up-alt'],
                                'retained' => ['text' => 'معيد / راسب', 'color' => 'danger', 'icon' => 'fa-redo'],
                                'active' => ['text' => 'نشط (الحالي)', 'color' => 'primary', 'icon' => 'fa-check-circle'],
                                'graduated' => ['text' => 'متخرج', 'color' => 'warning', 'icon' => 'fa-graduation-cap'],
                                'transferred' => ['text' => 'منقول', 'color' => 'info', 'icon' => 'fa-exchange-alt'],
                            ];
                            
                            $status = $statusTranslations[strtolower($enrollment->status)] ?? ['text' => $enrollment->status, 'color' => 'secondary', 'icon' => 'fa-info-circle'];
                        @endphp
                        
                        <div class="timeline-item-rtl">
                            <div class="card bg-light border-0 shadow-none">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                        <h5 class="mb-0 text-primary fw-bold">
                                            <i class="far fa-calendar-alt me-1"></i> العام الدراسي: {{ optional($enrollment->academicYear)->name }}
                                        </h5>
                                        <span class="badge bg-{{ $status['color'] }} px-3 py-2 rounded-pill shadow-sm">
                                            <i class="fas {{ $status['icon'] }} me-1"></i> {{ $status['text'] }}
                                        </span>
                                    </div>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center text-muted">
                                                <div class="bg-white p-2 rounded shadow-sm me-3">
                                                    <i class="fas fa-graduation-cap text-primary fa-fw"></i>
                                                </div>
                                                <div>
                                                    <small class="d-block mb-1">الصف</small>
                                                    <strong class="text-dark">{{ optional($enrollment->grade)->name ?? 'غير محدد' }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if($enrollment->schoolClass)
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center text-muted">
                                                <div class="bg-white p-2 rounded shadow-sm me-3">
                                                    <i class="fas fa-book-open text-primary fa-fw"></i>
                                                </div>
                                                <div>
                                                    <small class="d-block mb-1">الفصل / المسار</small>
                                                    <strong class="text-dark">{{ $enrollment->schoolClass->name }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center text-muted">
                                                <div class="bg-white p-2 rounded shadow-sm me-3">
                                                    <i class="fas fa-users text-primary fa-fw"></i>
                                                </div>
                                                <div>
                                                    <small class="d-block mb-1">الشعبة</small>
                                                    <strong class="text-dark">{{ optional($enrollment->section)->name ?? 'غير محدد' }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($loop->last)
                            </div>
                        @endif
                    @empty
                        <div class="text-center py-5 text-muted">
                            <div class="display-4 text-light mb-3">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <h5>لا يوجد سجل أكاديمي متاح حالياً.</h5>
                            <p>لم يتم العثور على أي بيانات تسجيل سابقة لك في النظام.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
