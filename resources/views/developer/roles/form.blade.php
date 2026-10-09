@extends('layout')
@section('title', $role->exists ? 'تعديل فئة: ' . $role->display_name : 'إنشاء فئة جديدة')

@section('content')
@include('developer.roles._styles')

@php
    $isDev = $role->isDeveloper();
    $granted = $isDev ? \App\Support\Permissions::all() : old('permissions', (array) $role->permissions);
    $total = count(\App\Support\Permissions::all());
    $templates = $roles->mapWithKeys(fn ($r) => [$r->name => $r->isDeveloper() ? \App\Support\Permissions::all() : (array) $r->permissions]);
@endphp

<div class="dv-wrap">

    <header class="dv-hero">
        <div class="dv-crumbs">
            <a href="{{ route('developer.roles.index') }}">إدارة الفئات</a> /
            @if($role->exists)<a href="{{ route('developer.roles.show', $role) }}">{{ $role->display_name }}</a> / تعديل@else فئة جديدة@endif
        </div>
        <div class="dv-hero-row">
            <div>
                <span class="dv-badge-dev">🛡️ منطقة المطور فقط</span>
                <h1>{{ $role->exists ? 'تعديل فئة «' . $role->display_name . '»' : 'إنشاء فئة جديدة' }}</h1>
                <p>{{ $isDev ? 'فئة المطور معاها كل الصلاحيات دايماً — تقدر تعدل الاسم والوصف واللون بس.' : 'حدد البيانات الأساسية وبعدين فعّل الصلاحيات اللي الفئة دي تقدر تعملها.' }}</p>
            </div>
            <a href="{{ $role->exists ? route('developer.roles.show', $role) : route('developer.roles.index') }}" class="dv-btn dv-btn-light">→ رجوع</a>
        </div>
    </header>

    @include('developer.roles._alerts')

    <form method="POST" action="{{ $role->exists ? route('developer.roles.update', $role) : route('developer.roles.store') }}" id="role-form">
        @csrf

        {{-- ========== البيانات الأساسية ========== --}}
        <section class="dv-card dv-card-pad" style="margin-bottom: 1.5rem;">
            <div class="dv-form-grid">
                <div>
                    <label class="dv-label" for="display_name">اسم الفئة *</label>
                    <input type="text" id="display_name" name="display_name" class="dv-input" required maxlength="100"
                           value="{{ old('display_name', $role->display_name) }}" placeholder="مثال: مشرف الاستقبال">

                    <label class="dv-label" for="description" style="margin-top: 1rem;">الوصف</label>
                    <input type="text" id="description" name="description" class="dv-input" maxlength="255"
                           value="{{ old('description', $role->description) }}" placeholder="وصف مختصر لدور الفئة دي">

                    @if(!$role->exists)
                        <label class="dv-label" for="name" style="margin-top: 1rem;">المعرّف البرمجي (اختياري)</label>
                        <input type="text" id="name" name="name" class="dv-input" maxlength="50" dir="ltr" style="text-align:left"
                               value="{{ old('name') }}" placeholder="reception_supervisor">
                        <div class="dv-hint">لو سبته فاضي هيتعمل تلقائي من الاسم. حروف إنجليزي وأرقام و _ بس.</div>
                    @else
                        <div class="dv-hint" style="margin-top: .8rem;">المعرّف البرمجي: <code dir="ltr">{{ $role->name }}</code> (ثابت)</div>
                    @endif
                </div>

                <div>
                    <label class="dv-label">لون الفئة</label>
                    <div class="dv-colors">
                        @php $selColor = old('color', $role->color); @endphp
                        @foreach(array_unique(array_merge($colors, [$selColor])) as $c)
                            <label class="dv-color" style="color: {{ $c }}" title="{{ $c }}">
                                <input type="radio" name="color" value="{{ $c }}" @checked($selColor === $c)>
                                <span style="background: {{ $c }}"></span>
                            </label>
                        @endforeach
                    </div>

                    <div style="margin-top: 1.25rem; padding: 1rem; border-radius: 14px; background: #f8fafc; border: 1px dashed #e2e8f0;">
                        <div class="dv-label" style="margin-bottom:.6rem">معاينة</div>
                        <span class="dv-role-pill" id="preview-pill" style="--c: {{ $selColor }}; font-size: .9rem; padding: .4rem .9rem;">
                            <span id="preview-name">{{ old('display_name', $role->display_name) ?: 'اسم الفئة' }}</span>
                        </span>
                    </div>

                    @if(!$isDev)
                        <label class="dv-label" for="template" style="margin-top: 1.25rem;">📋 انسخ الصلاحيات من فئة موجودة</label>
                        <select id="template" class="dv-select" style="width:100%">
                            <option value="">— اختر فئة كقالب —</option>
                            @foreach($roles as $r)
                                @continue($role->exists && $r->id === $role->id)
                                <option value="{{ $r->name }}">{{ $r->display_name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>
        </section>

        {{-- ========== الصلاحيات ========== --}}
        <div class="dv-section-title">
            <h2>🔐 الصلاحيات</h2>
            @unless($isDev)
                <div class="dv-tools">
                    <input type="text" id="perm-filter" class="dv-input" placeholder="🔎 فلترة الصلاحيات..." style="width: 220px; padding: .45rem .8rem; font-size: .84rem;">
                    <button type="button" class="dv-btn dv-btn-ghost dv-btn-sm" id="perm-all">تحديد الكل</button>
                    <button type="button" class="dv-btn dv-btn-ghost dv-btn-sm" id="perm-none">إلغاء الكل</button>
                </div>
            @endunless
        </div>

        @if($isDev)
            <div class="dv-alert dv-alert-warn">🔒 فئة المطور معاها كل صلاحيات النظام + إدارة الفئات، ومش ممكن تقليلها.</div>
        @endif

        <section class="dv-perm-groups">
            @foreach($groups as $gKey => $group)
                <div class="dv-perm-group" data-group="{{ $gKey }}">
                    <div class="dv-perm-head">
                        <h3>{{ $group['icon'] }} {{ $group['label'] }} <span class="dv-perm-count" data-count></span></h3>
                        @unless($isDev)
                            <button type="button" class="dv-perm-all" data-toggle-group>تحديد الكل</button>
                        @endunless
                    </div>
                    @foreach($group['permissions'] as $key => $label)
                        <label class="dv-perm-item" data-perm-text="{{ $label }} {{ $key }}">
                            <div>
                                <div class="t">{{ $label }}</div>
                                <code>{{ $key }}</code>
                            </div>
                            <span class="dv-switch">
                                <input type="checkbox" name="permissions[]" value="{{ $key }}" id="perm-{{ str_replace('.', '-', $key) }}"
                                       @checked(in_array($key, $granted)) @disabled($isDev)>
                                <span></span>
                            </span>
                        </label>
                    @endforeach
                </div>
            @endforeach
        </section>

        <div class="dv-sticky-bar">
            <div class="sum">الصلاحيات المفعّلة: <b id="sum-count">0</b> / {{ $total }}</div>
            <div style="display:flex; gap:.5rem">
                <a href="{{ $role->exists ? route('developer.roles.show', $role) : route('developer.roles.index') }}" class="dv-btn dv-btn-light">إلغاء</a>
                <button type="submit" class="dv-btn dv-btn-primary" id="btn-save-role">💾 {{ $role->exists ? 'حفظ التعديلات' : 'إنشاء الفئة' }}</button>
            </div>
        </div>
    </form>
</div>

<script>
(function () {
    const boxes = () => Array.from(document.querySelectorAll('input[name="permissions[]"]'));
    const templates = @json($templates);

    function refresh() {
        document.querySelectorAll('.dv-perm-group').forEach(g => {
            const all = g.querySelectorAll('input[type=checkbox]');
            const on = g.querySelectorAll('input[type=checkbox]:checked');
            g.querySelector('[data-count]').textContent = on.length + '/' + all.length;
            g.classList.toggle('is-full', on.length === all.length && all.length > 0);
            const btn = g.querySelector('[data-toggle-group]');
            if (btn) btn.textContent = on.length === all.length ? 'إلغاء الكل' : 'تحديد الكل';
        });
        document.getElementById('sum-count').textContent = boxes().filter(b => b.checked).length;
    }

    document.addEventListener('change', e => { if (e.target.matches('input[name="permissions[]"]')) refresh(); });

    document.querySelectorAll('[data-toggle-group]').forEach(btn => btn.addEventListener('click', () => {
        const g = btn.closest('.dv-perm-group');
        const all = g.querySelectorAll('input[type=checkbox]');
        const target = g.querySelectorAll('input[type=checkbox]:checked').length !== all.length;
        all.forEach(b => b.checked = target);
        refresh();
    }));

    const setAll = v => { boxes().forEach(b => { if (!b.disabled) b.checked = v; }); refresh(); };
    document.getElementById('perm-all')?.addEventListener('click', () => setAll(true));
    document.getElementById('perm-none')?.addEventListener('click', () => setAll(false));

    document.getElementById('template')?.addEventListener('change', e => {
        const list = templates[e.target.value];
        if (!list) return;
        boxes().forEach(b => b.checked = list.includes(b.value));
        refresh();
    });

    document.getElementById('perm-filter')?.addEventListener('input', e => {
        const q = e.target.value.trim().toLowerCase();
        document.querySelectorAll('.dv-perm-group').forEach(g => {
            let visible = 0;
            g.querySelectorAll('.dv-perm-item').forEach(it => {
                const show = !q || it.dataset.permText.toLowerCase().includes(q);
                it.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            g.style.display = visible ? '' : 'none';
        });
    });

    // معاينة الاسم واللون
    const nameInput = document.getElementById('display_name');
    const pill = document.getElementById('preview-pill');
    nameInput.addEventListener('input', () => document.getElementById('preview-name').textContent = nameInput.value || 'اسم الفئة');
    document.querySelectorAll('input[name=color]').forEach(r => r.addEventListener('change', () => pill.style.setProperty('--c', r.value)));

    refresh();
})();
</script>
@endsection
