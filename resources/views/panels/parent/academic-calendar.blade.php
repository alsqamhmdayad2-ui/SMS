@extends('layouts.app')
@section('title', 'التقويم الأكاديمي')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<style>
    .fc-event {
        cursor: pointer;
        transition: transform 0.2s;
    }
    .fc-event:hover {
        transform: scale(1.02);
    }
    .legend-indicator {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-left: 5px;
    }
    /* Dynamic UI enhancements */
    .fc .fc-toolbar-title {
        font-weight: bold;
        color: var(--bs-primary);
    }
    .fc .fc-button-primary {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
    }
    .fc .fc-button-primary:hover {
        background-color: var(--bs-primary-dark, #0b5ed7);
        border-color: var(--bs-primary-dark, #0a58ca);
    }
</style>
@endpush

@section('content')

<x-page-header title="التقويم الأكاديمي">
    <x-slot:actions>
        <span class="text-muted"><i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('F Y') }}</span>
    </x-slot:actions>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('parent.dashboard')],
    ['name' => 'التقويم الأكاديمي']
]" />

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('parent.academic-calendar') }}" method="GET" class="d-flex align-items-center">
                    <label for="student_id" class="form-label me-3 mb-0 fw-bold">اختر الابن:</label>
                    <select name="student_id" id="student_id" class="form-select w-50" onchange="this.form.submit()">
                        @forelse($children as $child)
                            <option value="{{ $child->id }}" {{ ($selectedStudent && $selectedStudent->id == $child->id) ? 'selected' : '' }}>
                                {{ $child->user->name }}
                            </option>
                        @empty
                            <option value="">لا يوجد أبناء مسجلين</option>
                        @endforelse
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>

@if(!$children->isEmpty() && $selectedStudent)
<div class="row">
    <div class="col-xl-9 col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div id="academic-calendar"></div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-4">
        <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 80px;">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h6 class="card-title mb-0 fw-bold"><i class="fas fa-list-ul me-2 text-primary"></i>دليل التقويم</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3 d-flex align-items-center">
                        <span class="legend-indicator" style="background-color: #3b82f6;"></span>
                        <span>بداية العام الدراسي</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <span class="legend-indicator" style="background-color: #ef4444;"></span>
                        <span>نهاية العام الدراسي</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <span class="legend-indicator" style="background-color: #8b5cf6;"></span>
                        <span>بداية الفصل الدراسي</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <span class="legend-indicator" style="background-color: #ec4899;"></span>
                        <span>نهاية الفصل الدراسي</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <span class="legend-indicator" style="background-color: #dc2626;"></span>
                        <span>الامتحانات</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <span class="legend-indicator" style="background-color: #f59e0b;"></span>
                        <span>العطل الرسمية</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <span class="legend-indicator" style="background-color: #10b981;"></span>
                        <span>الفعاليات والأنشطة</span>
                    </li>
                    <li class="d-flex align-items-center">
                        <span class="legend-indicator" style="background-color: #6366f1;"></span>
                        <span>الاجتماعات</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold" id="eventTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-3">
        <div class="d-flex align-items-center mb-3">
            <div class="flex-shrink-0 me-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white" id="eventIcon" style="width: 48px; height: 48px; background-color: var(--bs-primary);">
                    <i class="fas fa-calendar-day fs-4"></i>
                </div>
            </div>
            <div>
                <p class="mb-0 text-muted small" id="eventDate"></p>
                <span class="badge rounded-pill bg-light text-dark border mt-1" id="eventTypeBadge"></span>
            </div>
        </div>
        <p class="mb-0 text-secondary" id="eventDescription" style="display: none;"></p>
      </div>
      <div class="modal-footer border-top-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إغلاق</button>
      </div>
    </div>
  </div>
</div>
@else
<div class="alert alert-info">
    لا يوجد أبناء مسجلين لعرض التقويم الخاص بهم.
</div>
@endif

@endsection

@push('scripts')
@if(!$children->isEmpty() && $selectedStudent)
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/ar.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('academic-calendar');
        var eventsData = @json($events);

        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'ar',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            buttonText: {
                today: 'اليوم',
                month: 'شهر',
                week: 'أسبوع',
                list: 'قائمة'
            },
            events: eventsData,
            eventClick: function(info) {
                // Prevent browser navigation
                info.jsEvent.preventDefault();
                
                var event = info.event;
                var props = event.extendedProps;
                
                // Populate Modal
                document.getElementById('eventTitle').textContent = event.title;
                
                // Format Date
                var dateStr = event.start.toLocaleDateString('ar-EG', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                if (event.end && !event.allDay) {
                    dateStr += ' - ' + event.end.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
                }
                document.getElementById('eventDate').textContent = dateStr;
                
                // Set Badge
                var typeBadge = document.getElementById('eventTypeBadge');
                var typeText = 'حدث';
                var iconClass = 'fa-calendar-day';
                
                if (props.type === 'academic_year') { typeText = 'العام الدراسي'; iconClass = 'fa-graduation-cap'; }
                else if (props.type === 'semester') { typeText = 'الفصل الدراسي'; iconClass = 'fa-book-open'; }
                else if (props.type === 'exam') { typeText = 'امتحان'; iconClass = 'fa-file-alt'; }
                else if (props.type === 'holiday') { typeText = 'عطلة رسمية'; iconClass = 'fa-umbrella-beach'; }
                else if (props.type === 'meeting') { typeText = 'اجتماع'; iconClass = 'fa-users'; }
                else if (props.type === 'activity') { typeText = 'نشاط'; iconClass = 'fa-running'; }
                
                typeBadge.textContent = typeText;
                document.getElementById('eventIcon').style.backgroundColor = event.backgroundColor || 'var(--bs-primary)';
                document.getElementById('eventIcon').innerHTML = '<i class="fas ' + iconClass + ' fs-4"></i>';
                
                // Description
                var descEl = document.getElementById('eventDescription');
                if (props.description) {
                    descEl.textContent = props.description;
                    descEl.style.display = 'block';
                } else {
                    descEl.style.display = 'none';
                }
                
                // Show Modal
                var modal = new bootstrap.Modal(document.getElementById('eventModal'));
                modal.show();
            }
        });
        
        calendar.render();
    });
</script>
@endif
@endpush
