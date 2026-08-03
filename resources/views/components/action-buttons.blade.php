@props([
    'editUrl' => null,
    'showUrl' => null,
    'deleteUrl' => null,
    'restoreUrl' => null,
    'id' => null
])

<div class="d-flex gap-1 justify-content-center">
    @if($showUrl)
        <a href="{{ $showUrl }}" class="btn btn-sm btn-info text-white" title="عرض">
            <i class="fas fa-eye"></i>
        </a>
    @endif

    @if($editUrl)
        <a href="{{ $editUrl }}" class="btn btn-sm btn-primary" title="تعديل">
            <i class="fas fa-edit"></i>
        </a>
    @endif

    @if($restoreUrl)
        <form action="{{ $restoreUrl }}" method="POST" class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-sm btn-success" title="استعادة" onclick="return confirm('هل أنت متأكد من استعادة هذا السجل؟')">
                <i class="fas fa-undo"></i>
            </button>
        </form>
    @endif

    @if($deleteUrl)
        <form action="{{ $deleteUrl }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" title="حذف" onclick="return confirm('هل أنت متأكد من حذف هذا السجل؟')">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    @endif
</div>
