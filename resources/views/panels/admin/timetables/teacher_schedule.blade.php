@extends('layouts.app')

@section('title', 'Teacher Schedule')

@section('content')
@include('components.page-header', [
    'title' => 'Teacher Schedule',
    'subtitle' => 'Weekly schedule for ' . $teacher->name
])

<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Teachers
    </a>
    <a href="{{ route('admin.timetables.index') }}" class="btn btn-primary">
        <i class="bi bi-calendar3"></i> Class Timetables
    </a>
</div>

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">{{ $teacher->name }}'s Weekly Schedule</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="12%">Time / Day</th>
                        @foreach(['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'] as $day)
                            <th width="14%">{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($periods as $period)
                        <tr>
                            <td class="fw-bold bg-light">Period {{ $period }}</td>
                            @foreach(['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'] as $day)
                                @php
                                    $entry = $weeklySchedule[$day][$period] ?? null;
                                @endphp
                                <td>
                                    @if($entry)
                                        <div class="p-2 border rounded bg-white shadow-sm border-start border-3 border-primary">
                                            <div class="fw-bold text-primary mb-1">{{ $entry->subject->name }}</div>
                                            <div class="small text-muted mb-1">
                                                <i class="bi bi-diagram-3"></i> {{ $entry->grade->name ?? '' }} - {{ $entry->schoolClass->name ?? '' }} ({{ $entry->section->name }})
                                            </div>
                                            <div class="small text-muted mb-0">
                                                <i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($entry->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($entry->end_time)->format('H:i') }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">No schedule entries found for this teacher.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
