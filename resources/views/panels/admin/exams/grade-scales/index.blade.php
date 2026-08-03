@extends('layouts.app')
@section('title', 'سلم الدرجات والتقديرات')

@section('content')

<x-page-header title="سلم الدرجات (Grade Scales)">
    <x-slot name="actions">
        <button class="btn btn-outline-info btn-sm me-2" data-bs-toggle="modal" data-bs-target="#previewModal">
            <i class="fas fa-eye"></i> معاينة السلم الفعال
        </button>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addScaleModal">
            <i class="fas fa-plus"></i> إضافة نطاق درجة
        </button>
    </x-slot>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'التقييم والدرجات'],
    ['name' => 'سلم الدرجات']
]" />

<x-alerts />

<div class="">

    <!-- Range Visualization Bar -->
    <x-shared.card class="mb-4" shadow="sm">
        <h5 class="card-title text-sms-muted mb-3"><i class="bi bi-bar-chart-steps"></i> Active Scale Visualization (0% - 100%)</h5>
        <div class="progress radius-sms-pill" style="height: 30px; font-size: 14px; font-weight: bold;">
            @foreach($scales->where('status', true)->sortBy('percentage_from') as $scale)
                @php
                    $width = $scale->percentage_to - $scale->percentage_from;
                    // Just some dynamic color logic based on GPA/Letter
                    $bg = 'bg-danger';
                    if($scale->is_passing) {
                        if($scale->gpa_point >= 3.5) $bg = 'bg-success';
                        elseif($scale->gpa_point >= 2.5) $bg = 'bg-primary';
                        else $bg = 'bg-warning text-dark';
                    }
                @endphp
                <div class="progress-bar {{ $bg }} border-end border-white" role="progressbar" 
                     style="width: {{ $width }}%" 
                     title="{{ $scale->letter_grade }} ({{ $scale->percentage_from }}% - {{ $scale->percentage_to }}%)"
                     data-bs-toggle="tooltip">
                    {{ $scale->letter_grade }}
                </div>
            @endforeach
        </div>
        <div class="d-flex justify-content-between mt-1 text-sms-muted small fw-bold">
            <span>0%</span>
            <span>100%</span>
        </div>
    </x-shared.card>

    <!-- Data Table -->
    <x-shared.card shadow="sm">
        <x-table.data-table hover="true">
            <x-slot:header>
                <th>Scale Name</th>
                <th>Range (%)</th>
                <th>Letter Grade</th>
                <th>GPA Points</th>
                <th>Min Required %</th>
                <th>Passing</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </x-slot:header>
            <x-slot:body>
                @forelse($scales as $scale)
                <tr>
                    <td>
                        <strong>{{ $scale->name }}</strong>
                        @if(strtolower($scale->name) === 'default scale')
                            <x-shared.badge type="secondary" class="ms-1">Default</x-shared.badge>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            {{ (float)$scale->percentage_from }}% - {{ (float)$scale->percentage_to }}%
                        </span>
                    </td>
                    <td><h5 class="mb-0"><x-shared.badge type="dark">{{ $scale->letter_grade }}</x-shared.badge></h5></td>
                    <td>{{ (float)$scale->gpa_point }}</td>
                    <td>{{ $scale->minimum_required_percentage ? (float)$scale->minimum_required_percentage . '%' : 'N/A' }}</td>
                    <td>
                        @if($scale->is_passing)
                            <x-shared.badge type="success"><i class="bi bi-check-circle"></i> Pass</x-shared.badge>
                        @else
                            <x-shared.badge type="danger"><i class="bi bi-x-circle"></i> Fail</x-shared.badge>
                        @endif
                    </td>
                    <td>
                        @if($scale->status)
                            <x-shared.badge type="primary">Active</x-shared.badge>
                        @else
                            <x-shared.badge type="secondary">Disabled</x-shared.badge>
                        @endif
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editScaleModal{{ $scale->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        
                        <form action="{{ route('admin.grade-scales.destroy', $scale->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                    onclick="return confirm('Are you sure you want to delete this scale?')"
                                    {{ strtolower($scale->name) === 'default scale' ? 'disabled title="Default scales cannot be deleted"' : '' }}>
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <x-shared.modal id="editScaleModal{{ $scale->id }}" title="Edit Grade Scale">
                    <form action="{{ route('admin.grade-scales.update', $scale->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <x-slot:body>
                            <div class="mb-3">
                                <x-form.input name="name" label="Scale Name" value="{{ $scale->name }}" required="true" />
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <x-form.input type="number" step="0.01" name="percentage_from" label="Percentage From (%)" value="{{ (float)$scale->percentage_from }}" required="true" />
                                </div>
                                <div class="col-md-6">
                                    <x-form.input type="number" step="0.01" name="percentage_to" label="Percentage To (%)" value="{{ (float)$scale->percentage_to }}" required="true" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <x-form.input name="letter_grade" label="Letter Grade" value="{{ $scale->letter_grade }}" required="true" />
                                </div>
                                <div class="col-md-6">
                                    <x-form.input type="number" step="0.01" name="gpa_point" label="GPA Point" value="{{ (float)$scale->gpa_point }}" required="true" />
                                </div>
                            </div>
                            <div class="mb-3">
                                <x-form.input type="number" step="0.01" name="minimum_required_percentage" label="Minimum Required Percentage (%)" value="{{ $scale->minimum_required_percentage ? (float)$scale->minimum_required_percentage : '' }}" help="Optional. Example: 60 for passing." />
                            </div>
                            <div class="mb-3">
                                <x-form.switch name="is_passing" label="Is Passing Grade?" value="1" :checked="$scale->is_passing" />
                            </div>
                            <div class="mb-3">
                                <x-form.switch name="status" label="Active" value="1" :checked="$scale->status" />
                            </div>
                        </x-slot:body>
                        <x-slot:footer>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </x-slot:footer>
                    </form>
                </x-shared.modal>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-sms-muted">
                        <x-shared.empty-state 
                            icon="bar-chart-steps" 
                            title="No grade scales" 
                            message="No grade scales found." 
                        />
                    </td>
                </tr>
                @endforelse
            </x-slot:body>
        </x-table.data-table>
    </x-shared.card>
</div>

<!-- Add Modal -->
<x-shared.modal id="addScaleModal" title="Add New Grade Scale Range">
    <form action="{{ route('admin.grade-scales.store') }}" method="POST">
        @csrf
        <x-slot:body>
            <div class="mb-3">
                <x-form.input name="name" label="Scale Name" value="Default Scale" required="true" />
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <x-form.input type="number" step="0.01" name="percentage_from" label="Percentage From (%)" required="true" />
                </div>
                <div class="col-md-6">
                    <x-form.input type="number" step="0.01" name="percentage_to" label="Percentage To (%)" required="true" />
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <x-form.input name="letter_grade" label="Letter Grade" placeholder="e.g. A+" required="true" />
                </div>
                <div class="col-md-6">
                    <x-form.input type="number" step="0.01" name="gpa_point" label="GPA Point" placeholder="e.g. 4.0" required="true" />
                </div>
            </div>
            <div class="mb-3">
                <x-form.input type="number" step="0.01" name="minimum_required_percentage" label="Minimum Required Percentage (%)" value="60" help="Optional. Example: 60 for passing." />
            </div>
            <div class="mb-3">
                <x-form.switch name="is_passing" label="Is Passing Grade?" value="1" checked="true" />
            </div>
            <div class="mb-3">
                <x-form.switch name="status" label="Active" value="1" checked="true" />
            </div>
        </x-slot:body>
        <x-slot:footer>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Range</button>
        </x-slot:footer>
    </form>
</x-shared.modal>

<!-- Preview Modal -->
<x-shared.modal id="previewModal" title="<i class='bi bi-file-earmark-spreadsheet'></i> Grade Scale Preview">
    <x-slot:body class="p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Percentage</th>
                    <th>Letter</th>
                    <th>GPA</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scales->where('status', true)->sortByDesc('percentage_from') as $scale)
                <tr class="{{ $scale->is_passing ? 'table-success' : 'table-danger' }}">
                    <td>{{ (float)$scale->percentage_from }} - {{ (float)$scale->percentage_to }}</td>
                    <td class="fw-bold">{{ $scale->letter_grade }}</td>
                    <td>{{ (float)$scale->gpa_point }}</td>
                    <td>{{ $scale->is_passing ? 'Pass' : 'Fail' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </x-slot:body>
</x-shared.modal>

@endsection

@push('scripts')
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
@endpush
