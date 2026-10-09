@extends('layout')
@section('title', 'فئة: ' . $role->display_name)

@section('content')
@include('developer.roles._styles')

@php $total = count(\App\Support\Permissions::all()); @endphp

<div class="dv-wrap">

    <header class="dv-hero" style="background:
        radial-gradient(900px 260px at 100% 0%, {{ $role->color }}66, transparent 60%),
        linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%);">
        <div class="dv-crumbs"><a href="{{ route('developer.roles.index') }}">إدارة الفئات</a> / {{ $role->display_name }}</div>
        <div class="dv-hero-row">
            <div>
                <span class="dv-badge-dev">🛡️ منطقة المطور فقط</span>
                <h1>{{ $role->display_name }}</h1>
                <p>{{ $role->description ?: 'بدون وصف' }} — <code dir="ltr" style="color:#c7d2fe">{{ $role->name }}</code></p>
            </div>
            <div style="display:flex; gap:.5rem; flex-wrap:wrap">
                <a href="{{ route('developer.roles.edit', $role) }}" class="dv-btn dv-btn-light" id="btn-edit-role">🔐 تعديل الصلاحيات</a>
                <a href="{{ route('developer.roles.index') }}" class="dv-btn dv-btn-light">→ كل الفئات</a>
            </div>
        </div>
    </header>

    @include('developer.roles._alerts')

    <section class="dv-stats">
        <div class="dv-stat"><div class="dv-stat-icon" style="background: {{ $role->color }}22">👥</div><div><b>{{ $members->count() }}</b><span>عضو في الفئة</span></div></div>
        <div class="dv-stat"><div class="dv-stat-icon" style="background:#dcfce7">🔐</div><div><b>{{ count($granted) }} / {{ $total }}</b><span>صلاحية مفعّلة</span></div></div>
        <div class="dv-stat"><div class="dv-stat-icon" style="background:#fef3c7">{{ $role->is_system ? '🔒' : '✨' }}</div><div><b style="font-size:1rem">{{ $role->is_system ? 'فئة أساسية' : 'فئة مخصصة' }}</b><span>{{ $role->is_system ? 'لا يمكن حذفها' : 'يمكن حذفها' }}</span></div></div>
    </section>

    <div class="dv-two">

        {{-- ========== الأعضاء ========== --}}
        <div>
            <section class="dv-card dv-card-pad" style="margin-bottom: 1.25rem;">
                <div class="dv-section-title" style="margin-bottom:.7rem"><h2>➕ إضافة موظفين للفئة</h2></div>
                <form method="POST" action="{{ route('developer.roles.members.add', $role) }}" id="add-members-form">
                    @csrf
                    <select name="user_ids[]" id="user_ids" multiple placeholder="ابحث واختار موظف أو أكتر...">
                        @foreach($candidates as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} — {{ \App\Models\Role::displayNameFor($c->role) }}</option>
                        @endforeach
                    </select>
                    <div class="dv-hint">الموظف هيتنقل من فئته الحالية للفئة دي (كل موظف ليه فئة واحدة).</div>
                    <button type="submit" class="dv-btn dv-btn-primary" style="margin-top: .9rem; width:100%; justify-content:center" id="btn-add-members">إضافة للفئة</button>
                </form>
            </section>

            <section class="dv-card">
                <div class="dv-section-title" style="padding: 1rem 1.1rem 0; margin-bottom: .6rem;">
                    <h2>👥 الأعضاء ({{ $members->count() }})</h2>
                    @if($members->count() > 6)
                        <input type="text" class="dv-input" placeholder="🔎 بحث..." style="width: 160px; padding: .4rem .7rem; font-size: .82rem;" id="members-filter">
                    @endif
                </div>
                <div class="dv-table-wrap">
                    <table class="dv-table" id="members-table">
                        <tbody>
                        @forelse($members as $m)
                            <tr data-search="{{ mb_strtolower($m->name . ' ' . $m->email) }}">
                                <td>
                                    <div class="dv-user">
                                        <div class="dv-user-av" style="background: {{ $role->color }}">{{ mb_substr($m->name, 0, 1) }}</div>
                                        <div><b>{{ $m->name }} @if($m->id === auth()->id())<span class="dv-chip" style="font-size:.65rem; padding:.1rem .4rem">أنت</span>@endif</b><small>{{ $m->email }}</small></div>
                                    </div>
                                </td>
                                <td style="text-align:left; white-space:nowrap">
                                    @unless($role->isDefault() || $m->id === auth()->id())
                                        <form method="POST" action="{{ route('developer.roles.members.remove', [$role, $m]) }}"
                                              data-confirm="نقل {{ $m->name }} لفئة الموظف العادي؟" style="display:inline">
                                            @csrf
                                            <button class="dv-btn dv-btn-danger dv-btn-sm" type="submit" title="إزالة من الفئة">إزالة</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2"><div class="dv-empty"><div class="i">🫥</div>مفيش موظفين في الفئة دي لسه</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        {{-- ========== ملخص الصلاحيات ========== --}}
        <section>
            <div class="dv-section-title">
                <h2>🔐 الصلاحيات الحالية</h2>
                <a href="{{ route('developer.roles.edit', $role) }}" class="dv-btn dv-btn-ghost dv-btn-sm">✏️ تعديل</a>
            </div>
            <div class="dv-perm-groups" style="grid-template-columns: 1fr;">
                @foreach($groups as $group)
                    @php $on = array_intersect(array_keys($group['permissions']), $granted); @endphp
                    <div class="dv-perm-group {{ count($on) === count($group['permissions']) ? 'is-full' : '' }}">
                        <div class="dv-perm-head">
                            <h3>{{ $group['icon'] }} {{ $group['label'] }}</h3>
                            <span class="dv-perm-count">{{ count($on) }}/{{ count($group['permissions']) }}</span>
                        </div>
                        <div style="padding: .7rem 1rem; display:flex; flex-wrap:wrap; gap:.4rem;">
                            @foreach($group['permissions'] as $key => $label)
                                @if(in_array($key, $granted))
                                    <span class="dv-chip" style="background:#ecfdf5; color:#047857">✓ {{ $label }}</span>
                                @else
                                    <span class="dv-chip" style="background:#f8fafc; color:#cbd5e1; text-decoration: line-through">{{ $label }}</span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.TomSelect && document.getElementById('user_ids')) {
        new TomSelect('#user_ids', {
            plugins: ['remove_button'],
            maxOptions: 500,
            hideSelected: true,
            closeAfterSelect: false,
            render: { no_results: () => '<div class="no-results" style="padding:.6rem">لا يوجد نتائج</div>' }
        });
    }

    document.querySelectorAll('form[data-confirm]').forEach(f => f.addEventListener('submit', e => {
        if (!confirm(f.dataset.confirm)) { e.preventDefault(); e.stopImmediatePropagation(); }
    }));

    document.getElementById('members-filter')?.addEventListener('input', e => {
        const q = e.target.value.trim().toLowerCase();
        document.querySelectorAll('#members-table tr[data-search]').forEach(tr => {
            tr.style.display = !q || tr.dataset.search.includes(q) ? '' : 'none';
        });
    });
});
</script>
@endsection
