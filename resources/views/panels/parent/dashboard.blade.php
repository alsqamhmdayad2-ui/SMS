@extends('layouts.app')
@section('title', 'لوحة تحكم ولي الأمر')

@section('content')

<x-page-header title="لوحة تحكم ولي الأمر">
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('parent.dashboard')],
    ['name' => 'لوحة التحكم']
]" />

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-12 col-md-3">
        <div class="stat-card slide-up blue h-100">
            <div class="stat-icon"><i class="fas fa-child"></i></div>
            <div class="stat-details"><h3>{{ $totalChildren }}</h3><p>عدد الأبناء</p></div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="stat-card slide-up green h-100" style="animation-delay: 0.1s;">
            <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-details"><h3>{{ $attendanceSummary }}%</h3><p>متوسط الحضور</p></div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="stat-card slide-up purple h-100" style="animation-delay: 0.2s;">
            <div class="stat-icon"><i class="fas fa-bell"></i></div>
            <div class="stat-details"><h3>0</h3><p>آخر إشعار</p></div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="stat-card slide-up orange h-100" style="animation-delay: 0.3s;">
            <div class="stat-icon"><i class="fas fa-chart-bar"></i></div>
            <div class="stat-details"><h3>{{ $recentResults->count() }}</h3><p>آخر نتائج</p></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Results -->
    <div class="col-12 col-lg-8 offset-lg-2">
        <div class="card h-100">
            <div class="card-header">
                <h3><i class="fas fa-chart-line text-accent" style="margin-inline-start: 8px"></i>أحدث نتائج الأبناء</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover data-table">
                        <thead>
                            <tr>
                                <th>الابن</th>
                                <th>المادة</th>
                                <th>الدرجة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentResults as $result)
                                <tr>
                                    <td><strong>{{ $result['child_name'] }}</strong></td>
                                    <td>{{ $result['subject'] }}</td>
                                    <td>
                                        <span class="badge {{ $result['is_passing'] ? 'badge-success' : 'badge-warning' }}">
                                            {{ $result['percentage'] }} / 100
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="fas fa-folder-open fs-4 mb-2 d-block opacity-50"></i>
                                        لا توجد نتائج حديثة
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
