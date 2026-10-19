@extends('layout')
@section('title', $shift->exists ? 'تعديل مجموعة شيفت' : 'إضافة مجموعة شيفت جديدة')

@section('content')
@include('developer.roles._styles')

<style>
    .color-swatch {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        cursor: pointer;
        display: inline-grid;
        place-items: center;
        border: 2px solid transparent;
        transition: all .15s ease;
    }
    .color-swatch:hover {
        transform: scale(1.1);
    }
    .color-swatch.active {
        border-color: #0f172a;
        box-shadow: 0 0 0 2px #fff inset;
    }
    .icon-choice {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        font-size: 1.4rem;
        display: inline-grid;
        place-items: center;
        background: #f1f5f9;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all .15s ease;
    }
    .icon-choice:hover {
        background: #e2e8f0;
    }
    .icon-choice.active {
        background: #e0e7ff;
        border-color: #6366f1;
    }
</style>

<div class="dv-wrap" style="max-width: 750px;">

    <div class="dv-crumbs">
        <a href="{{ route('developer.shifts.index') }}">⏰ مجموعات الشيفتات</a> /
        <span>{{ $shift->exists ? 'تعديل' : 'إضافة جديدة' }}</span>
    </div>

    <header class="dv-hero">
        <div class="dv-hero-row">
            <div>
                <span class="dv-badge-dev">🛡️ منطقة المطور فقط</span>
                <h1>{{ $shift->exists ? '✏️ تعديل: ' . $shift->name : '➕ إضافة مجموعة شيفت جديدة' }}</h1>
                <p>حدد ساعات البداية والنهاية للشيفت لحظر طلبات الإضافي داخل هذا النطاق الزمني.</p>
            </div>
            <a href="{{ route('developer.shifts.index') }}" class="dv-btn dv-btn-light">⬅️ رجوع</a>
        </div>
    </header>

    @include('developer.roles._alerts')

    <div class="dv-card dv-card-pad">
        <form action="{{ $shift->exists ? route('developer.shifts.update', $shift) : route('developer.shifts.store') }}" method="POST">
            @csrf

            {{-- اسم الشيفت --}}
            <div class="mb-4">
                <label class="form-label fw-bold">اسم مجموعة الشيفت <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control form-control-lg" required value="{{ old('name', $shift->name) }}" placeholder="مثال: جروب الصباحي، جروب السهر..." style="border-radius: 10px;">
                <small class="text-muted">الاسم الذي يظهر للموظفين وفي كشوفات الإدارة.</small>
            </div>

            {{-- ساعات الشيفت --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">🕒 وقت بداية الشيفت (من) <span class="text-danger">*</span></label>
                    <input type="time" name="from_time" class="form-control form-control-lg" required value="{{ old('from_time', substr($shift->from_time ?? '09:00', 0, 5)) }}" style="border-radius: 10px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">🕒 وقت نهاية الشيفت (إلى) <span class="text-danger">*</span></label>
                    <input type="time" name="to_time" class="form-control form-control-lg" required value="{{ old('to_time', substr($shift->to_time ?? '16:00', 0, 5)) }}" style="border-radius: 10px;">
                </div>
                <div class="col-12">
                    <div class="p-3 bg-amber-50 rounded-3 border border-amber-200 text-amber-800" style="font-size: .85rem;">
                        💡 <b>ملاحظة للشيفتات الليلية (السهر):</b> إذا كان وقت النهاية أصغر من وقت البداية (مثلاً من 11:00 م إلى 8:00 ص)، فالسيستم يتعرف تلقائياً على أن الشيفت يعبر منتصف الليل ويحظر الإضافي طوال ساعات الليل حتى الصباح.
                    </div>
                </div>
            </div>

            {{-- الأيقونة --}}
            <div class="mb-4">
                <label class="form-label fw-bold">الأيقونة المعبرة</label>
                <input type="hidden" name="icon" id="input-icon" value="{{ old('icon', $shift->icon ?? '⏰') }}">
                <div class="d-flex flex-wrap gap-2 mt-1">
                    @foreach($icons as $ic)
                        <div class="icon-choice {{ old('icon', $shift->icon ?? '⏰') === $ic ? 'active' : '' }}" onclick="selectIcon(this, '{{ $ic }}')">
                            {{ $ic }}
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- اللون --}}
            <div class="mb-4">
                <label class="form-label fw-bold">اللون المميز للشيفت</label>
                <input type="hidden" name="color" id="input-color" value="{{ old('color', $shift->color ?? '#2563eb') }}">
                <div class="d-flex flex-wrap gap-2 mt-1">
                    @foreach($colors as $cl)
                        <div class="color-swatch {{ old('color', $shift->color ?? '#2563eb') === $cl ? 'active' : '' }}" style="background: {{ $cl }};" onclick="selectColor(this, '{{ $cl }}')"></div>
                    @endforeach
                </div>
            </div>

            {{-- الوصف --}}
            <div class="mb-4">
                <label class="form-label fw-bold">ملاحظات / وصف الشيفت (اختياري)</label>
                <input type="text" name="description" class="form-control" value="{{ old('description', $shift->description) }}" placeholder="مثال: شيفت قسم الاستقبال / التمريض الصباحي..." style="border-radius: 10px;">
            </div>

            <div class="d-flex gap-2 justify-content-end pt-3 border-top">
                <a href="{{ route('developer.shifts.index') }}" class="dv-btn dv-btn-ghost">إلغاء</a>
                <button type="submit" class="dv-btn dv-btn-primary">
                    💾 {{ $shift->exists ? 'حفظ التعديلات' : 'إنشاء مجموعة الشيفت' }}
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    function selectIcon(el, icon) {
        document.querySelectorAll('.icon-choice').forEach(i => i.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('input-icon').value = icon;
    }
    function selectColor(el, color) {
        document.querySelectorAll('.color-swatch').forEach(c => c.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('input-color').value = color;
    }
</script>
@endsection
