@extends('layout')

@section('title', 'أرشيف الموظفين')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            
            {{-- Header & Quick Navigation --}}
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold text-slate-800 mb-1 d-flex align-items-center gap-2" style="color: #1e293b;">
                        <span>🗄️</span>
                        <span>أرشيف الموظفين</span>
                        <span class="badge bg-secondary-subtle text-secondary fs-6 rounded-pill px-3 py-1">{{ $users->total() }}</span>
                    </h2>
                    <p class="text-muted small mb-0">جميع بيانات الموظفين المؤرشفين ووثائقهم وسجلاتهم محفوظة بالكامل ولا تظهر في القوائم النشطة</p>
                </div>
                
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('employee_profiles.index') }}" class="btn btn-primary fw-bold px-3 py-2 d-inline-flex align-items-center gap-2 shadow-xs" style="border-radius: 10px; background: linear-gradient(135deg, #2563eb, #1d4ed8); border: 0;">
                        <span>👥</span>
                        <span>الموظفون النشطون</span>
                        <span class="badge bg-white text-primary rounded-pill px-2 py-0.5" style="font-size: 0.78rem;">{{ \App\Models\User::count() }}</span>
                    </a>
                    <form action="{{ route('employee_profiles.lock_module') }}" method="POST" class="d-inline mb-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary px-3 py-2 fw-semibold" style="border-radius: 10px;" title="إعادة قفل ملفات الموظفين">
                            🔒 قفل
                        </button>
                    </form>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-xs mb-4 border-0 d-flex align-items-center justify-content-between" role="alert" style="background: #dcfce7; color: #166534;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-5">🎉</span>
                        <span class="fw-bold">{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Info Alert --}}
            <div class="alert alert-light border rounded-3 p-3 mb-4 shadow-xs d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                <div class="d-flex align-items-center gap-2 text-slate-700 small">
                    <span class="fs-5">ℹ️</span>
                    <span>الموظفون المؤرشفون <strong>مستبعدون تلقائياً</strong> من قوائم الحوافز، البصمة، الإجازات، والجزاءات. يمكنك استعراض كافة وثائقهم أو استرجاع حساباتهم في أي وقت.</span>
                </div>
                <span class="badge bg-dark bg-opacity-75 text-white px-3 py-2 rounded-pill small">
                    إجمالي المؤرشفين: {{ $users->total() }} موظف
                </span>
            </div>

            {{-- Filters Bar --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; background: #ffffff;">
                <div class="card-body p-3">
                    <form action="{{ route('employee_profiles.archive') }}" method="GET" class="row g-2 align-items-end">
                        @if(request('sort_by'))
                            <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                            <input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}">
                        @endif

                        <div class="col-12 col-md-3">
                            <label class="text-muted small mb-1 d-block fw-semibold">التأمين الاجتماعي:</label>
                            <select name="insured" id="insured" class="form-select form-select-sm" style="border-radius: 8px; height: 38px;" onchange="this.form.submit()">
                                <option value="">جميع الحالات (الكل)</option>
                                <option value="1" {{ request('insured') === '1' ? 'selected' : '' }}>🛡️ مؤمن عليهم فقط</option>
                                <option value="0" {{ request('insured') === '0' ? 'selected' : '' }}>⚠️ غير مؤمن عليهم فقط</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="text-muted small mb-1 d-block fw-semibold">فلترة بالوظيفة:</label>
                            <select name="job_title" id="job_title" class="form-select form-select-sm" style="border-radius: 8px; height: 38px;" onchange="this.form.submit()">
                                <option value="">جميع الوظائف (الكل)</option>
                                @foreach($jobTitles as $jt)
                                    <option value="{{ $jt }}" {{ request('job_title') == $jt ? 'selected' : '' }}>
                                        {{ $jt }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="text-muted small mb-1 d-block fw-semibold">بحث بموظف مؤرشف:</label>
                            <select name="user_id" id="user_id" class="user-search-select" onchange="this.form.submit()">
                                <option value="">كل الموظفين المؤرشفين</option>
                                @foreach($allArchivedUsers as $u)
                                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }} {{ $u->job_title ? '(' . $u->job_title . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-2 d-flex gap-2">
                            <a href="{{ route('employee_profiles.archive') }}" class="btn btn-outline-secondary btn-sm w-100 d-flex align-items-center justify-content-center" style="height: 38px; border-radius: 8px;" title="إعادة ضبط الفلاتر">
                                🔄 إعادة ضبط
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden; background: #ffffff;">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small border-bottom border-light-subtle">
                            <tr>
                                <th class="px-4 py-3" style="min-width: 260px;">
                                    @php
                                        $nextNameDir = (request('sort_by') === 'name' && request('sort_dir') === 'asc') ? 'desc' : 'asc';
                                    @endphp
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_dir' => $nextNameDir]) }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1 fw-bold">
                                        الموظف المؤرشف
                                        @if(request('sort_by') === 'name')
                                            <span style="font-size: 0.8rem; color: #2563eb;">{{ request('sort_dir') === 'desc' ? '▼' : '▲' }}</span>
                                        @else
                                            <span class="text-muted" style="opacity: 0.4;">↕</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-center" style="min-width: 150px;">
                                    @php
                                        $nextJobDir = (request('sort_by') === 'job_title' && request('sort_dir') === 'asc') ? 'desc' : 'asc';
                                    @endphp
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'job_title', 'sort_dir' => $nextJobDir]) }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1 fw-bold">
                                        المسمى الوظيفي
                                        @if(request('sort_by') === 'job_title')
                                            <span style="font-size: 0.8rem; color: #2563eb;">{{ request('sort_dir') === 'desc' ? '▼' : '▲' }}</span>
                                        @else
                                            <span class="text-muted" style="opacity: 0.4;">↕</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 text-center" style="min-width: 140px;">
                                    @php
                                        $nextDateDir = (request('sort_by') === 'deleted_at' && request('sort_dir') === 'desc') ? 'asc' : 'desc';
                                    @endphp
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'deleted_at', 'sort_dir' => $nextDateDir]) }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1 fw-bold">
                                        تاريخ الأرشفة
                                        @if(request('sort_by') === 'deleted_at' || !request('sort_by'))
                                            <span style="font-size: 0.8rem; color: #2563eb;">{{ request('sort_dir') === 'asc' ? '▲' : '▼' }}</span>
                                        @else
                                            <span class="text-muted" style="opacity: 0.4;">↕</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 text-center" style="min-width: 120px;">
                                    التأمين
                                </th>
                                <th class="px-4 py-3 text-center" style="min-width: 180px;">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle overflow-hidden flex-shrink-0 d-flex align-items-center justify-content-center text-white fw-bold shadow-xs border border-2 border-secondary border-opacity-25" 
                                                 style="width: 44px; height: 44px; background: linear-gradient(135deg, #64748b, #475569); font-size: 1.1rem;">
                                                @if($user->profile_picture)
                                                    <img src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                    <span>{{ mb_substr($user->name, 0, 1) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="fw-bold text-slate-800" style="color: #1e293b; font-size: 0.95rem;">
                                                    {{ $user->name }}
                                                </div>
                                                <div class="text-muted extra-small d-flex align-items-center gap-1" style="font-size: 0.8rem;">
                                                    <span>✉️</span>
                                                    <span>{{ $user->email }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($user->job_title)
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2" style="border-radius: 8px;">
                                                {{ $user->job_title }}
                                            </span>
                                        @else
                                            <span class="text-muted italic small">غير محدد</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="fw-semibold text-dark d-block" style="font-size: 0.86rem;">
                                            {{ $user->deleted_at ? $user->deleted_at->format('d M Y') : '—' }}
                                        </span>
                                        <span class="text-muted extra-small" style="font-size: 0.75rem;">
                                            {{ $user->deleted_at ? $user->deleted_at->diffForHumans() : '' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        @if($user->is_insured)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill" style="font-size: 0.78rem;">
                                                🛡️ مؤمن
                                            </span>
                                        @else
                                            <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill" style="font-size: 0.78rem; border-style: dashed !important;">
                                                غير مؤمن
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="d-inline-flex align-items-center gap-1.5">
                                            <a href="{{ route('employee_profiles.show', $user->id) }}" class="btn btn-sm btn-outline-info px-3 d-inline-flex align-items-center gap-1 shadow-xs" style="border-radius: 8px;" title="استعراض كافة وثائق وملفات الموظف المحفوظة">
                                                <span>👁️</span>
                                                <span>عرض الملف</span>
                                            </a>
                                            @can('employees.edit_profiles')
                                            <form action="{{ route('employee_profiles.restore', $user->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('هل تريد استرجاع حساب الموظف «{{ addslashes($user->name) }}» وإعادته إلى قائمة الموظفين النشطين؟')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success px-3 d-inline-flex align-items-center gap-1 shadow-xs" style="border-radius: 8px;" title="استرجاع الموظف من الأرشيف">
                                                    <span>♻️</span>
                                                    <span>استرجاع</span>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <div class="mb-2 fs-2">🗄️</div>
                                        <h6 class="fw-bold text-slate-700 mb-1">لا يوجد موظفون في الأرشيف</h6>
                                        <p class="small text-muted mb-0">جميع الموظفين يعملون في القائمة النشطة حالياً.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($users->hasPages())
                    <div class="card-footer bg-white border-top-0 py-3">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.querySelector('.user-search-select')) {
            new TomSelect(".user-search-select", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: "ابحث بالاسم...",
                allowEmptyOption: true,
            });
        }
    });
</script>
@endsection
