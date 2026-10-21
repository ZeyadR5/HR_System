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
                    <form action="{{ route('employee_profiles.update', $user->id) }}" method="POST" enctype="multipart/form-data">
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
                                <option value="Rec" {{ old('job_title', $user->job_title) == 'Rec' ? 'selected' : '' }}>استقبال (Rec)</option>
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

                        {{-- قسم صورة البروفايل --}}
                        <div class="card border-0 mb-4" style="border-radius: 14px; background: #f8fafc; border: 1px solid #e2e8f0 !important;">
                            <div class="card-body p-3">
                                <label class="form-label fw-bold text-slate-800 d-flex align-items-center justify-content-between mb-3" style="font-size: 0.95rem;">
                                    <span class="d-flex align-items-center gap-2">
                                        <span>👤</span> صورة البروفايل الشخصية للموظف
                                    </span>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.72rem;">
                                        الظهور في النظام
                                    </span>
                                </label>

                                <div class="d-flex align-items-center gap-3 mb-3 p-2 bg-white rounded-3 border">
                                    <div style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; flex-shrink: 0; border: 2px solid #e2e8f0;">
                                        <img src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <div class="flex-grow-1">
                                        @if($user->profile_picture && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile_picture))
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                    ✅ صورة مخصصة مرفوعة
                                                </span>
                                                <a href="{{ route('employee_profiles.document', [$user->id, 'profile_picture']) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.8rem;">
                                                    👁️ عرض
                                                </a>
                                            </div>
                                            @php
                                                $canManageAvatar = Auth::id() === $user->id || Auth::user()->can('employees.avatar_manage');
                                            @endphp
                                            @if($canManageAvatar)
                                                <button type="button" class="btn btn-sm btn-outline-danger mt-2 d-flex align-items-center gap-1" style="font-size: 0.8rem;" onclick="deleteFromEdit('profile_picture', this)">
                                                    <span>🗑️</span> حذف الصورة الحالية والعودة للأيقونة الافتراضية
                                                </button>
                                            @endif
                                        @else
                                            <span class="badge bg-light text-muted border">
                                                الأيقونة الافتراضية بالحرف الأول
                                            </span>
                                            <div class="text-muted extra-small mt-1" style="font-size: 0.75rem;">لم يتم رفع صورة شخصية حتى الآن.</div>
                                        @endif
                                    </div>
                                </div>

                                @if($canManageAvatar)
                                    <div>
                                        <label for="profile_picture" class="form-label small fw-semibold text-slate-700 mb-1">
                                            {{ $user->profile_picture ? 'استبدال الصورة الحالية:' : 'رفع صورة البروفايل:' }}
                                        </label>
                                        <input type="file" name="profile_picture" id="profile_picture" accept="image/png,image/jpeg,image/webp,image/jpg"
                                               class="form-control @error('profile_picture') is-invalid @enderror" style="border-radius: 10px; font-size: 0.9rem;" onchange="handleFileSelect('profile_picture', this)">
                                        <div id="bar_profile_picture" class="mt-2 p-2 rounded-2 bg-light border d-none align-items-center justify-content-between flex-wrap gap-2">
                                            <span class="small text-primary fw-semibold" id="text_profile_picture"></span>
                                        </div>
                                        <div class="form-text extra-small text-muted" style="font-size: 0.75rem;">الامتدادات المدعومة: JPG, PNG, WEBP (الحد الأقصى 5 ميجابايت). يمكن للموظف أيضاً تعديل صورته بنفسه من بروفايله.</div>
                                        @error('profile_picture')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- قسم أوراق وملفات الموظف (وثائق الـ HR الرسمية) --}}
                        <div class="card border-0 mb-4" id="documents-section" style="border-radius: 14px; background: #f8fafc; border: 1px solid #e2e8f0 !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <label class="form-label fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                                        <span>📁</span> أوراق وملفات الموظف الرسمية (خاص بالـ HR)
                                    </label>
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size: 0.72rem;">
                                        🔒 للموظف حق العرض فقط
                                    </span>
                                </div>
                                <p class="text-muted extra-small mb-3" style="font-size: 0.78rem;">
                                    هذه الملفات يرفعها ويتحكم فيها مسؤولو الموارد البشرية فقط، وتظهر للموظف في بروفايله الشخصي للاطلاع والتحميل فقط دون إمكانية التعديل.
                                </p>

                                @php
                                    $editDocs = [
                                        'national_id_card' => ['icon' => '🪪', 'title' => 'صورة بطاقة الرقم القومي', 'pick' => 'اختيار ملفات البطاقة'],
                                        'qualification_certificate' => ['icon' => '🎓', 'title' => 'صورة المؤهل الدراسي / الشهادة', 'pick' => 'اختيار ملفات المؤهل'],
                                        'other_document' => ['icon' => '📑', 'title' => 'وثائق وملفات أخرى', 'pick' => 'اختيار ملفات أخرى'],
                                    ];
                                @endphp

                                @php
                                    $canUploadDocs = Auth::user()->can('employees.documents_upload');
                                    $canDeleteDocs = Auth::user()->can('employees.documents_delete');
                                @endphp

                                @foreach($editDocs as $docKey => $meta)
                                    @php $docFiles = $user->documentFilesOf($docKey); @endphp
                                    <div class="p-3 bg-white rounded-3 border mb-3" id="doc_block_{{ $docKey }}">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="fw-bold text-slate-700 small d-flex align-items-center gap-2">
                                                <span>{{ $meta['icon'] }}</span> {{ $meta['title'] }}
                                            </span>
                                            <div id="status_{{ $docKey }}">
                                                @if($docFiles->isNotEmpty())
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.75rem;">
                                                        ✅ {{ $docFiles->count() }} {{ $docFiles->count() === 1 ? 'ملف' : 'ملفات' }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.75rem;">لم يتم الرفع</span>
                                                @endif
                                            </div>
                                        </div>

                                        @foreach($docFiles as $docFile)
                                            <div class="d-flex align-items-center gap-2 mb-2 p-2 bg-light rounded-3 border flex-wrap">
                                                <span class="flex-grow-1 small fw-semibold text-truncate" style="min-width: 120px;" title="{{ $docFile->original_name }}">
                                                    {{ $docFile->isImage() ? '🖼️' : '📕' }} {{ $docFile->original_name ?: ('ملف ' . strtoupper($docFile->extension())) }}
                                                </span>
                                                <a href="{{ $docFile->url() }}" target="_blank" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1" style="font-size: 0.8rem;">
                                                    <span>👁️</span> معاينة
                                                </a>
                                                <a href="{{ $docFile->downloadUrl() }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" style="font-size: 0.8rem;">
                                                    <span>📥</span> تحميل
                                                </a>
                                                @if($canDeleteDocs)
                                                    <button type="button" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1" style="font-size: 0.8rem;" onclick="deleteFromEdit('{{ $docKey }}', this, {{ $docFile->id }})">
                                                        <span>🗑️</span> حذف
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach

                                        @if($canUploadDocs)
                                            <label class="form-label extra-small text-muted mb-1" style="font-size: 0.75rem;">
                                                {{ $docFiles->isNotEmpty() ? 'إضافة ملفات أخرى (يمكن اختيار أكثر من ملف):' : $meta['pick'] . ' (يمكن اختيار أكثر من ملف):' }}
                                            </label>
                                            <input type="file" id="{{ $docKey }}" multiple accept="image/*,.pdf" class="form-control form-control-sm" style="border-radius: 8px;" onchange="uploadDocsFromEdit('{{ $docKey }}', this)">
                                            <div id="bar_{{ $docKey }}" class="mt-2 p-2 rounded-2 bg-light border d-none align-items-center justify-content-between flex-wrap gap-2">
                                                <span class="small text-primary fw-semibold" id="text_{{ $docKey }}"></span>
                                            </div>
                                            <div class="form-text extra-small text-muted" style="font-size: 0.72rem;">يقبل صور (JPG/PNG/WEBP) أو ملفات PDF (بحد أقصى 5 ميجا لكل ملف). يترفع الملف أول ما تختاره.</div>
                                        @else
                                            <div class="text-muted extra-small mt-2" style="font-size: 0.75rem;">🔒 رفع الملفات متاح للمصرح لهم فقط من لوحة التحكم.</div>
                                        @endif
                                    </div>
                                @endforeach

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

    function handleFileSelect(field, input) {
        const bar = document.getElementById('bar_' + field);
        const text = document.getElementById('text_' + field);
        if (!bar || !text) return;
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const sizeKb = (file.size / 1024).toFixed(1);
            text.innerHTML = `📄 تم اختيار: <b>${file.name}</b> (${sizeKb} KB)`;
            bar.classList.remove('d-none');
            bar.classList.add('d-flex');
            uploadDirectFromEdit(field);
        } else {
            bar.classList.add('d-none');
            bar.classList.remove('d-flex');
        }
    }

    async function uploadDocsFromEdit(field, input) {
        if (!input.files || !input.files.length) return;
        const files = Array.from(input.files);
        const bar = document.getElementById('bar_' + field);
        const text = document.getElementById('text_' + field);
        bar.classList.remove('d-none');
        bar.classList.add('d-flex');

        let okCount = 0;
        let lastError = null;

        for (let i = 0; i < files.length; i++) {
            text.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status"></span> جاري رفع الملف ${i + 1} من ${files.length}: <b>${files[i].name}</b> ⏳`;
            try {
                const f = await shrinkImageFile(files[i]);
                const formData = new FormData();
                formData.append('_token', "{{ csrf_token() }}");
                formData.append('type', field);
                formData.append('file', f, f.name);

                const res = await fetch("{{ route('employee_profiles.upload_document_ajax', $user->id) }}", {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    okCount++;
                } else {
                    lastError = data.message || 'فشل رفع الملف.';
                }
            } catch (err) {
                lastError = 'حدث خطأ في الاتصال: ' + err.message;
            }
        }

        input.value = '';

        if (okCount > 0) {
            text.innerHTML = `<span class="text-success fw-bold">✅ تم رفع ${okCount} ${okCount === 1 ? 'ملف' : 'ملفات'} بنجاح</span>` + (lastError ? ` <span class="text-danger">⚠️ ${lastError}</span>` : '');
            setTimeout(() => window.location.reload(), 900);
        } else {
            text.innerHTML = `<span class="text-danger fw-bold">⚠️ ${lastError || 'فشل رفع الملف.'}</span>`;
        }
    }

    function deleteFromEdit(field, btn, fileId) {
        if (!confirm('هل أنت متأكد من حذف هذا الملف؟')) return;
        btn.disabled = true;
        const formData = new FormData();
        formData.append('_token', "{{ csrf_token() }}");
        formData.append('type', field);
        if (fileId) formData.append('file_id', fileId);

        fetch("{{ route('employee_profiles.delete_document_ajax', $user->id) }}", {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                btn.disabled = false;
                alert(data.message || 'تعذّر حذف الملف.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            alert('حدث خطأ في الاتصال: ' + err.message);
        });
    }

    function shrinkImageFile(file) {
        return new Promise(resolve => {
            if (!file.type.startsWith('image/') || file.size < 1.5 * 1024 * 1024) return resolve(file);
            const img = new Image();
            const url = URL.createObjectURL(file);
            img.onload = () => {
                const max = 1800;
                const scale = Math.min(1, max / Math.max(img.width, img.height));
                const c = document.createElement('canvas');
                c.width = Math.round(img.width * scale);
                c.height = Math.round(img.height * scale);
                c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
                c.toBlob(b => {
                    URL.revokeObjectURL(url);
                    resolve(b ? new File([b], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }) : file);
                }, 'image/jpeg', 0.85);
            };
            img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
            img.src = url;
        });
    }

    function uploadDirectFromEdit(field) {
        const input = document.getElementById(field);
        if (!input || !input.files || !input.files[0]) {
            alert('يُرجى اختيار ملف أولاً.');
            return;
        }

        const bar = document.getElementById('bar_' + field);
        const text = document.getElementById('text_' + field);
        text.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status"></span> جاري رفع وحفظ الملف الآن... ⏳`;

        const formData = new FormData();
        formData.append('_token', "{{ csrf_token() }}");
        formData.append('type', field);

        shrinkImageFile(input.files[0]).then(f => {
            formData.append('file', f, f.name);
            return fetch("{{ route('employee_profiles.upload_document_ajax', $user->id) }}", {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData
            });
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                text.innerHTML = `<span class="text-success fw-bold">${data.message}</span>`;
                input.value = '';
                const statusBox = document.getElementById('status_' + field);
                if (statusBox) {
                    statusBox.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.75rem;">✅ مرفوعة (${data.extension})</span>`;
                }
                setTimeout(() => {
                    bar.classList.add('d-none');
                    bar.classList.remove('d-flex');
                }, 3500);
            } else {
                text.innerHTML = `<span class="text-danger fw-bold">⚠️ ${data.message}</span>`;
            }
        })
        .catch(err => {
            text.innerHTML = `<span class="text-danger fw-bold">⚠️ حدث خطأ في الاتصال: ${err.message}</span>`;
        });
    }
</script>

<style>
    .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }
    .text-primary { color: #2563eb !important; }
</style>
@endsection
