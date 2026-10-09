@extends('layout')

@section('title', 'تعديل بروفايل موظف')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            
            <div class="mb-4">
                <a href="{{ route('employee_profiles.index') }}" class="text-decoration-none text-muted small d-flex align-items-center gap-1">
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
                        
                        <div class="mb-4">
                            <label for="job_title" class="form-label fw-bold text-slate-700">الوظيفة (المسمى الوظيفي)</label>
                            <select name="job_title" id="job_title" 
                                    class="form-select form-select-lg @error('job_title') is-invalid @enderror" 
                                    style="border-radius: 10px; font-size: 1rem;">
                                <option value="" {{ old('job_title', $user->job_title) == '' ? 'selected' : '' }}>اختر الوظيفة...</option>
                                <option value="Rec" {{ old('job_title', $user->job_title) == 'Rec' ? 'selected' : '' }}>استقبال</option>
                                <option value="Nur" {{ old('job_title', $user->job_title) == 'Nur' ? 'selected' : '' }}>تمريض</option>
                                <option value="Tec" {{ old('job_title', $user->job_title) == 'Tec' ? 'selected' : '' }}>فني</option>
                                <option value="Acc" {{ old('job_title', $user->job_title) == 'Acc' ? 'selected' : '' }}>حسابات</option>
                                <option value="Super_V" {{ old('job_title', $user->job_title) == 'Super_V' ? 'selected' : '' }}>اداري</option>
                                <option value="IT" {{ old('job_title', $user->job_title) == 'IT' ? 'selected' : '' }}>IT</option>
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

                        <div class="mb-4">
                            <label for="hourly_rate" class="form-label fw-bold text-slate-700">قيمة الساعة (ج.م)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0" style="border-radius: 10px 0 0 10px;">⏱️</span>
                                <input type="number" step="0.01" name="hourly_rate" id="hourly_rate" 
                                       class="form-control @error('hourly_rate') is-invalid @enderror" 
                                       value="{{ old('hourly_rate', $user->hourly_rate) }}" 
                                       placeholder="0.00"
                                       style="border-radius: 0 10px 10px 0; font-size: 1rem;">
                            </div>
                            @error('hourly_rate')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text small text-muted">سيتم استخدام هذه القيمة لحساب الإضافي: (الساعات * 1.5 * قيمة الساعة).</div>
                        </div>

                        <hr class="my-4 opacity-50">

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm" style="border-radius: 12px; background: #2563eb; border: none; padding: 12px;">
                                ✅ حفظ التعديلات
                            </button>
                            <a href="{{ route('employee_profiles.index') }}" class="btn btn-light btn-lg fw-semibold" style="border-radius: 12px; padding: 12px;">
                                إلغاء
                            </a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }
    .text-primary { color: #2563eb !important; }
</style>
@endsection
