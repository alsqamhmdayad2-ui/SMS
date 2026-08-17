@extends('layouts.app')
@section('title', 'إنشاء جلسة حضور يدوية')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">فتح جلسة حضور جديدة</h2>
            <p class="text-sms-muted mb-0">للاستخدام في حال غياب معلم الحصة الأولى.</p>
        </div>
        <a href="{{ route('admin.attendance-sessions.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i> عودة للقائمة
        </a>
    </div>

    <x-alerts />

    <x-shared.card shadow="sm" class="border-top border-sms-primary border-4">
        <form action="{{ route('admin.attendance-sessions.store') }}" method="POST">
            @csrf
            <div class="row g-4">
                <div class="col-md-3">
                    <label class="form-label required">العام الدراسي</label>
                    <select class="form-select" name="academic_year_id" required>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label required">الفصل الدراسي</label>
                    <select class="form-select" name="semester_id" required>
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $sem->is_current ? 'selected' : '' }}>
                                {{ $sem->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label required">الشعبة</label>
                    <select class="form-select searchable-select" name="section_id" required>
                        <option value="">اختر الشعبة...</option>
                        @foreach($sections as $sec)
                            <option value="{{ $sec->id }}">
                                {{ $sec->schoolClass?->name }} - {{ $sec->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label required">التاريخ</label>
                    <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> فتح الجلسة
                </button>
            </div>
        </form>
    </x-shared.card>
</div>
@endsection
