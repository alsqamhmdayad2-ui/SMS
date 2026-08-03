@props(['status'])

@php
    $status = strtolower($status);
    $color = match($status) {
        'active', 'present', 'open' => 'success',
        'inactive', 'absent', 'closed', 'locked' => 'danger',
        'pending', 'late' => 'warning',
        'excused' => 'info',
        'sick' => 'secondary',
        default => 'secondary'
    };

    $label = match($status) {
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'pending' => 'قيد الانتظار',
        'present' => 'حاضر',
        'absent' => 'غائب',
        'late' => 'متأخر',
        'excused' => 'مستأذن',
        'sick' => 'مريض',
        'open' => 'مفتوح',
        'closed' => 'مغلق',
        'locked' => 'مقفل',
        default => ucfirst($status)
    };
@endphp

<span class="badge bg-{{ $color }}">{{ $label }}</span>
