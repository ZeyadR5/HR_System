@extends('layout')
@section('title', 'إضافة بيانات موظف - السوبر ادمن')

@section('styles')
<style>
    .entry-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .entry-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
    }

    .entry-hero::after {
        content: '';
        position: absolute;
        bottom: -30%;
        right: -10%;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
    }

    .entry-hero h1 {
        color: #fff;
        font-size: 1.6rem;
        font-weight: 700;
        margin: 0 0 0.4rem;
    }

    .entry-hero p {
        color: rgba(255,255,255,0.7);
        font-size: 0.9rem;
        margin: 0;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(255,255,255,0.15);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 999px;
        margin-bottom: 0.7rem;
        border: 1px solid rgba(255,255,255,0.2);
    }

    .card-form {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 20px rgba(0,0,0,0.07);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 1.2rem 1.6rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .card-header h2 {
        font-size: 1rem;
        font-weight: 700;
        color: #1e3a5f;
        margin: 0;
    }

    .card-body {
        padding: 1.6rem;
    }

    .type-selector {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .type-card {
        position: relative;
        cursor: pointer;
    }

    .type-card input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .type-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 1rem 0.75rem;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
    }

    .type-label:hover {
        border-color: #93c5fd;
        background: #eff6ff;
    }

    .type-card input:checked + .type-label {
        border-color: #2563eb;
        background: #eff6ff;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
    }

    .type-icon {
        font-size: 1.8rem;
        line-height: 1;
    }

    .type-name {
        font-size: 0.85rem;
        font-weight: 700;
        color: #1e3a5f;
    }

    .type-desc {
        font-size: 0.72rem;
        color: #64748b;
        line-height: 1.3;
    }

    .form-section {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1.2rem;
        margin-bottom: 1rem;
        background: #fafafa;
    }

    .form-section-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: #2563eb;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .form-grid {
        display: grid;
        gap: 1rem;
    }

    .form-grid.cols-2 {
        grid-template-columns: 1fr 1fr;
    }

    .form-grid.cols-3 {
        grid-template-columns: 1fr 1fr 1fr;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .form-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: #374151;
    }

    .form-label .required {
        color: #ef4444;
        margin-right: 2px;
    }

    .form-control {
        width: 100%;
        padding: 0.55rem 0.85rem;
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        font-family: 'Cairo', sans-serif;
        font-size: 0.87rem;
        color: #1f2937;
        background: #fff;
        transition: border-color 0.18s, box-shadow 0.18s;
        outline: none;
    }

    .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }

    select.form-control {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: left 0.6rem center;
        background-repeat: no-repeat;
        background-size: 1.2em;
        padding-left: 2rem;
    }

    .employee-search-wrapper {
        position: relative;
    }

    .employee-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1.5px solid #2563eb;
        border-top: none;
        border-radius: 0 0 8px 8px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 100;
        display: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }

    .suggestion-item {
        padding: 0.6rem 0.85rem;
        cursor: pointer;
        font-size: 0.86rem;
        color: #1f2937;
        transition: background 0.15s;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .suggestion-item:last-child {
        border-bottom: none;
    }

    .suggestion-item:hover {
        background: #eff6ff;
        color: #2563eb;
    }

    .suggestion-badge {
        font-size: 0.7rem;
        background: #dbeafe;
        color: #2563eb;
        padding: 1px 7px;
        border-radius: 999px;
        font-weight: 600;
    }

    .suggestion-new-badge {
        font-size: 0.7rem;
        background: #fef3c7;
        color: #92400e;
        padding: 1px 7px;
        border-radius: 999px;
        font-weight: 600;
    }

    .dynamic-fields {
        display: none;
    }

    .dynamic-fields.show {
        display: block;
    }

    .btn-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 2rem;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        font-family: 'Cairo', sans-serif;
        font-size: 0.92rem;
        font-weight: 700;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 15px rgba(37,99,235,0.3);
        width: 100%;
    }

    .btn-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(37,99,235,0.4);
    }

    .btn-submit:active {
        transform: translateY(0);
    }

    .alert-success {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        border: 1px solid #6ee7b7;
        color: #065f46;
        border-radius: 10px;
        padding: 0.9rem 1.2rem;
        margin-bottom: 1.2rem;
        font-size: 0.9rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .alert-error {
        background: #fef2f2;
        border: 1px solid #fca5a5;
        color: #991b1b;
        border-radius: 10px;
        padding: 0.9rem 1.2rem;
        margin-bottom: 1.2rem;
        font-size: 0.9rem;
    }

    .info-note {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-size: 0.82rem;
        color: #1e40af;
        display: flex;
        gap: 0.5rem;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .is-invalid {
        border-color: #ef4444 !important;
    }

    .error-msg {
        font-size: 0.78rem;
        color: #ef4444;
        margin-top: 2px;
    }

    @media (max-width: 640px) {
        .type-selector {
            grid-template-columns: 1fr;
        }

        .form-grid.cols-2,
        .form-grid.cols-3 {
            grid-template-columns: 1fr;
        }

        .entry-hero h1 {
            font-size: 1.2rem;
        }
    }

    @keyframes pulseBreach {
        0%, 100% { border-color: #ef4444; box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        50% { border-color: #dc2626; box-shadow: 0 0 0 6px rgba(239, 68, 68, 0.15); }
    }
    .breach-alert-box {
        animation: pulseBreach 2s infinite ease-in-out;
    }
    .balance-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        border: 1px solid transparent;
    }
    .balance-pill-ok {
        background: #ecfdf5;
        color: #065f46;
        border-color: #a7f3d0;
    }
    .balance-pill-warn {
        background: #fffbeb;
        color: #92400e;
        border-color: #fde68a;
    }
    .balance-pill-danger {
        background: #fef2f2;
        color: #991b1b;
        border-color: #fecdd3;
    }
</style>
@endsection

@section('content')
<div style="max-width:820px; margin:0 auto; padding:1rem 0 3rem;">



    {{-- Success / Error Messages --}}
    @if(session('success'))
        <div class="alert-success">
            <span style="font-size:1.1rem;">✅</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- Alert when submitting exceeded limits and needs confirmation --}}
    @if(session('needs_override_confirmation') && session('breached_warnings'))
        <div style="background: linear-gradient(135deg, #fffbeb, #fef3c7); border: 2px solid #f59e0b; border-radius: 14px; padding: 1.2rem 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.15);">
            <div style="display: flex; align-items: flex-start; gap: 0.8rem;">
                <span style="font-size: 1.8rem; line-height: 1;">⚠️</span>
                <div style="flex-grow: 1;">
                    <h4 style="margin: 0 0 0.4rem; color: #92400e; font-size: 1.05rem; font-weight: 700;">تنبيه: هذه الإضافة اليدوية تتجاوز حدود الرصيد المسموح به للموظف!</h4>
                    <ul style="margin: 0 0 0.8rem; padding-right: 1.2rem; color: #78350f; font-size: 0.88rem; line-height: 1.6;">
                        @foreach(session('breached_warnings') as $w)
                            <li><strong>{{ $w }}</strong></li>
                        @endforeach
                    </ul>
                    <p style="margin: 0 0 0.8rem; font-size: 0.84rem; color: #92400e;">هل ترغب في اعتماد هذه الإضافة كـ <strong>استثناء إداري معتمد</strong> وتخطي الرصيد للموظف؟</p>
                    <button type="button" class="btn btn-warning btn-sm fw-bold px-3 py-2 shadow-xs" onclick="confirmOverrideAndSubmit()" style="border-radius: 8px; background: #d97706; border-color: #b45309; color: #fff;">
                        ⚡ نعم، اعتمد الإضافة كاستثناء الآن
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert-error">
            <strong>⚠️ يرجى مراجعة:</strong>
            <ul style="margin:0.4rem 0 0 0; padding-right:1.2rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card-form">
        <div class="card-header">
            <span style="font-size:1.2rem;">📝</span>
            <h2>نموذج الإضافة اليدوية</h2>
        </div>

        <div class="card-body">

            <form action="{{ route('super_admin.entry.store') }}" method="POST" id="entryForm">
                @csrf
                <input type="hidden" name="confirmed_override" id="confirmed_override" value="0">

                {{-- Step 1: Entry Type --}}
                <div class="form-section">
                    <div class="form-section-title">
                        <span>1️⃣</span> اختر نوع الإدخال
                    </div>

                    <div class="type-selector">
                        <label class="type-card">
                            <input type="radio" name="entry_type" value="overtime" id="type_overtime"
                                {{ old('entry_type') === 'overtime' ? 'checked' : '' }}>
                            <span class="type-label">
                                <span class="type-icon">⏱️</span>
                                <span class="type-name">إضافي</span>
                                <span class="type-desc">ساعات العمل الإضافية</span>
                            </span>
                        </label>

                        <label class="type-card">
                            <input type="radio" name="entry_type" value="leave" id="type_leave"
                                {{ old('entry_type') === 'leave' ? 'checked' : '' }}>
                            <span class="type-label">
                                <span class="type-icon">🏖️</span>
                                <span class="type-name">إجازة</span>
                                <span class="type-desc">إجازة عادية أو مرضية</span>
                            </span>
                        </label>

                        <label class="type-card">
                            <input type="radio" name="entry_type" value="permission" id="type_permission"
                                {{ old('entry_type') === 'permission' ? 'checked' : '' }}>
                            <span class="type-label">
                                <span class="type-icon">📋</span>
                                <span class="type-name">إذن</span>
                                <span class="type-desc">إذن تأخير أو انصراف أو بصمة</span>
                            </span>
                        </label>
                    </div>

                    @error('entry_type')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Step 2: Employee + Common Fields --}}
                <div class="form-section">
                    <div class="form-section-title">
                        <span>2️⃣</span> بيانات الموظف والتاريخ
                    </div>


                    <div class="form-grid cols-2" style="margin-bottom:1rem;">
                        <div class="form-group">
                            <label class="form-label" for="employee_name">
                                <span class="required">*</span> اسم الموظف
                            </label>
                            <div class="employee-search-wrapper">
                                <input type="text"
                                    name="employee_name"
                                    id="employee_name"
                                    class="form-control {{ $errors->has('employee_name') ? 'is-invalid' : '' }}"
                                    placeholder="ابدأ بكتابة الاسم..."
                                    value="{{ old('employee_name') }}"
                                    autocomplete="off">
                                <div class="employee-suggestions" id="employeeSuggestions"></div>
                            </div>
                            @error('employee_name')
                                <p class="error-msg">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="entry_date">
                                <span class="required">*</span> التاريخ
                            </label>
                            <input type="text"
                                name="date"
                                id="entry_date"
                                class="form-control {{ $errors->has('date') ? 'is-invalid' : '' }}"
                                placeholder="اختر التاريخ"
                                value="{{ old('date') }}"
                                autocomplete="off">
                            @error('date')
                                <p class="error-msg">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="form-grid cols-2">
                        <div class="form-group">
                            <label class="form-label" for="entry_day">
                                <span class="required">*</span> اليوم
                            </label>
                            <select name="day" id="entry_day"
                                class="form-control {{ $errors->has('day') ? 'is-invalid' : '' }}">
                                <option value="">اختر اليوم</option>
                                @foreach(['السبت','الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة'] as $d)
                                    <option value="{{ $d }}" {{ old('day') === $d ? 'selected' : '' }}>{{ $d }}</option>
                                @endforeach
                            </select>
                            @error('day')
                                <p class="error-msg">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="entry_reason">
                                <span class="required">*</span> السبب / الوصف
                            </label>
                            <input type="text"
                                name="reason"
                                id="entry_reason"
                                class="form-control {{ $errors->has('reason') ? 'is-invalid' : '' }}"
                                placeholder="أدخل السبب..."
                                value="{{ old('reason') }}">
                            @error('reason')
                                <p class="error-msg">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Live Employee Balance & Limit Breach Alert --}}
                    <div id="liveBalanceContainer" style="display: none; margin-top: 1.2rem;">
                        {{-- Balance Stats Pill Bar --}}
                        <div id="liveBalanceInfo" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 0.85rem 1.1rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.8rem;">
                            <div style="display: flex; align-items: center; gap: 0.6rem;">
                                <span style="font-size: 1.25rem;">📊</span>
                                <div>
                                    <div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;" id="liveEmployeeTitle"></div>
                                    <div style="font-size: 0.76rem; color: #64748b;" id="livePeriodLabel"></div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;" id="liveBalanceBadges">
                            </div>
                        </div>

                        {{-- Limit Breach Warning Card --}}
                        <div id="liveBreachBanner" class="breach-alert-box" style="display: none; margin-top: 0.8rem; background: linear-gradient(135deg, #fff1f2, #fee2e2); border: 2px solid #ef4444; border-radius: 12px; padding: 1rem 1.25rem;">
                            <div style="display: flex; align-items: flex-start; gap: 0.8rem;">
                                <span style="font-size: 1.7rem; line-height: 1;">⚠️</span>
                                <div style="flex-grow: 1;">
                                    <div style="font-weight: 800; color: #991b1b; font-size: 0.98rem; margin-bottom: 0.4rem;" id="liveBreachTitle">
                                        تنبيه: هذا الإدخال يتجاوز حدود الرصيد المسموح به للموظف!
                                    </div>
                                    <div id="liveBreachList" style="color: #7f1d1d; font-size: 0.86rem; line-height: 1.6;">
                                    </div>
                                    <div style="margin-top: 0.6rem; font-size: 0.8rem; color: #b91c1c; font-weight: 600; display: flex; align-items: center; gap: 0.35rem;">
                                        <span>💡</span>
                                        <span>يمكنك اعتماد الإضافة كاستثناء إداري مخصص عند النقر على الحفظ.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Overtime Specific Fields --}}
                <div class="form-section dynamic-fields" id="fields_overtime">
                    <div class="form-section-title">
                        <span>3️⃣</span> بيانات الإضافي
                    </div>
                    <div class="form-grid cols-2">
                        <div class="form-group">
                            <label class="form-label" for="ot_from">
                                <span class="required">*</span> من الساعة
                            </label>
                            <input type="time" name="from" id="ot_from"
                                class="form-control {{ $errors->has('from') ? 'is-invalid' : '' }}"
                                value="{{ old('from') }}"
                                disabled>
                            @error('from')
                                <p class="error-msg">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="ot_to">
                                <span class="required">*</span> إلى الساعة
                            </label>
                            <input type="time" name="to" id="ot_to"
                                class="form-control {{ $errors->has('to') ? 'is-invalid' : '' }}"
                                value="{{ old('to') }}"
                                disabled>
                            @error('to')
                                <p class="error-msg">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div id="hoursPreview" style="margin-top:0.7rem; font-size:0.85rem; color:#2563eb; font-weight:600; display:none;">
                        ⏱️ المدة: <span id="hoursDisplay"></span>
                    </div>
                </div>

                {{-- Leave Specific Fields --}}
                <div class="form-section dynamic-fields" id="fields_leave">
                    <div class="form-section-title">
                        <span>3️⃣</span> بيانات الإجازة
                    </div>
                    <div class="form-grid cols-2">
                        <div class="form-group">
                            <label class="form-label" for="leave_substitute">
                                <span class="required">*</span> البديل
                            </label>
                            <input type="text" name="substitute" id="leave_substitute"
                                class="form-control {{ $errors->has('substitute') ? 'is-invalid' : '' }}"
                                placeholder="اسم الموظف البديل"
                                value="{{ old('substitute') }}"
                                disabled>
                            @error('substitute')
                                <p class="error-msg">{{ $message }}</p>
                            @enderror
                        </div>
                        {{--
                        <div class="form-group">
                            <label class="form-label" for="leave_days">
                                <span class="required">*</span> عدد الأيام
                            </label>
                            <input type="number" name="days_count" id="leave_days_visible"
                                class="form-control {{ $errors->has('days_count') ? 'is-invalid' : '' }}"
                                min="1" placeholder="1"
                                value="{{ old('days_count', 1) }}"
                                disabled>
                            @error('days_count')
                                <p class="error-msg">{{ $message }}</p>
                            @enderror
                        </div>
                        --}}
                        <input type="hidden" name="days_count" id="leave_days" value="1" disabled>
                    </div>
                </div>

                {{-- Permission Specific Fields --}}
                <div class="form-section dynamic-fields" id="fields_permission">
                    <div class="form-section-title">
                        <span>3️⃣</span> بيانات الإذن
                    </div>
                    <div class="form-group" style="margin-bottom:1rem;">
                        <label class="form-label" for="perm_type">
                            <span class="required">*</span> نوع الإذن
                        </label>
                        <select name="permission_type" id="perm_type"
                            class="form-control {{ $errors->has('permission_type') ? 'is-invalid' : '' }}"
                            onchange="togglePermissionTimes(this.value)"
                            disabled>
                            <option value="">اختر نوع الإذن</option>
                            <option value="إذن تأخير" {{ old('permission_type') === 'إذن تأخير' ? 'selected' : '' }}>إذن تأخير</option>
                            <option value="إذن انصراف باكر" {{ old('permission_type') === 'إذن انصراف باكر' ? 'selected' : '' }}>إذن انصراف باكر</option>
                            <option value="إذن نسيان بصمة حضور" {{ old('permission_type') === 'إذن نسيان بصمة حضور' ? 'selected' : '' }}>إذن نسيان بصمة حضور</option>
                            <option value="إذن نسيان بصمة انصراف" {{ old('permission_type') === 'إذن نسيان بصمة انصراف' ? 'selected' : '' }}>إذن نسيان بصمة انصراف</option>
                        </select>
                        @error('permission_type')
                            <p class="error-msg">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="perm_times" style="display:none;">
                        <div class="form-grid cols-2">
                            <div class="form-group">
                                <label class="form-label" for="perm_from">من الساعة</label>
                                <input type="time" name="perm_from" id="perm_from"
                                    class="form-control"
                                    value="{{ old('perm_from') }}"
                                    disabled>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="perm_to">إلى الساعة</label>
                                <input type="time" name="perm_to" id="perm_to"
                                    class="form-control"
                                    value="{{ old('perm_to') }}"
                                    disabled>
                            </div>
                        </div>
                        <div id="permHoursPreview" style="margin-top:0.7rem; font-size:0.85rem; color:#2563eb; font-weight:600; display:none;">
                            ⏱️ المدة: <span id="permHoursDisplay"></span>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div style="margin-top:1.5rem;">
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <span id="submitIcon">💾</span>
                        <span id="submitText">حفظ وإضافة البيانات</span>
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

