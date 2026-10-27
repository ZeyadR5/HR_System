@extends('layout')
@section('title', 'إعدادات النظام العامة')

@section('content')
<div class="container-fluid py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold text-slate-800"><span style="font-size: 1.5rem;">⚙️</span> إعدادات النظام الأساسية (Developer)</h2>
            <p class="text-muted">تحكم في القواعد الأساسية للنظام (مواعيد تقفيل الشهر، أرصدة الإجازات، والحدود القصوى) بدون الرجوع للكود.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success fw-bold border-0 shadow-sm" style="border-radius: 12px; background-color: #dcfce7; color: #166534;">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('developer.settings.update') }}" method="POST">
        @csrf
        <div class="row g-4">
            {{-- دورة الرواتب --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; border-top: 4px solid #2563eb !important;">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">🔄 دورة الرواتب (Payroll Cycle)</h4>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-slate-700">يوم بداية الشهر (بداية السايكل)</label>
                            <input type="number" name="payroll_start_day" value="{{ $settings['payroll_start_day'] }}" class="form-control form-control-lg" min="1" max="31" required>
                            <small class="text-muted mt-1 d-block">الرقم الافتراضي: 26</small>
                        </div>

                        <div>
                            <label class="form-label fw-bold text-slate-700">يوم نهاية الشهر (نهاية السايكل / التقفيل)</label>
                            <input type="number" name="payroll_end_day" value="{{ $settings['payroll_end_day'] }}" class="form-control form-control-lg" min="1" max="31" required>
                            <small class="text-muted mt-1 d-block">الرقم الافتراضي: 25</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- الإضافي --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; border-top: 4px solid #f59e0b !important;">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">⏱️ الإضافي (Overtime)</h4>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-slate-700">الحد الأقصى لعدد ساعات الإضافي في اليوم</label>
                            <div class="input-group input-group-lg">
                                <input type="number" name="daily_overtime_limit" value="{{ $settings['daily_overtime_limit'] }}" class="form-control" min="0" required>
                                <span class="input-group-text">ساعات</span>
                            </div>
                            <small class="text-muted mt-1 d-block">الرقم الافتراضي: 5 ساعات. أي محاولة لإدخال أكثر من ذلك في اليوم الواحد سيتم رفضها تلقائياً.</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- الإجازات --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; border-top: 4px solid #10b981 !important;">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">🌴 الإجازات (Leaves)</h4>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-slate-700">الرصيد السنوي الافتراضي للإجازات</label>
                            <div class="input-group input-group-lg">
                                <input type="number" name="annual_leaves_limit" value="{{ $settings['annual_leaves_limit'] }}" class="form-control" min="0" required>
                                <span class="input-group-text">أيام</span>
                            </div>
                            <small class="text-muted mt-1 d-block">الرقم الافتراضي: 14 يوم.</small>
                        </div>

                        <div>
                            <label class="form-label fw-bold text-slate-700">الحد الأقصى للإجازات شهرياً</label>
                            <div class="input-group input-group-lg">
                                <input type="number" name="monthly_leaves_limit" value="{{ $settings['monthly_leaves_limit'] }}" class="form-control" min="0" required>
                                <span class="input-group-text">أيام</span>
                            </div>
                            <small class="text-muted mt-1 d-block">الرقم الافتراضي: يومين (2) في الشهر.</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- الأذونات --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; border-top: 4px solid #8b5cf6 !important;">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">🚪 الأذونات (Permissions)</h4>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-slate-700">الحد الأقصى لعدد الأذونات المسموحة شهرياً</label>
                            <div class="input-group input-group-lg">
                                <input type="number" name="monthly_permissions_limit" value="{{ $settings['monthly_permissions_limit'] }}" class="form-control" min="0" required>
                                <span class="input-group-text">مرات</span>
                            </div>
                            <small class="text-muted mt-1 d-block">الرقم الافتراضي: 4 مرات في الشهر.</small>
                        </div>

                        <div>
                            <label class="form-label fw-bold text-slate-700">الحد الأقصى لمجموع ساعات الأذونات شهرياً</label>
                            <div class="input-group input-group-lg">
                                <input type="number" name="monthly_permissions_hours" value="{{ $settings['monthly_permissions_hours'] }}" class="form-control" min="0" required>
                                <span class="input-group-text">ساعات</span>
                            </div>
                            <small class="text-muted mt-1 d-block">الرقم الافتراضي: 3 ساعات في الشهر.</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- التسويات --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; border-top: 4px solid #ef4444 !important;">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">⚙️ التسويات (Settlements)</h4>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-slate-700">يوم بداية السماح بطلب التسوية</label>
                            <input type="number" name="settlement_start_day" value="{{ $settings['settlement_start_day'] }}" class="form-control form-control-lg" min="1" max="31" required>
                            <small class="text-muted mt-1 d-block">الرقم الافتراضي: يوم 1 في الشهر.</small>
                        </div>

                        <div>
                            <label class="form-label fw-bold text-slate-700">يوم نهاية السماح بطلب التسوية</label>
                            <input type="number" name="settlement_end_day" value="{{ $settings['settlement_end_day'] }}" class="form-control form-control-lg" min="1" max="31" required>
                            <small class="text-muted mt-1 d-block">الرقم الافتراضي: يوم 10 في الشهر.</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- الحضور بالموقع الجغرافي والسيلفي --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 16px; border-top: 4px solid #0d9488 !important;">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">📍 الحضور بالموقع الجغرافي (Geofencing)</h4>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" role="switch" name="geofence_enabled" value="1" id="geofence_enabled" {{ $settings['geofence_enabled'] ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="geofence_enabled">تفعيل التحقق من الموقع</label>
                                </div>
                                <div class="form-check form-switch fs-5 mt-3">
                                    <input class="form-check-input" type="checkbox" role="switch" name="require_selfie" value="1" id="require_selfie" {{ $settings['require_selfie'] ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="require_selfie">إلزام صورة سيلفي عند الحضور</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">خط العرض (Latitude)</label>
                                <input type="number" step="any" name="office_latitude" value="{{ $settings['office_latitude'] }}" class="form-control form-control-lg" placeholder="30.0444196">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">خط الطول (Longitude)</label>
                                <input type="number" step="any" name="office_longitude" value="{{ $settings['office_longitude'] }}" class="form-control form-control-lg" placeholder="31.2357116">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">نصف القطر المسموح (متر)</label>
                                <input type="number" name="geofence_radius_m" value="{{ $settings['geofence_radius_m'] }}" class="form-control form-control-lg" min="10" max="5000">
                            </div>
                        </div>
                        <small class="text-muted mt-3 d-block">💡 افتح مقر الشركة في Google Maps، اضغط بزر الماوس الأيمن على المكان وانسخ الإحداثيات (الرقم الأول = خط العرض، الثاني = خط الطول).</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 text-end">
            <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #1e293b, #0f172a); border: 0;">
                💾 حفظ وتطبيق الإعدادات
            </button>
        </div>
    </form>
</div>
@endsection
