@extends('layout')
@section('title', 'إدارة الفئات والصلاحيات')

@section('content')
@include('developer.roles._styles')

<div class="dv-wrap">

    <header class="dv-hero">
        <div class="dv-hero-row">
            <div>
                <span class="dv-badge-dev">🛡️ منطقة المطور فقط</span>
                <h1>إدارة الفئات والصلاحيات</h1>
                <p>أنشئ فئات جديدة، حدد صلاحيات كل فئة بالتفصيل، ووزّع الموظفين عليها — التغييرات بتشتغل فوراً.</p>
            </div>
            <a href="{{ route('developer.roles.create') }}" class="dv-btn dv-btn-light" id="btn-create-role">➕ فئة جديدة</a>
        </div>
    </header>

    @include('developer.roles._alerts')

    @if($orphanCount > 0)
        <div class="dv-alert dv-alert-warn">⚠️ يوجد {{ $orphanCount }} موظف مسجل على فئة غير موجودة — مش هيكون معاهم أي صلاحيات لحد ما تنقلهم لفئة.</div>
    @endif

    <section class="dv-stats">
        <div class="dv-stat"><div class="dv-stat-icon" style="background:#ede9fe">🗂️</div><div><b>{{ $roles->count() }}</b><span>فئة</span></div></div>
        <div class="dv-stat"><div class="dv-stat-icon" style="background:#e0f2fe">👥</div><div><b>{{ $roles->sum('users_count') }}</b><span>موظف</span></div></div>
        <div class="dv-stat"><div class="dv-stat-icon" style="background:#dcfce7">🔐</div><div><b>{{ $totalPermissions }}</b><span>صلاحية متاحة</span></div></div>
        <div class="dv-stat"><div class="dv-stat-icon" style="background:#fef3c7">✨</div><div><b>{{ $roles->where('is_system', false)->count() }}</b><span>فئة مخصصة</span></div></div>
    </section>

    {{-- ========== كلمة مرور النظام ========== --}}
    <div class="dv-section-title mt-5">
        <h2>🔒 كلمة مرور التعديلات</h2>
        <small>كلمة المرور المطلوبة عند الدخول لتعديل ملفات الموظفين لزيادة الأمان.</small>
    </div>

    <div class="card mb-5 border-0 shadow-sm" style="border-radius: 12px; background: #fff; padding: 20px;">
        <form action="{{ route('developer.system_password.update') }}" method="POST" class="d-flex align-items-end gap-3 flex-wrap">
            @csrf
            <div style="flex: 1; min-width: 250px;">
                <label class="form-label fw-bold">كلمة مرور النظام الخاصة بالتعديل</label>
                <input type="text" name="system_password" class="form-control form-control-lg" required minlength="4" value="{{ \App\Support\Settings::get('system_edit_password', '123456') }}" style="border-radius: 8px; border: 1px solid #cbd5e1;">
            </div>
            <div>
                <button type="submit" class="dv-btn" style="background: #1e293b; color: white;">💾 تحديث كلمة المرور</button>
            </div>
        </form>
    </div>
    {{-- ========== الفئات ========== --}}
    <div class="dv-section-title">
        <h2>🗂️ الفئات</h2>
        <small>اضغط على أي فئة لإدارة أعضائها</small>
    </div>

    <section class="dv-roles">
        @foreach($roles as $role)
            @php
                $count = $role->isDeveloper() ? $totalPermissions : count((array) $role->permissions);
                $pct = $totalPermissions ? round($count / $totalPermissions * 100) : 0;
            @endphp
            <article class="dv-role" style="--c: {{ $role->color }}" id="role-card-{{ $role->name }}">
                <div class="dv-role-head">
                    <div class="dv-role-avatar">{{ mb_substr($role->display_name, 0, 1) }}</div>
                    <div style="min-width:0">
                        <h3 class="dv-role-name">{{ $role->display_name }}</h3>
                        <span class="dv-role-slug">{{ $role->name }}</span>
                    </div>
                </div>
                <p class="dv-role-desc">{{ $role->description ?: 'بدون وصف' }}</p>
                <div class="dv-role-meta">
                    <span class="dv-chip dv-chip-c">👥 {{ $role->users_count }} موظف</span>
                    <span class="dv-chip">🔐 {{ $role->isDeveloper() ? 'كل الصلاحيات' : $count . ' / ' . $totalPermissions }}</span>
                    @if($role->is_system)<span class="dv-chip dv-chip-lock">🔒 أساسية</span>@endif
                    @if($role->isDefault())<span class="dv-chip">⭐ افتراضية</span>@endif
                </div>
                <div class="dv-progress"><i style="width: {{ $pct }}%"></i></div>
                <div class="dv-role-actions">
                    <a href="{{ route('developer.roles.show', $role) }}" class="dv-btn dv-btn-ghost dv-btn-sm">👥 الأعضاء</a>
                    <a href="{{ route('developer.roles.edit', $role) }}" class="dv-btn dv-btn-ghost dv-btn-sm">
                        {{ $role->isDeveloper() ? '✏️ تعديل البيانات' : '🔐 الصلاحيات' }}
                    </a>
                    @if($role->canBeDeleted())
                        <form action="{{ route('developer.roles.destroy', $role) }}" method="POST"
                              data-confirm="حذف فئة «{{ $role->display_name }}»؟&#10;{{ $role->users_count }} موظف هيتنقلوا لفئة الموظف العادي.">
                            @csrf
                            <button class="dv-btn dv-btn-danger dv-btn-sm" type="submit">🗑️</button>
                        </form>
                    @endif
                </div>
            </article>
        @endforeach

        <a href="{{ route('developer.roles.create') }}" class="dv-role dv-role-new">
            <span class="plus">+</span>
            إنشاء فئة جديدة
        </a>
    </section>

    {{-- ========== الموظفين ========== --}}
    <div class="dv-section-title">
        <h2>👥 توزيع الموظفين على الفئات</h2>
        <small>غيّر فئة أي موظف مباشرة من القائمة</small>
    </div>

    <section class="dv-card">
        <form method="GET" action="{{ route('developer.roles.index') }}" class="dv-filters">
            <div class="dv-search">
                <input type="text" name="q" value="{{ request('q') }}" class="dv-input" placeholder="ابحث بالاسم أو الإيميل..." id="users-search">
            </div>
            <select name="role" class="dv-select" onchange="this.form.submit()" id="users-role-filter">
                <option value="">كل الفئات</option>
                @foreach($roles as $r)
                    <option value="{{ $r->name }}" @selected(request('role') === $r->name)>{{ $r->display_name }} ({{ $r->users_count }})</option>
                @endforeach
            </select>
            <button class="dv-btn dv-btn-primary dv-btn-sm" type="submit">بحث</button>
            @if(request('q') || request('role'))
                <a href="{{ route('developer.roles.index') }}" class="dv-btn dv-btn-ghost dv-btn-sm">✖ مسح</a>
            @endif
        </form>

        <div class="dv-table-wrap">
            <table class="dv-table">
                <thead>
                    <tr>
                        <th>الموظف</th>
                        <th>المسمى الوظيفي</th>
                        <th>الفئة الحالية</th>
                        <th>تغيير الفئة</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $u)
                    @php $ur = $roles->firstWhere('name', $u->role); @endphp
                    <tr>
                        <td>
                            <div class="dv-user">
                                <div class="dv-user-av" style="background: {{ $ur->color ?? '#94a3b8' }}">{{ mb_substr($u->name, 0, 1) }}</div>
                                <div><b>{{ $u->name }}</b><small>{{ $u->email }}</small></div>
                            </div>
                        </td>
                        <td style="color:#64748b">{{ $u->job_title ?: '—' }}</td>
                        <td>
                            <span class="dv-role-pill" style="--c: {{ $ur->color ?? '#94a3b8' }}">{{ $ur->display_name ?? $u->role . ' (غير موجودة)' }}</span>
                        </td>
                        <td>
                            <form action="{{ route('developer.users.role', $u) }}" method="POST">
                                @csrf
                                <select name="role" class="dv-select js-role-change" id="user-role-{{ $u->id }}"
                                        data-name="{{ $u->name }}" data-current="{{ $u->role }}"
                                        @disabled($u->id === auth()->id())>
                                    @foreach($roles as $r)
                                        <option value="{{ $r->name }}" @selected($u->role === $r->name)>{{ $r->display_name }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="dv-empty"><div class="i">🔍</div>لا يوجد نتائج</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div style="padding: 1rem 1.1rem; border-top: 1px solid #eef2f7;">{{ $users->links() }}</div>
        @endif
    </section>
</div>

<script>
    document.querySelectorAll('form[data-confirm]').forEach(f => f.addEventListener('submit', e => {
        if (!confirm(f.dataset.confirm)) { e.preventDefault(); e.stopImmediatePropagation(); }
    }));
    document.querySelectorAll('.js-role-change').forEach(s => s.addEventListener('change', () => {
        const label = s.options[s.selectedIndex].text;
        if (confirm('تغيير فئة ' + s.dataset.name + ' إلى: ' + label + ' ؟')) s.form.submit();
        else s.value = s.dataset.current;
    }));
</script>
@endsection