{{-- Override Confirmation Modal --}}
<div class="modal fade" id="overrideConfirmModal" tabindex="-1" aria-hidden="true" style="display: none; z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content border-0 shadow-lg text-end" style="border-radius: 18px; overflow: hidden; background: #fff;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size: 1.5rem;">⚠️</span>
                    <h5 class="modal-title fw-bold text-danger mb-0" style="font-size: 1.1rem;">تأكيد كسر حدود الرصيد للموظف</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body p-4 text-end">
                <p class="text-muted small mb-2">
                    البيانات المدخلة تتجاوز الحد النظامي المعتمد لهذا الموظف:
                </p>
                <div id="modalBreachWarnings" class="p-3 rounded-3 mb-3" style="background: #fff1f2; border: 1px solid #fecdd3; color: #9f1239; font-size: 0.88rem; line-height: 1.6;">
                </div>
                <div class="p-2 rounded-2" style="background: #fffbeb; border: 1px solid #fde68a; font-size: 0.84rem; color: #92400e;">
                    هل تؤكد رغبتك في تسجيل هذه الإضافة اليدوية كـ <strong>استثناء إداري معتمد</strong> مع توثيق ذلك في سجل العمليات؟
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-between gap-2">
                <button type="button" class="btn btn-warning fw-bold px-3 py-2 shadow-xs d-flex align-items-center gap-1" id="confirmOverrideBtn" style="border-radius: 10px; background: #d97706; border-color: #b45309; color: #fff;">
                    <span>⚡</span>
                    <span>نعم، اعتمد الإضافة كاستثناء</span>
                </button>
                <button type="button" class="btn btn-light fw-semibold px-3 py-2" data-bs-dismiss="modal" style="border-radius: 10px; border: 1px solid #cbd5e1;">
                    تراجع وتعديل
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Hidden employees data for JS autocomplete --}}
<script>
const systemUsers = @json($users->pluck('name'));
</script>

