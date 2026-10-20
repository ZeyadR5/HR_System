@extends('layout')

@section('title', 'تعديل بروفايل موظف')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            
            @php
                $backParams = array_filter([
                    'page'      => request('page') ?: request('return_page'),
                    'job_title' => request('job_title') ?: request('return_job_title'),
                    'user_id'   => request('user_id') ?: request('return_user_id'),
                    'sort_by'   => request('sort_by') ?: request('return_sort_by'),
                    'sort_dir'  => request('sort_dir') ?: request('return_sort_dir'),
                ]);
                $backUrl = route('employee_profiles.index', $backParams);
            @endphp
            <div class="mb-4">
                <a href="{{ $backUrl }}" class="text-decoration-none text-muted small d-flex align-items-center gap-1">
                    <span>🔙</span> العودة للقائمة
                </a>
                <h2 class="fw-bold text-slate-800 mt-2" style="color: #1e293b;">✏️ تعديل بروفايل</h2>
                <p class="text-muted small">تحديث بيانات الموظف: <span class="fw-bold text-primary">{{ $user->name }}</span></p>
            </div>

            <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="border-radius: 16px; background: #f8fafc; border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-slate-700 mb-3 d-flex align-items-center gap-2">
                        📊 إحصائيات سريعة (الدورة الحالية)
                    </h6>
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="p-2 bg-white rounded border text-center">
                                <small class="text-muted d-block">إجازات (ش)</small>
                                <span class="fw-bold">{{ $stats['leaves']['month'] }}/{{ $stats['leaves']['month_limit'] }}</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-white rounded border text-center">
                                <small class="text-muted d-block">أذونات</small>
                                <span class="fw-bold">{{ $stats['permissions']['hours'] }}س</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-primary-subtle rounded border border-primary-subtle text-center">
                                <small class="text-primary d-block">إضافي</small>
                                <span class="fw-bold text-primary">{{ $stats['overtime']['hours'] }}س</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 text-center">
                         <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                            قيمة الإضافي المتوقعة: {{ number_format($stats['overtime']['value'], 2) }} ج.م
                         </span>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <form action="{{ route('employee_profiles.update', $user->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="return_page" value="{{ request('page') ?: request('return_page') }}">
                        <input type="hidden" name="return_job_title" value="{{ request('job_title') ?: request('return_job_title') }}">
                        <input type="hidden" name="return_user_id" value="{{ request('user_id') ?: request('return_user_id') }}">
                        <input type="hidden" name="return_sort_by" value="{{ request('sort_by') ?: request('return_sort_by') }}">
                        <input type="hidden" name="return_sort_dir" value="{{ request('sort_dir') ?: request('return_sort_dir') }}">
                        
                        <div class="mb-4">
                            <label for="job_title" class="form-label fw-bold text-slate-700">الوظيفة (المسمى الوظيفي)</label>
                            <select name="job_title" id="job_title" 
                                    class="form-select form-select-lg @error('job_title') is-invalid @enderror" 
                                    style="border-radius: 10px; font-size: 1rem;">
                                <option value="" {{ old('job_title', $user->job_title) == '' ? 'selected' : '' }}>اختر الوظيفة...</option>
                                <option value="Res" {{ old('job_title', $user->job_title) == 'Res' ? 'selected' : '' }}>استقبال (Res)</option>
                                <option value="Nur" {{ old('job_title', $user->job_title) == 'Nur' ? 'selected' : '' }}>تمريض</option>
                                <option value="Tec" {{ old('job_title', $user->job_title) == 'Tec' ? 'selected' : '' }}>فني</option>
                                <option value="Acc" {{ old('job_title', $user->job_title) == 'Acc' ? 'selected' : '' }}>حسابات</option>
                                <option value="Reporting" {{ old('job_title', $user->job_title) == 'Reporting' ? 'selected' : '' }}>Reporting</option>
                                <option value="Super_V" {{ old('job_title', $user->job_title) == 'Super_V' ? 'selected' : '' }}>اداري</option>
                                <option value="IT Tecnicall Support" {{ old('job_title', $user->job_title) == 'IT Tecnicall Support' ? 'selected' : '' }}>IT Tecnicall Support</option>
                                <option hidden value="IT Manager" {{ old('job_title', $user->job_title) == 'IT Manager' ? 'selected' : '' }}>IT Manager</option>
                                <option value="Call_Center" {{ old('job_title', $user->job_title) == 'Call_Center' ? 'selected' : '' }}>كول سنتر</option>
                            </select>
                            @error('job_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(Auth::user()->role === 'developer')
                        <div class="mb-4">
                            <label for="role" class="form-label fw-bold text-slate-700">الصلاحية (Role)</label>
                            <select name="role" id="role" 
                                    class="form-select form-select-lg @error('role') is-invalid @enderror" 
                                    style="border-radius: 10px; font-size: 1rem;">
                                @foreach($roles ?? \App\Models\Role::orderBy('id')->get() as $r)
                                    <option value="{{ $r->name }}" {{ old('role', $user->role) == $r->name ? 'selected' : '' }}>
                                        {{ $r->display_name }} ({{ $r->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <div class="card border-0 mb-4" style="border-radius: 14px; background: #f8fafc; border: 1px solid #e2e8f0 !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <label class="form-label fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                                        <span>💰</span> حساب وتحديد أجر الساعة
                                    </label>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.75rem;">
                                        ⚡ احتساب ذكي
                                    </span>
                                </div>

                                <div class="row g-3">
                                    {{-- 1. قيمة المرتب --}}
                                    <div class="col-12 col-md-4">
                                        <label for="salary_amount" class="form-label fw-bold text-slate-700 small mb-1">
                                            💵 قيمة المرتب (ج.م)
                                        </label>
                                        <div class="input-group">
                                            <input type="number" step="any" min="0" id="salary_amount" name="salary_amount" 
                                                   class="form-control text-center fw-semibold" 
                                                   value="{{ old('salary_amount', $user->salary_amount ? (float)$user->salary_amount : '') }}" 
                                                   placeholder="مثال: 6000"
                                                   style="border-radius: 10px; font-size: 0.95rem;">
                                        </div>
                                        <div class="form-text extra-small text-muted" style="font-size: 0.75rem;">الراتب الأساسي/الشهري</div>
                                    </div>

                                    {{-- 2. عدد الساعات --}}
                                    <div class="col-12 col-md-4">
                                        <label for="working_hours" class="form-label fw-bold text-slate-700 small mb-1">
                                            ⏳ عدد الساعات
                                        </label>
                                        <div class="input-group">
                                            <input type="number" step="any" min="0" id="working_hours" name="working_hours" 
                                                   class="form-control text-center fw-semibold" 
                                                   value="{{ old('working_hours', $user->working_hours ? (float)$user->working_hours : '') }}" 
                                                   placeholder="مثال: 240"
                                                   style="border-radius: 10px; font-size: 0.95rem;">
                                        </div>
                                        <div class="form-text extra-small text-muted" style="font-size: 0.75rem;">إجمالي ساعات العمل</div>
                                    </div>

                                    {{-- 3. قيمة الساعة --}}
                                    <div class="col-12 col-md-4">
                                        <label for="hourly_rate" class="form-label fw-bold text-primary small mb-1">
                                            ⏱️ قيمة الساعة (ج.م) *
                                        </label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0" name="hourly_rate" id="hourly_rate" 
                                                   class="form-control text-center fw-bold text-primary @error('hourly_rate') is-invalid @enderror" 
                                                   value="{{ old('hourly_rate', $user->hourly_rate) }}" 
                                                   placeholder="0.00"
                                                   style="border-radius: 10px; font-size: 1rem; background-color: #ffffff;" required>
                                        </div>
                                        <div class="form-text extra-small text-muted" style="font-size: 0.75rem;">تُحسب تلقائياً أو يدوياً</div>
                                    </div>
                                </div>

                                @error('hourly_rate')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror

                                <div class="mt-3 p-2 rounded-2 bg-white border border-light text-muted small d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <span style="font-size: 0.8rem;">
                                        💡 <b>المعادلة:</b> قيمة الساعة = المرتب ÷ عدد الساعات
                                    </span>
                                    <span class="badge bg-light text-slate-600 border" id="calc_preview_badge" style="font-size: 0.78rem;">
                                        الحاسبة جاهزة
                                    </span>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 opacity-50">

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm" style="border-radius: 12px; background: #2563eb; border: none; padding: 12px;">
                                ✅ حفظ التعديلات
                            </button>
                            <a href="{{ $backUrl }}" class="btn btn-light btn-lg fw-semibold" style="border-radius: 12px; padding: 12px;">
                                إلغاء
                            </a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const salaryInput = document.getElementById('salary_amount');
        const hoursInput = document.getElementById('working_hours');
        const hourlyRateInput = document.getElementById('hourly_rate');
        const previewBadge = document.getElementById('calc_preview_badge');

        function calculateHourlyRate() {
            const salary = parseFloat(salaryInput.value);
            const hours = parseFloat(hoursInput.value);

            if (!isNaN(salary) && salary > 0 && !isNaN(hours) && hours > 0) {
                const rate = (salary / hours).toFixed(2);
                hourlyRateInput.value = rate;
                if (previewBadge) {
                    previewBadge.className = 'badge bg-success-subtle text-success border border-success-subtle px-2 py-1';
                    previewBadge.innerHTML = `✅ ${salary} ÷ ${hours} = <b>${rate} ج.م/ساعة</b>`;
                }
            } else if (previewBadge) {
                if (!isNaN(salary) && salary > 0 && (!hours || hours <= 0)) {
                    previewBadge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1';
                    previewBadge.innerText = '⚠️ اكتب عدد الساعات لحساب القيمة';
                } else if ((!salary || salary <= 0) && !isNaN(hours) && hours > 0) {
                    previewBadge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1';
                    previewBadge.innerText = '⚠️ اكتب قيمة المرتب لحساب القيمة';
                } else {
                    previewBadge.className = 'badge bg-light text-slate-600 border px-2 py-1';
                    previewBadge.innerText = 'الحاسبة جاهزة';
                }
            }
        }

        if (salaryInput && hoursInput && hourlyRateInput) {
            salaryInput.addEventListener('input', calculateHourlyRate);
            hoursInput.addEventListener('input', calculateHourlyRate);

            hourlyRateInput.addEventListener('input', function() {
                if (previewBadge) {
                    const val = parseFloat(hourlyRateInput.value);
                    if (!isNaN(val) && val > 0) {
                        previewBadge.className = 'badge bg-info-subtle text-info border border-info-subtle px-2 py-1';
                        previewBadge.innerHTML = `✏️ تم تحديد القيمة يدوياً: <b>${val.toFixed(2)} ج.م</b>`;
                    }
                }
            });

            if (salaryInput.value || hoursInput.value) {
                calculateHourlyRate();
            }
        }
    });
</script>

<style>
    .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }
    .text-primary { color: #2563eb !important; }
</style>
@endsection
