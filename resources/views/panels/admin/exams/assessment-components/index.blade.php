@extends('layouts.app')
@section('title', 'مكونات التقييم')

@section('content')

<x-page-header title="مكونات التقييم">
    <x-slot name="actions">
        @if($selectedSubjectId ?? false)
            <button class="btn btn-outline-info btn-sm me-2" data-bs-toggle="modal" data-bs-target="#previewModal">
                <i class="fas fa-calculator"></i> معاينة الحساب
            </button>
            <button class="btn btn-outline-secondary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#copyModal">
                <i class="fas fa-copy"></i> نسخ من مادة
            </button>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addComponentModal">
                <i class="fas fa-plus"></i> إضافة مكون
            </button>
        @endif
    </x-slot>
</x-page-header>

<x-breadcrumb :items="[
    ['name' => 'الرئيسية', 'url' => route('admin.dashboard')],
    ['name' => 'التقييم والدرجات'],
    ['name' => 'مكونات التقييم']
]" />

<div class="">

    <x-alerts />

    <!-- Filters -->
    <x-shared.card class="mb-4" shadow="sm">
        <form action="{{ route('admin.assessment-components.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <x-form.select name="academic_year_id" label="Academic Year" required="true">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                            {{ $year->name }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-3">
                <x-form.select name="subject_id" label="Subject" required="true">
                    <option value="">Select Subject...</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ $selectedSubjectId == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Load Components
                </button>
            </div>
        </form>
    </x-shared.card>

    @if($selectedSubjectId)
        <!-- Weight Progress Indicator -->
        <x-shared.card class="mb-4" shadow="sm">
            <div class="d-flex justify-content-between align-items-end mb-2">
                <h5 class="card-title text-sms-muted mb-0"><i class="bi bi-speedometer2"></i> Total Weight Status</h5>
                <h4 class="mb-0 fw-bold">
                    @if($totalWeight == 100)
                        <span class="text-success"><i class="bi bi-check-circle-fill"></i> 100% Complete</span>
                    @elseif($totalWeight < 100)
                        <span class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> {{ $totalWeight }}% (Incomplete)</span>
                    @else
                        <span class="text-danger"><i class="bi bi-x-octagon-fill"></i> {{ $totalWeight }}% (Invalid)</span>
                    @endif
                </h4>
            </div>
            
            @php
                $barColor = $totalWeight == 100 ? 'success' : ($totalWeight > 100 ? 'danger' : 'warning');
                $displayWeight = min($totalWeight, 100);
            @endphp
            <x-dashboard.progress 
                :value="$totalWeight" 
                :max="100" 
                :color="$barColor" 
                height="lg">
                <div class="fw-bold px-2 text-white" style="position: absolute; right: 0;">{{ $totalWeight }}%</div>
            </x-dashboard.progress>

            @if($totalWeight > 100)
                <div class="text-danger small mt-2 fw-bold">Error: Total weight exceeds 100%. Please adjust the components.</div>
            @endif
            @if($totalWeight < 100)
                <div class="text-warning text-dark small mt-2 fw-bold">Warning: Total weight must equal exactly 100% before it can be used for calculations.</div>
            @endif
        </x-shared.card>

        <!-- Components Table -->
        <x-shared.card shadow="sm">
            <x-table.data-table hover="true" id="componentsTable">
                <x-slot:header>
                    <th style="width: 50px;"></th> <!-- Drag Handle -->
                    <th>Order</th>
                    <th>Code</th>
                    <th>Component Name</th>
                    <th>Weight %</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </x-slot:header>
                
                <x-slot:body id="sortable-list">
                    @forelse($components->sortBy('order') as $component)
                    <tr data-id="{{ $component->id }}" class="sortable-row">
                        <td class="text-sms-muted" style="cursor: grab;"><i class="bi bi-grip-vertical fs-5"></i></td>
                        <td class="order-number fw-bold">{{ $component->order }}</td>
                        <td><span class="badge bg-secondary font-monospace">{{ $component->code }}</span></td>
                        <td>
                            <strong>{{ $component->name }}</strong>
                            <div class="small text-sms-muted" style="font-size: 0.75rem;">
                                Last updated by User #{{ $component->updated_by ?? 'N/A' }}
                            </div>
                        </td>
                        <td>
                            <h5 class="mb-0"><span class="badge bg-primary">{{ (float)$component->weight_percentage }}%</span></h5>
                        </td>
                        <td>
                            <x-shared.badge :type="$component->status ? 'success' : 'secondary'">
                                {{ $component->status ? 'Active' : 'Draft' }}
                            </x-shared.badge>
                        </td>
                        <td class="text-end">
                            <form action="{{ route('admin.assessment-components.duplicate', $component->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Duplicate">
                                    <i class="bi bi-files"></i>
                                </button>
                            </form>

                            <button class="btn btn-sm btn-outline-primary ms-1" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editComponentModal{{ $component->id }}"
                                    title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            
                            <form action="{{ route('admin.assessment-components.destroy', $component->id) }}" method="POST" class="d-inline ms-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Are you sure you want to delete this component?')"
                                        title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <x-shared.modal id="editComponentModal{{ $component->id }}" title="Edit Component: {{ $component->name }}">
                        <form action="{{ route('admin.assessment-components.update', $component->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <x-slot:body>
                                <div class="alert alert-info py-2">
                                    <i class="bi bi-info-circle"></i> Component Code (<strong>{{ $component->code }}</strong>) cannot be changed to preserve historical calculation integrity.
                                </div>
                                <div class="mb-3">
                                    <x-form.input name="name" label="Component Name" value="{{ $component->name }}" required="true" />
                                </div>
                                <div class="mb-3">
                                    <x-form.input type="number" step="0.01" name="weight_percentage" label="Weight Percentage (%)" value="{{ (float)$component->weight_percentage }}" required="true" min="0" max="100" />
                                </div>
                                <div class="mb-3">
                                    <x-form.switch name="status" label="Active" value="1" :checked="$component->status" />
                                </div>
                            </x-slot:body>
                            <x-slot:footer>
                                <div class="w-100 d-flex justify-content-between align-items-center">
                                    <div class="small text-sms-muted">
                                        Created: {{ $component->created_at?->format('Y-m-d') ?? '—' }}
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </div>
                            </x-slot:footer>
                        </form>
                    </x-shared.modal>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-sms-muted">
                            <x-shared.empty-state 
                                icon="inbox" 
                                title="No assessment components" 
                                message="No assessment components configured for this subject." 
                            />
                        </td>
                    </tr>
                    @endforelse
                </x-slot:body>
            </x-table.data-table>
        </x-shared.card>

        <!-- Add Component Modal -->
        <x-shared.modal id="addComponentModal" title="Add Assessment Component">
            <form action="{{ route('admin.assessment-components.store') }}" method="POST">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
                
                <x-slot:body>
                    <div class="mb-3">
                        <x-form.input name="name" label="Component Name" placeholder="e.g. Midterm Exam" required="true" />
                    </div>
                    <div class="mb-3">
                        <x-form.input name="code" label="Unique Code (System Identifier)" placeholder="e.g. MIDTERM" required="true" style="text-transform: uppercase;" />
                        <div class="form-text text-danger"><i class="bi bi-exclamation-triangle"></i> This code cannot be changed later.</div>
                    </div>
                    <div class="mb-3">
                        <x-form.input type="number" step="0.01" name="weight_percentage" label="Weight Percentage (%)" placeholder="e.g. 20" required="true" min="0" max="100" />
                    </div>
                    <div class="mb-3">
                        <x-form.switch name="status" label="Active" value="1" checked="true" />
                    </div>
                </x-slot:body>
                <x-slot:footer>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Component</button>
                </x-slot:footer>
            </form>
        </x-shared.modal>

        <!-- Copy Components Modal -->
        <x-shared.modal id="copyModal" title="Copy Components From Subject">
            <form action="{{ route('admin.assessment-components.copy') }}" method="POST">
                @csrf
                <input type="hidden" name="target_academic_year_id" value="{{ $selectedYearId }}">
                <input type="hidden" name="target_subject_id" value="{{ $selectedSubjectId }}">
                
                <x-slot:body>
                    <div class="mb-3">
                        <x-form.select name="source_subject_id" label="Select Source Subject" required="true">
                            <option value="">Choose subject to copy from...</option>
                            @foreach($subjects as $subject)
                                @if($subject->id != $selectedSubjectId)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endif
                            @endforeach
                        </x-form.select>
                    </div>
                    <div class="mb-3">
                        <x-form.checkbox name="replace_existing" id="replaceExisting" value="1" label="Delete all existing components in the target subject before copying." labelClass="text-danger" />
                    </div>
                </x-slot:body>
                <x-slot:footer>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to perform this copy action?')">Copy Components</button>
                </x-slot:footer>
            </form>
        </x-shared.modal>

        <!-- Preview Modal -->
        <x-shared.modal id="previewModal" title="<i class='bi bi-calculator'></i> Grade Calculation Preview">
            <x-slot:body class="p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Component</th>
                            <th class="text-end">Weight</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($components->where('status', true)->sortBy('order') as $comp)
                        <tr>
                            <td>{{ $comp->name }}</td>
                            <td class="text-end">{{ (float)$comp->weight_percentage }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td>Total Calculated Grade</td>
                            <td class="text-end {{ $totalWeight == 100 ? 'text-success' : 'text-danger' }}">{{ $totalWeight }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </x-slot:body>
            @if($totalWeight != 100)
            <x-slot:footer class="bg-danger text-white">
                <small><i class="bi bi-exclamation-triangle"></i> This setup is invalid and cannot be published until total weight is exactly 100%.</small>
            </x-slot:footer>
            @endif
        </x-shared.modal>

    @else
        <div class="text-center py-5">
            <h4 class="text-sms-muted">Please select an Academic Year and Subject to view components.</h4>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<!-- Include SortableJS for Drag & Drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var tbody = document.getElementById('sortable-list');
        if(tbody) {
            new Sortable(tbody, {
                animation: 150,
                handle: 'td', // Click anywhere on the row or specific handle
                onEnd: function (evt) {
                    var rows = tbody.querySelectorAll('tr.sortable-row');
                    var newOrder = [];
                    
                    rows.forEach(function(row, index) {
                        var id = row.getAttribute('data-id');
                        var position = index + 1;
                        row.querySelector('.order-number').innerText = position; // Update UI immediately
                        newOrder.push({id: id, position: position});
                    });

                    // Send AJAX request to update order
                    fetch('{{ route('admin.assessment-components.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ order: newOrder })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            // Optional: Show a small toast notification that order was saved
                            console.log('Order saved successfully');
                        }
                    })
                    .catch(error => console.error('Error saving order:', error));
                }
            });
        }
    });
</script>
@endpush