<script>
// ---- Type Selector ----
const typeRadios = document.querySelectorAll('input[name="entry_type"]');
const allDynamic = document.querySelectorAll('.dynamic-fields');

// Map of type => input names to enable when that section is active
const sectionInputs = {
    overtime:   ['ot_from', 'ot_to'],
    leave:      ['leave_substitute', 'leave_days'],
    permission: ['perm_type'],
};

function setInputsDisabled(ids, disabled) {
    ids.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.disabled = disabled;
    });
}

function showDynamicFields(type) {
    // Hide all sections and disable their inputs
    allDynamic.forEach(el => el.classList.remove('show'));
    Object.values(sectionInputs).forEach(ids => setInputsDisabled(ids, true));
    // Also disable perm time inputs
    setInputsDisabled(['perm_from', 'perm_to'], true);

    if (type) {
        const target = document.getElementById('fields_' + type);
        if (target) target.classList.add('show');
        // Enable only the current section's inputs
        if (sectionInputs[type]) setInputsDisabled(sectionInputs[type], false);
    }

    // Update button text
    const labels = { overtime: 'حفظ الإضافي', leave: 'حفظ الإجازة', permission: 'حفظ الإذن' };
    const icons  = { overtime: '⏱️', leave: '🏖️', permission: '📋' };
    document.getElementById('submitText').textContent = labels[type] || 'حفظ وإضافة البيانات';
    document.getElementById('submitIcon').textContent = icons[type] || '💾';
}

