@extends('layout')
@section('title', 'إدارة مجموعات الشيفتات')

@section('content')
@include('developer.roles._styles')

<style>
    .shift-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 18px;
        padding: 1.25rem;
        box-shadow: 0 1px 3px rgba(15,23,42,.05);
        transition: all .2s ease;
        position: relative;
        overflow: hidden;
    }
    .shift-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -10px rgba(15,23,42,.12);
    }
    .shift-card-bar {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        height: 4px;
    }
    .shift-time-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-size: .82rem;
        font-weight: 700;
        padding: .35rem .65rem;
        border-radius: 10px;
        margin-top: .5rem;
    }
    .shift-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .user-shift-select {
        border-radius: 8px;
        font-size: .85rem;
        font-weight: 600;
        padding: .4rem .6rem;
        border: 1px solid #cbd5e1;
        background-color: #fff;
        cursor: pointer;
        width: 100%;
        max-width: 220px;
    }
    .user-shift-select:focus {
        border-color: #2563eb;
        outline: none;
        box-shadow: 0 0 0 3px rgba(37,99,235,.15);
    }
</style>

<div class="dv-wrap">

    <header class="dv-hero">
        <div class="dv-hero-row">
            <div>
                <span class="dv-badge-dev">🛡️ منطقة المطور فقط</span>
                <h1>⏰ إدارة مجموعات الشيفتات</h1>
                <p>حدد أوقات وساعات كل شيفت لمنع تسجيل أي عمل إضافي داخل ساعات الشيفت المقررة، ووزّع الموظفين بسهولة.</p>
            </div>
            <a href="{{ route('developer.shifts.create') }}" class="dv-btn dv-btn-light" id="btn-create-shift">➕ مجموعة شيفت جديدة</a>
        </div>
    </header>

    @include('developer.roles._alerts')

    {{-- Stats Cards --}}
    <section class="dv-stats">
        <div class="dv-stat">
            <div class="dv-stat-icon" style="background:#ede9fe">⏰</div>
            <div><b>{{ $shifts->count() }}</b><span>مجموعات شيفتات</span></div>
        </div>
        <div class="dv-stat">
            <div class="dv-stat-icon" style="background:#dcfce7">👥</div>
            <div><b>{{ $totalAssigned }}</b><span>موظف تم تحديد شيفتهم</span></div>
        </div>
        <div class="dv-stat">
            <div class="dv-stat-icon" style="background:#fef3c7">⏳</div>
            <div><b>{{ $totalUnassigned }}</b><span>موظف بدون شيفت محدد</span></div>
        </div>
    </section>

    {{-- الشيفتات الحالية --}}
    <div class="dv-section-title">
        <h2>📋 مجموعات الشيفتات المعتمدة</h2>
        <small>الساعات المحددة لكل شيفت يُحظر تسجيل إضافي بداخلها تلقائياً</small>
    </div>

    <section class="shift-grid">
        @foreach($shifts as $shift)
            <div class="shift-card">
                <div class="shift-card-bar" style="background: {{ $shift->color }};"></div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span style="font-size: 1.8rem;">{{ $shift->icon }}</span>
                    <span class="badge" style="background: {{ $shift->color }}15; color: {{ $shift->color }}; font-weight: 800; font-size: .8rem; padding: 4px 10px; border-radius: 999px;">
                        👥 {{ $shift->users_count }} موظف
                    </span>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0 0 .25rem; color: #0f172a;">
                    {{ $shift->name }}
                </h3>
                <div class="shift-time-badge" style="background: {{ $shift->color }}10; color: {{ $shift->color }}; border: 1px solid {{ $shift->color }}30;">
                    🕒 {{ $shift->formatted_hours }}
                </div>
                <div class="text-muted mt-2" style="font-size: .78rem; font-weight: 600;">
                    مدة الشيفت: {{ $shift->duration_hours }} ساعات
                    @if($shift->crossesMidnight())
                        <span class="badge bg-purple-100 text-purple-700 ms-1" style="font-size: .7rem;">سهر (يعبر منتصف الليل)</span>
                    @endif
                </div>

                <div class="mt-3 pt-3 border-top d-flex gap-2 justify-content-end">
                    <a href="{{ route('developer.shifts.edit', $shift) }}" class="dv-btn dv-btn-ghost dv-btn-sm">✏️ تعديل الأوقات</a>
                    <form action="{{ route('developer.shifts.destroy', $shift) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف {{ $shift->name }}؟ سيتم إلغاء ارتباط الموظفين به.');">
                        @csrf
                        <button type="submit" class="dv-btn dv-btn-danger dv-btn-sm">🗑️ حذف</button>
                    </form>
                </div>
            </div>
        @endforeach
    </section>

    {{-- جدول توزيع الموظفين على الشيفتات --}}
    <div class="dv-section-title mt-4">
        <h2>👥 توزيع الموظفين على مجموعات الشيفتات</h2>
        <small>غيّر شيفت أي موظف فوراً من القائمة المنسدلة واضغط حفظ</small>
    </div>

    <div class="dv-card mb-4">
        <div class="p-3 border-bottom bg-slate-50 d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <form action="{{ route('developer.shifts.index') }}" method="GET" class="d-flex flex-wrap gap-2 align-items-center" style="flex: 1; max-width: 600px;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="🔍 بحث بالاسم أو الإيميل..." class="form-control form-control-sm" style="max-width: 260px; border-radius: 8px;">
                <select name="shift" class="form-select form-select-sm" style="max-width: 180px; border-radius: 8px;">
                    <option value="">كل الشيفتات</option>
                    <option value="unassigned" {{ request('shift') === 'unassigned' ? 'selected' : '' }}>⏳ بدون شيفت محدد</option>
                    @foreach($shifts as $s)
                        <option value="{{ $s->id }}" {{ request('shift') == $s->id ? 'selected' : '' }}>{{ $s->icon }} {{ $s->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="dv-btn dv-btn-ghost dv-btn-sm">بحث</button>
                @if(request()->hasAny(['q', 'shift']))
                    <a href="{{ route('developer.shifts.index') }}" class="dv-btn dv-btn-ghost dv-btn-sm text-danger">إلغاء الفلتر</a>
                @endif
            </form>
            <span class="text-muted fw-bold" style="font-size: .85rem;">إجمالي المعروض: {{ $users->total() }} موظف</span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size: .9rem;">
                <thead style="background: #f8fafc; font-weight: 700; color: #475569;">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>اسم الموظف</th>
                        <th>البريد الإلكتروني</th>
                        <th>الفئة (Role)</th>
                        <th>الشيفت المسند</th>
                        <th style="width: 140px; text-align: center;">إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $u)
                        <tr>
                            <td>{{ $users->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $u->name }}</div>
                                @if($u->job_title)
                                    <small class="text-muted">{{ $u->job_title }}</small>
                                @endif
                            </td>
                            <td><span class="text-muted" style="font-size: .85rem;">{{ $u->email }}</span></td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $u->roleModel?->display_name ?? $u->role }}
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('developer.shifts.assign_user', $u) }}" method="POST" id="form-shift-{{ $u->id }}" class="d-flex align-items-center gap-2">
                                    @csrf
                                    <select name="shift_group_id" class="user-shift-select" onchange="document.getElementById('save-btn-{{ $u->id }}').classList.remove('d-none');">
                                        <option value="">-- بدون شيفت محدد --</option>
                                        @foreach($shifts as $s)
                                            <option value="{{ $s->id }}" {{ $u->shift_group_id == $s->id ? 'selected' : '' }}>
                                                {{ $s->icon }} {{ $s->name }} ({{ $s->formatted_hours }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" id="save-btn-{{ $u->id }}" class="dv-btn dv-btn-primary dv-btn-sm d-none">
                                        💾 حفظ
                                    </button>
                                </form>
                            </td>
                            <td style="text-align: center;">
                                @if($u->shiftGroup)
                                    <span class="badge" style="background: {{ $u->shiftGroup->color }}15; color: {{ $u->shiftGroup->color }}; font-weight: 700; padding: 5px 10px; border-radius: 8px;">
                                        {{ $u->shiftGroup->icon }} {{ $u->shiftGroup->name }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary text-white" style="font-weight: 600; padding: 5px 10px; border-radius: 8px;">
                                        غير محدد
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                لا يوجد موظفين مطابقين لشروط البحث.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-3 border-top">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
