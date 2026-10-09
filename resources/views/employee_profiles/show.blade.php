@extends('layout')

@section('title', 'بروفايل الموظف - ' . $user->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">

            @if(in_array(Auth::user()->role, ['super_admin', 'developer']) || Auth::user()->role === 'admin')
                <div class="mb-3">
                    <a href="{{ route('employee_profiles.index') }}" class="btn btn-light btn-sm rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 fw-semibold border shadow-xs" style="background: #ffffff; color: #1e293b; border-color: #cbd5e1 !important;">
                        <span>⬅️</span>
                        <span>العودة لصفحة الموظفين</span>
                    </a>
                </div>
            @endif

            {{-- 1. Employee Header & Annual Leave Balance Banner --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff;">
                <div class="card-body p-4">
                    <div class="row align-items-center g-4">
                        <div class="col-12 col-md-6 d-flex align-items-center gap-3">
                            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-25 rounded-circle p-3 text-primary border border-primary border-opacity-25" style="width: 75px; height: 75px; flex-shrink: 0;">
                                <span style="font-size: 2.5rem;">👤</span>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-1 text-white">{{ $user->name }}</h3>
                                <div class="d-flex flex-wrap align-items-center gap-2 text-slate-300 small">
                                    <span>🏢 {{ $user->job_title ?? 'موظف' }}</span>
                                    <span>•</span>
                                    <span>✉️ {{ $user->email }}</span>
                                    @if(in_array(Auth::user()->role, ['super_admin', 'developer']) || Auth::user()->role === 'admin' || Auth::id() === $user->id)
                                        <span>•</span>
                                        <span class="badge bg-info text-dark">⏱️ {{ number_format($user->hourly_rate, 2) }} ج.م/ساعة</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Annual Leave Balance Card --}}
                        <div class="col-12 col-md-6">
                            <div class="p-3 bg-white bg-opacity-10 rounded-3 border border-white border-opacity-10">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-semibold text-white">🌴 رصيد الإجازات السنوية المتبقي</span>
                                    <span class="badge bg-success text-white px-2 py-1 fs-6 fw-bold">{{ $remainingAnnualLeaves }} يوم متبقي</span>
                                </div>
                                @php
                                    $usedPercent = min(100, round(($yearUsedLeavesDays / $annualQuota) * 100));
                                    $progressBg = $usedPercent > 80 ? 'bg-danger' : ($usedPercent > 50 ? 'bg-warning' : 'bg-success');
                                @endphp
                                <div class="progress bg-dark bg-opacity-50" style="height: 10px; border-radius: 5px;">
                                    <div class="progress-bar {{ $progressBg }}" role="progressbar" style="width: {{ $usedPercent }}%;" aria-valuenow="{{ $usedPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 text-slate-300 extra-small" style="font-size: 0.8rem;">
                                    <span>المُستهلك: <strong>{{ $yearUsedLeavesDays }}</strong> يوم (عام {{ now()->year }})</span>
                                    <span>الإجمالي السنوي: <strong>{{ $annualQuota }}</strong> يوم</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Calendar / Month Navigation Control --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; background: #ffffff;">
                <div class="card-body p-3">
                    <div class="row align-items-center g-3">

                        {{-- Month Stepper --}}
                        <div class="col-12 col-md-7 d-flex align-items-center justify-content-between justify-content-md-start gap-2">
                            @php
                                $profileRoute = request()->routeIs('employee_profiles.show') ? route('employee_profiles.show', $user->id) : route('my_profile');
                            @endphp
                            <a href="{{ $profileRoute }}?year={{ $prevYear }}&month={{ $prevMonth }}" class="btn btn-outline-secondary btn-sm px-3 rounded-pill d-flex align-items-center gap-1">
                                <span>◀</span>
                                <span class="d-none d-sm-inline">الشهر السابق</span>
                            </a>

                            <div class="text-center px-3 py-1 bg-light rounded-pill border">
                                <span class="fs-6 fw-bold text-dark">📅 {{ $selectedMonthLabel }}</span>
                            </div>

                            <a href="{{ $profileRoute }}?year={{ $nextYear }}&month={{ $nextMonth }}" class="btn btn-outline-secondary btn-sm px-3 rounded-pill d-flex align-items-center gap-1">
                                <span class="d-none d-sm-inline">الشهر التالي</span>
                                <span>▶</span>
                            </a>
                        </div>

                        {{-- Quick Month Dropdown Picker --}}
                        <div class="col-12 col-md-5">
                            <form method="GET" action="{{ $profileRoute }}" class="d-flex align-items-center gap-2">
                                <select name="month" class="form-select form-select-sm rounded-3">
                                    @foreach($arabicMonths as $mNum => $mName)
                                        <option value="{{ $mNum }}" {{ $mNum == $month ? 'selected' : '' }}>{{ $mName }}</option>
                                    @endforeach
                                </select>
                                <select name="year" class="form-select form-select-sm rounded-3">
                                    @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3 fw-bold text-nowrap">عرض</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

            {{-- 3. Monthly Summary Cards --}}
            <div class="row g-3 mb-4">
                <!-- Leaves Stat -->
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3 h-100" style="border-radius: 16px; background: #eff6ff;">
                        <div class="fs-2 mb-1">🌴</div>
                        <span class="text-muted small fw-semibold">الإجازات</span>
                        <div class="fs-4 fw-bold text-primary">{{ $monthAcceptedLeavesDays }} <small class="fs-6">يوم</small></div>
                        <small class="text-muted" style="font-size: 0.75rem;">المقبولة هذا الشهر</small>
                    </div>
                </div>

                <!-- Overtime Stat -->
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3 h-100" style="border-radius: 16px; background: #f0fdf4;">
                        <div class="fs-2 mb-1">⏱️</div>
                        <span class="text-muted small fw-semibold">الساعات الإضافية</span>
                        <div class="fs-4 fw-bold text-success">{{ round($monthAcceptedOvertimeHours, 2) }} <small class="fs-6">ساعة</small></div>
                        <small class="text-success fw-semibold" style="font-size: 0.75rem;">{{ number_format($monthOvertimeValue, 2) }} ج.م</small>
                    </div>
                </div>

                <!-- Permissions Stat -->
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3 h-100" style="border-radius: 16px; background: #fefce8;">
                        <div class="fs-2 mb-1">🚪</div>
                        <span class="text-muted small fw-semibold">الأذونات</span>
                        <div class="fs-4 fw-bold text-warning-emphasis">{{ $monthPermissionHours }} <small class="fs-6">ساعة</small></div>
                        <small class="text-muted" style="font-size: 0.75rem;">({{ $monthPermissionMinutes }} دقيقة)</small>
                    </div>
                </div>

                <!-- Penalties Stat -->
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3 h-100" style="border-radius: 16px; background: #fef2f2;">
                        <div class="fs-2 mb-1">⚠️</div>
                        <span class="text-muted small fw-semibold">الجزاءات</span>
                        <div class="fs-4 fw-bold text-danger">{{ $monthAcceptedPenaltiesCount }} <small class="fs-6">جزاء</small></div>
                        <small class="text-danger fw-semibold" style="font-size: 0.75rem;">إجمالي الخصم: {{ number_format($monthPenaltiesAmount, 2) }} ج.م</small>
                    </div>
                </div>
            </div>

            {{-- 4. Detailed Breakdown Tabs --}}
            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 20px;">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                    <ul class="nav nav-tabs card-header-tabs border-bottom-0 gap-2" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold px-3 py-2 rounded-top-3" id="leaves-tab" data-bs-toggle="tab" data-bs-target="#leaves-pane" type="button" role="tab">
                                🌴 الإجازات ({{ $leaves->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold px-3 py-2 rounded-top-3" id="overtime-tab" data-bs-toggle="tab" data-bs-target="#overtime-pane" type="button" role="tab">
                                ⏱️ الإضافي ({{ $overtimes->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold px-3 py-2 rounded-top-3" id="permissions-tab" data-bs-toggle="tab" data-bs-target="#permissions-pane" type="button" role="tab">
                                📋 الأذونات ({{ $permissions->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold px-3 py-2 rounded-top-3" id="penalties-tab" data-bs-toggle="tab" data-bs-target="#penalties-pane" type="button" role="tab">
                                ⚠️ الجزاءات ({{ $penalties->count() }})
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4 bg-white">
                    <div class="tab-content" id="profileTabsContent">

                        {{-- Tab 1: Leaves --}}
                        <div class="tab-pane fade show active" id="leaves-pane" role="tabpanel">
                            @if($leaves->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="py-3 px-3 border-0">التاريخ واليوم</th>
                                                <th class="py-3 px-3 border-0">عدد الأيام</th>
                                                <th class="py-3 px-3 border-0">السبب / الملاحظات</th>
                                                <th class="py-3 px-3 border-0">البديل</th>
                                                <th class="py-3 px-3 border-0 text-center">الحالة</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($leaves as $item)
                                                <tr>
                                                    <td class="py-3 px-3">
                                                        <div class="fw-bold text-dark">{{ $item->date }}</div>
                                                        <div class="small text-muted">{{ $item->day }}</div>
                                                    </td>
                                                    <td class="py-3 px-3">
                                                        <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1 fs-6">{{ $item->days_count }} يوم</span>
                                                    </td>
                                                    <td class="py-3 px-3 text-muted small">{{ $item->reason ?? '—' }}</td>
                                                    <td class="py-3 px-3 text-muted small">{{ $item->substitute ?? '—' }}</td>
                                                    <td class="py-3 px-3 text-center">
                                                        @if($item->status === 'accepted')
                                                            <span class="badge bg-success text-white px-3 py-1">مقبول</span>
                                                        @elseif($item->status === 'refused')
                                                            <span class="badge bg-danger text-white px-3 py-1">مرفوض</span>
                                                        @else
                                                            <span class="badge bg-warning text-dark px-3 py-1">قيد الانتظار</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <span class="fs-1 d-block mb-2 text-muted">🌴</span>
                                    <p class="text-muted mb-0">لا توجد إجازات مسجلة في {{ $selectedMonthLabel }}.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Tab 2: Overtime --}}
                        <div class="tab-pane fade" id="overtime-pane" role="tabpanel">
                            @if($overtimes->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="py-3 px-3 border-0">التاريخ واليوم</th>
                                                <th class="py-3 px-3 border-0">التوقيت (من - إلى)</th>
                                                <th class="py-3 px-3 border-0">عدد الساعات</th>
                                                <th class="py-3 px-3 border-0">القيمة المستحقة</th>
                                                <th class="py-3 px-3 border-0">السبب</th>
                                                <th class="py-3 px-3 border-0 text-center">الحالة</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($overtimes as $item)
                                                @php
                                                    $val = ($user->hourly_rate > 0) ? ($item->total_hours * 1.5 * $user->hourly_rate) : 0;
                                                @endphp
                                                <tr>
                                                    <td class="py-3 px-3">
                                                        <div class="fw-bold text-dark">{{ $item->date }}</div>
                                                        <div class="small text-muted">{{ $item->day }}</div>
                                                    </td>
                                                    <td class="py-3 px-3 text-muted small">
                                                        @if($item->from && $item->to)
                                                            {{ $item->from }} ⬅ {{ $item->to }}
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td class="py-3 px-3">
                                                        <span class="badge bg-success-subtle text-success fw-bold px-2 py-1 fs-6">{{ round($item->total_hours, 2) }} ساعة</span>
                                                    </td>
                                                    <td class="py-3 px-3 fw-bold text-success">
                                                        {{ number_format($val, 2) }} <small class="text-muted font-normal">ج.م</small>
                                                    </td>
                                                    <td class="py-3 px-3 text-muted small">{{ $item->reason ?? '—' }}</td>
                                                    <td class="py-3 px-3 text-center">
                                                        @if($item->status === 'accepted')
                                                            <span class="badge bg-success text-white px-3 py-1">مقبول</span>
                                                        @elseif($item->status === 'refused')
                                                            <span class="badge bg-danger text-white px-3 py-1">مرفوض</span>
                                                        @else
                                                            <span class="badge bg-warning text-dark px-3 py-1">قيد الانتظار</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <span class="fs-1 d-block mb-2 text-muted">⏱️</span>
                                    <p class="text-muted mb-0">لا توجد ساعات إضافية مسجلة في {{ $selectedMonthLabel }}.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Tab 3: Permissions --}}
                        <div class="tab-pane fade" id="permissions-pane" role="tabpanel">
                            @if($permissions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="py-3 px-3 border-0">التاريخ واليوم</th>
                                                <th class="py-3 px-3 border-0">نوع الإذن</th>
                                                <th class="py-3 px-3 border-0">الوقت (من - إلى)</th>
                                                <th class="py-3 px-3 border-0">المدة</th>
                                                <th class="py-3 px-3 border-0">السبب</th>
                                                <th class="py-3 px-3 border-0 text-center">الحالة</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($permissions as $item)
                                                @php
                                                    $mins = 0;
                                                    if ($item->from && $item->to) {
                                                        $from = \Carbon\Carbon::parse($item->from);
                                                        $to   = \Carbon\Carbon::parse($item->to);
                                                        $mins = $from->diffInMinutes($to);
                                                    }
                                                @endphp
                                                <tr>
                                                    <td class="py-3 px-3">
                                                        <div class="fw-bold text-dark">{{ $item->date }}</div>
                                                        <div class="small text-muted">{{ $item->day }}</div>
                                                    </td>
                                                    <td class="py-3 px-3 text-muted small fw-semibold">{{ $item->permission_type ?? 'إذن عام' }}</td>
                                                    <td class="py-3 px-3 text-muted small">
                                                        @if($item->from && $item->to)
                                                            {{ $item->from }} ⬅ {{ $item->to }}
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td class="py-3 px-3">
                                                        <span class="badge bg-warning-subtle text-warning-emphasis fw-bold px-2 py-1">{{ $mins }} دقيقة</span>
                                                    </td>
                                                    <td class="py-3 px-3 text-muted small">{{ $item->reason ?? '—' }}</td>
                                                    <td class="py-3 px-3 text-center">
                                                        @if($item->status === 'accepted')
                                                            <span class="badge bg-success text-white px-3 py-1">مقبول</span>
                                                        @elseif($item->status === 'refused')
                                                            <span class="badge bg-danger text-white px-3 py-1">مرفوض</span>
                                                        @else
                                                            <span class="badge bg-warning text-dark px-3 py-1">قيد الانتظار</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <span class="fs-1 d-block mb-2 text-muted">📋</span>
                                    <p class="text-muted mb-0">لا توجد أذونات مسجلة في {{ $selectedMonthLabel }}.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Tab 4: Penalties --}}
                        <div class="tab-pane fade" id="penalties-pane" role="tabpanel">
                            @if($penalties->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="py-3 px-3 border-0">التاريخ والوقت</th>
                                                <th class="py-3 px-3 border-0">اسم / مسمى الجزاء</th>
                                                <th class="py-3 px-3 border-0">الخصم / المبلغ</th>
                                                <th class="py-3 px-3 border-0">السبب / الملاحظات</th>
                                                <th class="py-3 px-3 border-0 text-center">الحالة</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($penalties as $item)
                                                <tr>
                                                    <td class="py-3 px-3">
                                                        <div class="fw-bold text-dark">{{ $item->created_at->format('Y-m-d') }}</div>
                                                        <div class="small text-muted">{{ $item->created_at->format('h:i A') }}</div>
                                                    </td>
                                                    <td class="py-3 px-3 fw-bold text-danger">{{ $item->name ?? 'جزاء مالي' }}</td>
                                                    <td class="py-3 px-3 fw-bold text-danger">
                                                        @if(is_numeric($item->amount))
                                                            {{ number_format((float) $item->amount, 2) }} <small class="text-muted font-normal">ج.م</small>
                                                        @else
                                                            {{ $item->amount }}
                                                        @endif
                                                    </td>
                                                    <td class="py-3 px-3 text-muted small">
                                                        {{ $item->reason ?? $item->notes ?? '—' }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center">
                                                        @if($item->status === 'accepted')
                                                            <span class="badge bg-danger text-white px-3 py-1">مُطبق</span>
                                                        @elseif($item->status === 'refused')
                                                            <span class="badge bg-secondary text-white px-3 py-1">ملغي / مرفوض</span>
                                                        @else
                                                            <span class="badge bg-warning text-dark px-3 py-1">قيد المراجعة</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <span class="fs-1 d-block mb-2 text-muted">🎉</span>
                                    <p class="text-muted mb-0">لا توجد جزاءات مسجلة في {{ $selectedMonthLabel }}.</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .bg-primary-subtle { background-color: #dbeafe !important; }
    .bg-success-subtle { background-color: #dcfce7 !important; }
    .bg-warning-subtle { background-color: #fef9c3 !important; }
    .text-warning-emphasis { color: #854d0e !important; }
    .nav-tabs .nav-link {
        color: #64748b;
        border: 1px solid transparent;
        background: #f8fafc;
        cursor: pointer;
    }
    .nav-tabs .nav-link:hover {
        color: #2563eb;
        background: #eff6ff;
    }
    .nav-tabs .nav-link.active {
        color: #2563eb;
        background: #ffffff;
        border-color: #e2e8f0 #e2e8f0 #ffffff #e2e8f0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('#profileTabs button[data-bs-toggle="tab"]');
        tabButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const targetSelector = this.getAttribute('data-bs-target');
                
                // Reset all tab buttons
                tabButtons.forEach(b => b.classList.remove('active'));
                
                // Hide all tab panes
                document.querySelectorAll('#profileTabsContent .tab-pane').forEach(pane => {
                    pane.classList.remove('show', 'active');
                });
                
                // Activate current tab button & target pane
                this.classList.add('active');
                const targetPane = document.querySelector(targetSelector);
                if (targetPane) {
                    targetPane.classList.add('show', 'active');
                }
            });
        });
    });
</script>
@endsection