typeRadios.forEach(radio => {
    radio.addEventListener('change', () => showDynamicFields(radio.value));
});

// Init on page load (in case of old() values)
const checkedType = document.querySelector('input[name="entry_type"]:checked');
if (checkedType) {
    showDynamicFields(checkedType.value);
    // Re-enable perm times if needed
    const permType = document.getElementById('perm_type');
    if (permType && permType.value) togglePermissionTimes(permType.value);
}

// ---- Permission Times Toggle ----
function togglePermissionTimes(val) {
    const timesDiv = document.getElementById('perm_times');
    const needsTimes = (val === 'إذن تأخير' || val === 'إذن انصراف باكر');
    timesDiv.style.display = needsTimes ? 'block' : 'none';
    setInputsDisabled(['perm_from', 'perm_to'], !needsTimes);
    if (needsTimes) {
        updatePermHoursPreview();
    } else {
        const preview = document.getElementById('permHoursPreview');
        if (preview) preview.style.display = 'none';
    }
}

// ---- Duration Preview Helper ----
function calculateDurationText(fromVal, toVal) {
    if (!fromVal || !toVal) return null;

    const [fh, fm] = fromVal.split(':').map(Number);
    const [th, tm] = toVal.split(':').map(Number);
    let totalMins = (th * 60 + tm) - (fh * 60 + fm);
    if (totalMins < 0) totalMins += 24 * 60; // handle overnight spans

    if (totalMins <= 0) return null;

    const h = Math.floor(totalMins / 60);
    const m = totalMins % 60;

    if (h > 0 && m > 0) return `${h} ساعة و ${m} دقيقة`;
    if (h > 0) return `${h} ساعة`;
    return `${m} دقيقة`;
}

