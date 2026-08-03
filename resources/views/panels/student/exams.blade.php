@extends('layouts.app')
@section('title', 'الاختبارات - الطالب')

@section('content')

<x-page-header title="الاختبارات">
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('student.dashboard')],
    ['name' => 'الاختبارات']
]" />

<!-- التبويبات -->
<div class="card mb-4">
    <div class="card-body">
        <div class="tabs">
            <button class="tab-btn active" data-tab="tab-available">الاختبارات المتاحة</button>
            <button class="tab-btn" data-tab="tab-completed">الاختبارات المكتملة</button>
        </div>

        <!-- تبويب: الاختبارات المتاحة -->
        <div class="tab-content active" id="tab-available">
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                <!-- بطاقة اختبار 1 -->
                <div class="col">
                    <div class="exam-card h-100">
                        <div class="exam-subject"><i class="fas fa-book" style="margin-inline-start: 4px"></i>رياضيات</div>
                        <div class="exam-title">اختبار الفصل الأول - رياضيات</div>
                        <div class="exam-meta">
                            <span><i class="fas fa-clock"></i> 45 دقيقة</span>
                            <span><i class="fas fa-question-circle"></i> 20 سؤال (اختيار من متعدد)</span>
                            <span><i class="fas fa-star"></i> 100 درجة</span>
                        </div>
                        <div class="exam-status-bar">
                            <span class="badge badge-info">متاح الآن</span>
                            <button class="btn btn-sm btn-secondary"><i class="fas fa-play"></i> بدء الاختبار</button>
                        </div>
                    </div>
                </div>
                <!-- بطاقة اختبار 2 -->
                <div class="col">
                    <div class="exam-card h-100">
                        <div class="exam-subject"><i class="fas fa-flask" style="margin-inline-start: 4px"></i>علوم</div>
                        <div class="exam-title">اختبار منتصف الفصل - علوم</div>
                        <div class="exam-meta">
                            <span><i class="fas fa-clock"></i> 30 دقيقة</span>
                            <span><i class="fas fa-question-circle"></i> 15 سؤال (صح/خطأ + اختيار)</span>
                            <span><i class="fas fa-star"></i> 50 درجة</span>
                        </div>
                        <div class="exam-status-bar">
                            <span class="badge badge-warning">يبدأ غداً</span>
                            <button class="btn btn-sm btn-outline-secondary" disabled><i class="fas fa-lock"></i> غير متاح حالياً</button>
                        </div>
                    </div>
                </div>
                <!-- بطاقة اختبار 3 -->
                <div class="col">
                    <div class="exam-card h-100">
                        <div class="exam-subject"><i class="fas fa-globe" style="margin-inline-start: 4px"></i>لغة إنجليزية</div>
                        <div class="exam-title">اختبار الاستماع والفهم</div>
                        <div class="exam-meta">
                            <span><i class="fas fa-clock"></i> 60 دقيقة</span>
                            <span><i class="fas fa-question-circle"></i> 25 سؤال (اختيار + صور)</span>
                            <span><i class="fas fa-star"></i> 80 درجة</span>
                        </div>
                        <div class="exam-status-bar">
                            <span class="badge badge-info">متاح الآن</span>
                            <button class="btn btn-sm btn-secondary"><i class="fas fa-play"></i> بدء الاختبار</button>
                        </div>
                    </div>
                </div>
                <!-- بطاقة اختبار 4 -->
                <div class="col">
                    <div class="exam-card h-100">
                        <div class="exam-subject"><i class="fas fa-quran" style="margin-inline-start: 4px"></i>تربية إسلامية</div>
                        <div class="exam-title">اختبار نهاية الفصل</div>
                        <div class="exam-meta">
                            <span><i class="fas fa-clock"></i> 40 دقيقة</span>
                            <span><i class="fas fa-question-circle"></i> 30 سؤال (صح/خطأ)</span>
                            <span><i class="fas fa-star"></i> 60 درجة</span>
                        </div>
                        <div class="exam-status-bar">
                            <span class="badge badge-warning">بعد أسبوع</span>
                            <button class="btn btn-sm btn-outline-secondary" disabled><i class="fas fa-lock"></i> غير متاح حالياً</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- تبويب: الاختبارات المكتملة -->
        <div class="tab-content" id="tab-completed">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم الاختبار</th>
                            <th>المادة</th>
                            <th>نوع الأسئلة</th>
                            <th>الدرجة الكلية</th>
                            <th>درجتي</th>
                            <th>النسبة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>اختبار نهائي</td>
                            <td>لغة عربية</td>
                            <td><span class="badge badge-info">اختيار من متعدد</span></td>
                            <td>100</td>
                            <td>92</td>
                            <td><span class="badge badge-success">92%</span></td>
                            <td>2026-02-15</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>اختبار الفصل الأول</td>
                            <td>رياضيات</td>
                            <td><span class="badge badge-primary">صح/خطأ + اختيار</span></td>
                            <td>80</td>
                            <td>68</td>
                            <td><span class="badge badge-success">85%</span></td>
                            <td>2026-02-01</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>اختبار قصير</td>
                            <td>علوم</td>
                            <td><span class="badge badge-warning">صح/خطأ</span></td>
                            <td>30</td>
                            <td>22</td>
                            <td><span class="badge badge-warning">73%</span></td>
                            <td>2026-01-20</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>اختبار منتصف الفصل</td>
                            <td>تربية إسلامية</td>
                            <td><span class="badge badge-info">اختيار من متعدد</span></td>
                            <td>50</td>
                            <td>48</td>
                            <td><span class="badge badge-success">96%</span></td>
                            <td>2026-01-10</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
