@props([
    'hover' => true,
    'striped' => false,
    'bordered' => false
])

<div class="sms-table-wrapper table-responsive w-100">
    <table {{ $attributes->merge(['class' => 'table mb-0 align-middle ' . ($hover ? 'table-hover' : '') . ' ' . ($striped ? 'table-striped' : '') . ' ' . ($bordered ? 'table-bordered' : '')]) }}>
        @isset($header)
            <thead class="table-light">
                <tr>
                    {{ $header }}
                </tr>
            </thead>
        @endisset
        
        <tbody>
            @if(isset($body) && trim($body) !== '')
                {{ $body }}
            @else
                <tr>
                    <td colspan="100%" class="text-center p-0">
                        <x-shared.empty-state />
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

@isset($pagination)
    <div class="d-flex justify-content-between align-items-center p-3 border-top bg-white">
        {{ $pagination }}
    </div>
@endisset