// ---- Hours Preview for Overtime ----
function updateHoursPreview() {
    const fromVal = document.getElementById('ot_from').value;
    const toVal   = document.getElementById('ot_to').value;
    const text    = calculateDurationText(fromVal, toVal);
    const preview = document.getElementById('hoursPreview');

    if (text) {
        document.getElementById('hoursDisplay').textContent = text;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

// ---- Hours Preview for Permission ----
function updatePermHoursPreview() {
    const fromVal = document.getElementById('perm_from').value;
    const toVal   = document.getElementById('perm_to').value;
    const text    = calculateDurationText(fromVal, toVal);
    const preview = document.getElementById('permHoursPreview');

    if (preview) {
        if (text) {
            document.getElementById('permHoursDisplay').textContent = text;
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    }
}

['change', 'input'].forEach(evt => {
    document.getElementById('ot_from')?.addEventListener(evt, updateHoursPreview);
    document.getElementById('ot_to')?.addEventListener(evt, updateHoursPreview);
    document.getElementById('perm_from')?.addEventListener(evt, updatePermHoursPreview);
    document.getElementById('perm_to')?.addEventListener(evt, updatePermHoursPreview);
});

// ---- Employee Autocomplete ----
const nameInput = document.getElementById('employee_name');
const suggestionsBox = document.getElementById('employeeSuggestions');

nameInput.addEventListener('input', function() {
    const query = this.value.trim().toLowerCase();
    suggestionsBox.innerHTML = '';

    if (query.length < 1) { suggestionsBox.style.display = 'none'; return; }

    const matches = systemUsers.filter(name => name.toLowerCase().includes(query));

    if (matches.length === 0 && query.length >= 2) {
        // Show "new employee" option
        const item = document.createElement('div');
        item.className = 'suggestion-item';
        item.innerHTML = `<span>👤</span> <span>${this.value}</span> <span class="suggestion-new-badge">موظف خارجي</span>`;
        item.addEventListener('click', () => {
            nameInput.value = this.value;
            suggestionsBox.style.display = 'none';
        });
        suggestionsBox.appendChild(item);
        suggestionsBox.style.display = 'block';
        return;
    }

    matches.forEach(name => {
        const item = document.createElement('div');
        item.className = 'suggestion-item';
        item.innerHTML = `👤 ${name} <span class="suggestion-badge">في النظام</span>`;
        item.addEventListener('click', () => {
            nameInput.value = name;
            suggestionsBox.style.display = 'none';
        });
        suggestionsBox.appendChild(item);
    });

    suggestionsBox.style.display = matches.length > 0 ? 'block' : 'none';
});

document.addEventListener('click', (e) => {
    if (!nameInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
        suggestionsBox.style.display = 'none';
    }
});

// ---- Date Picker with limits check ----
const dateFp = flatpickr('#entry_date', {
    dateFormat: 'Y-m-d',
    locale: { firstDayOfWeek: 6 },
    allowInput: true,
    onChange: function(dates) {
        if (dates.length > 0) {
            const days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
            document.getElementById('entry_day').value = days[dates[0].getDay()];
        }
        debouncedCheckLimits();
    }
});

// ---- Real-time Employee Limits & Breach Checking ----
window.currentLimitBreached = false;
window.currentBreachWarnings = [];
let checkLimitsTimer = null;

function debouncedCheckLimits() {
    clearTimeout(checkLimitsTimer);
    checkLimitsTimer = setTimeout(checkEmployeeLimits, 300);
}

function checkEmployeeLimits() {
    const employeeName = document.getElementById('employee_name')?.value?.trim();
    const checkedRadio = document.querySelector('input[name="entry_type"]:checked');
    const entryType = checkedRadio ? checkedRadio.value : '';
    const dateVal = document.getElementById('entry_date')?.value?.trim();
    const daysCount = document.getElementById('leave_days')?.value || 1;
    const otFrom = document.getElementById('ot_from')?.value;
    const otTo = document.getElementById('ot_to')?.value;
    const permFrom = document.getElementById('perm_from')?.value;
    const permTo = document.getElementById('perm_to')?.value;
    const permType = document.getElementById('perm_type')?.value;

    const container = document.getElementById('liveBalanceContainer');
    const breachBanner = document.getElementById('liveBreachBanner');
    const titleEl = document.getElementById('liveEmployeeTitle');
    const periodLabelEl = document.getElementById('livePeriodLabel');
    const badgesEl = document.getElementById('liveBalanceBadges');
    const breachListEl = document.getElementById('liveBreachList');

    if (!employeeName || !entryType) {
        if (container) container.style.display = 'none';
        window.currentLimitBreached = false;
        window.currentBreachWarnings = [];
        return;
    }

    const params = new URLSearchParams({
        employee_name: employeeName,
        entry_type: entryType,
        date: dateVal || '',
        days_count: daysCount,
        from: entryType === 'permission' ? (permFrom || '') : (otFrom || ''),
        to: entryType === 'permission' ? (permTo || '') : (otTo || ''),
        permission_type: permType || ''
    });

    fetch('{{ route("super_admin.entry.check_limits") }}?' + params.toString())
        .then(response => response.json())
        .then(data => {
            if (!data.has_user) {
                if (container) container.style.display = 'none';
                window.currentLimitBreached = false;
                window.currentBreachWarnings = [];
                return;
            }

            container.style.display = 'block';
            titleEl.textContent = `👤 ${data.user_name} (${data.job_title || 'موظف'})`;
            periodLabelEl.textContent = data.stats?.period_label ? `دورة القبض: ${data.stats.period_label}` : '';

            // Render stats badges
            let badgesHtml = '';
            const s = data.stats || {};

            if (entryType === 'leave') {
                const monthClass = (s.month_remaining <= 0) ? 'balance-pill-danger' : (s.month_remaining === 1 ? 'balance-pill-warn' : 'balance-pill-ok');
                const yearClass = (s.year_remaining <= 0) ? 'balance-pill-danger' : (s.year_remaining <= 3 ? 'balance-pill-warn' : 'balance-pill-ok');

                badgesHtml += `<span class="balance-pill ${monthClass}">🏖️ إجازات الشهر: ${s.month_used} / ${s.month_limit} يوم (متبقي: ${s.month_remaining})</span>`;
                badgesHtml += `<span class="balance-pill ${yearClass}">🌟 إجازات السنة: ${s.year_used} / ${s.year_limit} يوم (متبقي: ${s.year_remaining})</span>`;
            } else if (entryType === 'permission') {
                const permClass = (s.minutes_remaining <= 0) ? 'balance-pill-danger' : (s.minutes_remaining <= 60 ? 'balance-pill-warn' : 'balance-pill-ok');
                badgesHtml += `<span class="balance-pill ${permClass}">📋 أذونات الشهر: ${s.hours_used} / ${s.hours_limit} ساعات</span>`;
                badgesHtml += `<span class="balance-pill ${permClass}">⏳ المتبقي: ${s.minutes_remaining} دقيقة</span>`;
            } else if (entryType === 'overtime') {
                const otClass = (s.day_remaining <= 0) ? 'balance-pill-danger' : (s.day_remaining <= 1 ? 'balance-pill-warn' : 'balance-pill-ok');
                badgesHtml += `<span class="balance-pill ${otClass}">⏱️ إضافي اليوم: ${s.day_used} / ${s.day_limit} ساعات (متبقي: ${s.day_remaining})</span>`;
            }

            badgesEl.innerHTML = badgesHtml;

            // Handle breach alert
            window.currentLimitBreached = !!data.is_breached;
            window.currentBreachWarnings = data.warnings || [];

            if (data.is_breached && data.warnings && data.warnings.length > 0) {
                breachBanner.style.display = 'block';
                breachListEl.innerHTML = data.warnings.map(w => `<div style="margin-bottom:0.25rem;">• <strong>${w}</strong></div>`).join('');
            } else {
                breachBanner.style.display = 'none';
                breachListEl.innerHTML = '';
            }
        })
        .catch(err => {
            console.error('Error checking limits:', err);
        });
}

// Attach listeners to trigger limits check
['input', 'change'].forEach(evt => {
    document.getElementById('employee_name')?.addEventListener(evt, debouncedCheckLimits);
    document.getElementById('leave_days')?.addEventListener(evt, debouncedCheckLimits);
    document.getElementById('ot_from')?.addEventListener(evt, debouncedCheckLimits);
    document.getElementById('ot_to')?.addEventListener(evt, debouncedCheckLimits);
    document.getElementById('perm_from')?.addEventListener(evt, debouncedCheckLimits);
    document.getElementById('perm_to')?.addEventListener(evt, debouncedCheckLimits);
    document.getElementById('perm_type')?.addEventListener(evt, debouncedCheckLimits);
});

typeRadios.forEach(radio => {
    radio.addEventListener('change', debouncedCheckLimits);
});

// Intercept form submit if limit is breached
const entryForm = document.getElementById('entryForm');
if (entryForm) {
    entryForm.addEventListener('submit', function(e) {
        const overrideInput = document.getElementById('confirmed_override');
        if (window.currentLimitBreached && overrideInput.value !== '1') {
            e.preventDefault();
            const modalWarningsBox = document.getElementById('modalBreachWarnings');
            if (modalWarningsBox) {
                modalWarningsBox.innerHTML = window.currentBreachWarnings.map(w => `<div style="margin-bottom:0.35rem;">⚠️ <strong>${w}</strong></div>`).join('');
            }
            const modalEl = document.getElementById('overrideConfirmModal');
            if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            } else {
                if (confirm('⚠️ تنبيه كسر حدود الرصيد للموظف:\n\n' + window.currentBreachWarnings.join('\n') + '\n\nهل ترغب في اعتماد الإضافة كاستثناء إداري؟')) {
                    overrideInput.value = '1';
                    entryForm.submit();
                }
            }
        }
    });
}

// Modal confirmation action
document.getElementById('confirmOverrideBtn')?.addEventListener('click', function() {
    const overrideInput = document.getElementById('confirmed_override');
    if (overrideInput && entryForm) {
        overrideInput.value = '1';
        entryForm.submit();
    }
});

function confirmOverrideAndSubmit() {
    const overrideInput = document.getElementById('confirmed_override');
    if (overrideInput && entryForm) {
        overrideInput.value = '1';
        entryForm.submit();
    }
}

// Initial check on page load if employee_name is filled
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('employee_name')?.value?.trim()) {
        checkEmployeeLimits();
    }
});
</script>
@endsection
