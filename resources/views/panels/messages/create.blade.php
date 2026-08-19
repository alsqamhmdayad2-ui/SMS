@extends('layouts.app')
@section('title', 'رسالة جديدة')

@push('styles')
<style>
    /* ── Recipient picker ── */
    .recipient-picker {
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        transition: border-color .2s;
        overflow: hidden;
    }
    .recipient-picker:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,.12);
    }
    .recipient-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 10px 14px;
        min-height: 50px;
        align-items: center;
    }
    .rtag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
        border-radius: 20px;
        padding: 4px 10px 4px 6px;
        font-size: .82rem;
        font-weight: 600;
        white-space: nowrap;
        animation: tagIn .15s ease;
    }
    @keyframes tagIn { from { opacity:0; transform:scale(.8); } to { opacity:1; transform:scale(1); } }
    .rtag .remove-tag {
        cursor: pointer;
        width: 18px; height: 18px;
        background: #bfdbfe;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .7rem;
        color: #1e40af;
        transition: background .15s;
        border: none;
        line-height: 1;
    }
    .rtag .remove-tag:hover { background: #ef4444; color:#fff; }
    .picker-search {
        border: none;
        outline: none;
        flex: 1;
        min-width: 140px;
        font-size: .9rem;
        background: transparent;
        direction: rtl;
        color: #334155;
    }
    .picker-search::placeholder { color: #94a3b8; }

    .picker-dropdown {
        position: absolute;
        width: 100%;
        background: #fff;
        border: 1.5px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 12px 12px;
        max-height: 260px;
        overflow-y: auto;
        z-index: 999;
        display: none;
        box-shadow: 0 8px 25px rgba(0,0,0,.1);
    }
    .picker-dropdown.open { display: block; }
    .picker-group-label {
        padding: 8px 14px 4px;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #94a3b8;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
    }
    .picker-option {
        padding: 9px 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: background .12s;
        font-size: .88rem;
        border-bottom: 1px solid #f8fafc;
    }
    .picker-option:hover, .picker-option.focused { background: #eff6ff; }
    .picker-option.selected { opacity: .4; cursor: default; }
    .picker-option .opt-avatar {
        width: 32px; height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg,#1e3a8a,#3b82f6);
        color: #fff;
        font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        font-size: .8rem;
        flex-shrink: 0;
    }
    .picker-option .opt-info small { color: #94a3b8; font-size: .75rem; }
    .picker-empty { padding: 18px; text-align: center; color: #94a3b8; font-size: .87rem; }

    /* sections chip select */
    .section-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
    .section-chip {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px; border-radius: 20px;
        border: 2px solid #e2e8f0;
        cursor: pointer; transition: all .15s;
        font-size: .85rem; font-weight: 600; color: #475569;
        user-select: none;
    }
    .section-chip:hover { border-color: #3b82f6; color: #3b82f6; background: #eff6ff; }
    .section-chip.active { border-color: #3b82f6; background: #3b82f6; color: #fff; }
    .section-chip.active input { display: none; }
</style>
@endpush

@section('content')
<x-page-header title="الرسائل - رسالة جديدة">
    <x-slot:actions>
        <a href="{{ route('messages.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right me-1"></i> إلغاء
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-pen text-primary me-2"></i> كتابة رسالة جديدة</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('messages.store') }}" method="POST" id="msgForm">
                    @csrf

                    {{-- ── Sections chips (teacher/admin only) ── --}}
                    @if($sections->isNotEmpty())
                    <div class="mb-4 p-3 bg-light rounded-3 border border-secondary-subtle">
                        <label class="form-label fw-bold text-primary mb-2">
                            <i class="fas fa-chalkboard-teacher me-1"></i> إرسال إلى شعبة بالكامل (اختياري)
                        </label>
                        <div class="section-chips" id="sectionChips">
                            @foreach($sections as $section)
                            <label class="section-chip" data-id="{{ $section->id }}">
                                <i class="fas fa-users fa-sm"></i>
                                {{ $section->schoolClass->name }} — {{ $section->name }}
                                <input type="checkbox" name="sections[]" value="{{ $section->id }}" class="section-cb" hidden>
                            </label>
                            @endforeach
                        </div>
                        <div class="form-text mt-2">سيتم إرسال الرسالة لجميع الطلاب المسجلين في الشعبة المختارة.</div>
                    </div>
                    @endif

                    {{-- ── Custom recipient picker ── --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">إلى أفراد محددين</label>
                        <div id="recipientPickerWrap" class="position-relative">
                            <div class="recipient-picker" id="recipientPicker">
                                <div class="recipient-tags" id="tagBox">
                                    <input type="text" class="picker-search" id="pickerSearch" autocomplete="off" placeholder="ابحث باسم الشخص أو البريد...">
                                </div>
                            </div>
                            <div class="picker-dropdown" id="pickerDropdown">
                                @forelse($users as $roleName => $groupUsers)
                                    <div class="picker-group-label">{{ ucfirst($roleName) }}</div>
                                    @foreach($groupUsers as $u)
                                    <div class="picker-option" data-id="{{ $u->id }}" data-name="{{ $u->name }}" data-email="{{ $u->email }}" data-role="{{ $roleName }}">
                                        <div class="opt-avatar">{{ mb_substr($u->name, 0, 1) }}</div>
                                        <div class="opt-info">
                                            <div class="fw-semibold">{{ $u->name }}</div>
                                            <small>{{ $u->email }}</small>
                                        </div>
                                    </div>
                                    @endforeach
                                @empty
                                    <div class="picker-empty">لا يوجد مستخدمون للإرسال إليهم.</div>
                                @endforelse
                            </div>
                        </div>
                        <div id="recipientHiddenInputs"></div>
                        @error('recipients')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Subject --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">عنوان الرسالة <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control form-control-lg" required
                               placeholder="اكتب موضوع الرسالة هنا..." value="{{ old('subject') }}">
                        @error('subject')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Body --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">محتوى الرسالة <span class="text-danger">*</span></label>
                        <textarea name="body" class="form-control" rows="8" required
                                  placeholder="اكتب تفاصيل رسالتك هنا...">{{ old('body') }}</textarea>
                        @error('body')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow-sm">
                            <i class="fas fa-paper-plane me-2"></i> إرسال الرسالة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const search   = document.getElementById('pickerSearch');
    const dropdown = document.getElementById('pickerDropdown');
    const tagBox   = document.getElementById('tagBox');
    const hiddens  = document.getElementById('recipientHiddenInputs');
    const options  = [...document.querySelectorAll('.picker-option')];
    let selected   = new Set();

    // Show / hide dropdown
    search.addEventListener('focus', () => dropdown.classList.add('open'));
    document.addEventListener('click', e => {
        if (!document.getElementById('recipientPickerWrap').contains(e.target))
            dropdown.classList.remove('open');
    });

    // Filter on type
    search.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();
        let anyVisible = false;
        options.forEach(opt => {
            const match = opt.dataset.name.toLowerCase().includes(q) ||
                          opt.dataset.email.toLowerCase().includes(q);
            opt.style.display = match ? '' : 'none';
            if (match) anyVisible = true;
        });
        // Show/hide group labels when all items hidden
        document.querySelectorAll('.picker-group-label').forEach(lbl => {
            const nextItems = [];
            let el = lbl.nextElementSibling;
            while (el && el.classList.contains('picker-option')) {
                nextItems.push(el);
                el = el.nextElementSibling;
            }
            lbl.style.display = nextItems.some(i => i.style.display !== 'none') ? '' : 'none';
        });
        dropdown.classList.add('open');
    });

    // Select option
    options.forEach(opt => {
        opt.addEventListener('click', () => {
            if (selected.has(opt.dataset.id)) return;
            addTag(opt.dataset.id, opt.dataset.name);
            search.value = '';
            search.dispatchEvent(new Event('input'));
            search.focus();
        });
    });

    function addTag(id, name) {
        if (selected.has(id)) return;
        selected.add(id);

        // Tag chip
        const tag = document.createElement('div');
        tag.className = 'rtag';
        tag.dataset.id = id;
        tag.innerHTML = `${name} <button type="button" class="remove-tag">✕</button>`;
        tag.querySelector('.remove-tag').addEventListener('click', () => removeTag(id, tag));
        tagBox.insertBefore(tag, search);

        // Hidden input
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'recipients[]'; inp.value = id; inp.id = 'hi_' + id;
        hiddens.appendChild(inp);

        // Mark option as selected
        const opt = options.find(o => o.dataset.id === id);
        if (opt) opt.classList.add('selected');
    }

    function removeTag(id, tagEl) {
        selected.delete(id);
        tagEl.remove();
        document.getElementById('hi_' + id)?.remove();
        const opt = options.find(o => o.dataset.id === id);
        if (opt) opt.classList.remove('selected');
    }

    // Section chips toggle
    document.querySelectorAll('.section-cb').forEach(cb => {
        cb.addEventListener('change', function () {
            this.closest('.section-chip').classList.toggle('active', this.checked);
        });
    });
})();
</script>
@endpush
