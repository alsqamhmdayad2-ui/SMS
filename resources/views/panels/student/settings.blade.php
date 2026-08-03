@extends('layouts.app')
@section('title', 'الإعدادات - الطالب')

@section('content')

<x-page-header title="الإعدادات">
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('student.dashboard')],
    ['name' => 'الإعدادات']
]" />

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-light pt-4 pb-3">
                <h5 class="fw-bold m-0"><i class="fas fa-lock ms-2 text-primary"></i>تغيير كلمة المرور</h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label fw-bold">كلمة المرور الحالية</label>
                        <input type="password" class="form-control" name="current_password" placeholder="أدخل كلمة المرور الحالية">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">كلمة المرور الجديدة</label>
                        <input type="password" class="form-control" name="new_password" placeholder="أدخل كلمة المرور الجديدة">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">تأكيد كلمة المرور الجديدة</label>
                        <input type="password" class="form-control" name="new_password_confirmation" placeholder="أعد إدخال كلمة المرور الجديدة">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save ms-1"></i> حفظ التغييرات</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-light pt-4 pb-3">
                <h5 class="fw-bold m-0"><i class="fas fa-bell ms-2 text-warning"></i>إعدادات الإشعارات</h5>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="notifyExams" checked>
                    <label class="form-check-label fw-bold" for="notifyExams">إشعارات الاختبارات</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="notifyResults" checked>
                    <label class="form-check-label fw-bold" for="notifyResults">إشعارات النتائج</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="notifyAttendance" checked>
                    <label class="form-check-label fw-bold" for="notifyAttendance">إشعارات الحضور</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="notifyGeneral">
                    <label class="form-check-label fw-bold" for="notifyGeneral">الإعلانات العامة</label>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
