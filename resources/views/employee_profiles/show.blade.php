@extends('layout')

@section('title', 'بروفايل الموظف - ' . $user->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">

            @if(Auth::user()->can('employees.view_profiles'))
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
                <div class="mb-3">
                    <a href="{{ $backUrl }}" class="btn btn-light btn-sm rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 fw-semibold border shadow-xs" style="background: #ffffff; color: #1e293b; border-color: #cbd5e1 !important;">
                        <span>⬅️</span>
                        <span>العودة لصفحة الموظفين</span>
                    </a>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-xs mb-3 border-0 d-flex align-items-center justify-content-between" role="alert" style="background: #dcfce7; color: #166534;">
                    <div class="d-flex align-items-center gap-2">
                        <span>✅</span>
                        <span class="fw-semibold">{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-xs mb-3 border-0 d-flex align-items-center justify-content-between" role="alert" style="background: #fee2e2; color: #991b1b;">
                    <div class="d-flex align-items-center gap-2">
                        <span>⚠️</span>
                        <span class="fw-semibold">{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-xs mb-3 border-0" role="alert" style="background: #fee2e2; color: #991b1b;">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $err)
                            <li class="fw-semibold">{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- 1. Employee Header & Annual Leave Balance Banner --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff;">
                <div class="card-body p-4">
                    <div class="row align-items-center g-4">
                        <div class="col-12 col-md-6 d-flex align-items-center gap-3">
                            <div class="position-relative d-inline-block" style="width: 120px; height: 120px; flex-shrink: 0;">
                                <div id="profile_avatar_wrapper" 
                                     class="rounded-circle overflow-hidden border border-3 border-white shadow-lg d-flex align-items-center justify-content-center position-relative profile-avatar-clickable" 
                                     style="width: 120px; height: 120px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); cursor: pointer; transition: transform 0.25s ease, box-shadow 0.25s ease;"
                                     title="انقر لعرض وتكبير الصورة بالكامل 🔍"
                                     onclick="openAvatarViewer()">
                                    <div id="avatar_spinner" class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column align-items-center justify-content-center bg-dark bg-opacity-75" style="z-index: 10;">
                                        <div class="spinner-border spinner-border-sm text-light" role="status"></div>
                                    </div>
                                    <div class="avatar-hover-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.35); opacity: 0; transition: opacity 0.2s; z-index: 5;">
                                        <span style="font-size: 1.6rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.6));">🔍</span>
                                    </div>
                                    @if($user->profile_picture)
                                        <img id="profile_avatar_img" src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <span id="profile_avatar_letter" class="text-white fw-bold" style="font-size: 3rem;">{{ mb_substr($user->name, 0, 1) }}</span>
                                    @endif
                                </div>

                                @if(Auth::id() === $user->id || Auth::user()->can('employees.avatar_manage'))
                                    {{-- Hidden avatar file input for instant upload --}}
                                    <input type="file" id="avatar_ajax_input" accept="image/png, image/jpeg, image/jpg, image/webp" style="display: none;" onchange="uploadAvatarDirect(this)">
                                    <button type="button" 
                                            class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 p-0 d-flex align-items-center justify-content-center shadow" 
                                            style="width: 36px; height: 36px; border: 3px solid #1e293b; z-index: 15;" 
                                            title="تغيير الصورة الشخصية فوراً" 
                                            onclick="event.stopPropagation(); document.getElementById('avatar_ajax_input').click()">
                                        <span style="font-size: 0.95rem;">📷</span>
                                    </button>
                                @endif
                            </div>
                            <div>
                                <h3 class="fw-bold mb-1 text-white">{{ $user->name }}</h3>
                                <div class="d-flex flex-wrap align-items-center gap-2 text-slate-300 small">
                                    <span>🏢 {{ $user->job_title ?? 'موظف' }}</span>
                                    <span>•</span>
                                    <span>✉️ {{ $user->email }}</span>
                                    @if(Auth::user()->can('employees.view_profiles') || Auth::id() === $user->id)
                                        <span>•</span>
                                        <span class="badge bg-info text-dark">⏱️ {{ number_format($user->hourly_rate, 2) }} ج.م/ساعة</span>
                                    @endif
                                    <span>•</span>
                                    @if($user->is_insured)
                                        <span class="badge bg-success text-white px-2 py-1 shadow-xs" style="font-size: 0.78rem;">🛡️ مؤمن عليه</span>
                                    @else
                                        <span class="badge bg-secondary text-light px-2 py-1 shadow-xs" style="font-size: 0.78rem; opacity: 0.85;">غير مؤمن عليه</span>
                                    @endif
                                    @if($user->basic_incentive > 0)
                                        <span>•</span>
                                        <span class="badge bg-warning text-dark px-2 py-1 shadow-xs" style="font-size: 0.78rem;">🎁 حافز: {{ number_format($user->basic_incentive, 2) }} ج.م</span>
                                    @endif
                                    @if($user->hire_date)
                                        <span>•</span>
                                        <span class="badge bg-light text-dark px-2 py-1 shadow-xs" style="font-size: 0.78rem;">📅 بدء العمل: {{ \Carbon\Carbon::parse($user->hire_date)->format('d M Y') }}</span>
                                    @endif
                                    @if($user->evaluation)
                                        <span>•</span>
                                        @if($user->evaluation === 'A')
                                            <span class="badge text-dark px-2 py-1 shadow-xs" style="background: linear-gradient(135deg, #fef08a, #facc15); font-size: 0.78rem; font-weight: 700;">⭐ تقييم A (ممتاز)</span>
                                        @elseif($user->evaluation === 'B')
                                            <span class="badge text-white px-2 py-1 shadow-xs" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); font-size: 0.78rem; font-weight: 700;">🥈 تقييم B (جيد جداً)</span>
                                        @elseif($user->evaluation === 'C')
                                            <span class="badge text-white px-2 py-1 shadow-xs" style="background: linear-gradient(135deg, #64748b, #475569); font-size: 0.78rem; font-weight: 700;">🥉 تقييم C (متوسط)</span>
                                        @endif
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

            {{-- 1.2. HR Details Overview Strip --}}
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 16px; background: #ffffff;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: #fef3c7; color: #b45309; font-size: 1.3rem;">
                                🎁
                            </div>
                            <div>
                                <span class="text-muted small fw-semibold d-block" style="font-size: 0.78rem;">قيمة الحافز الأساسي</span>
                                <span class="fs-6 fw-bold text-dark">
                                    {{ $user->basic_incentive > 0 ? number_format($user->basic_incentive, 2) . ' ج.م' : '—' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 16px; background: #ffffff;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: #eff6ff; color: #1d4ed8; font-size: 1.3rem;">
                                📅
                            </div>
                            <div>
                                <span class="text-muted small fw-semibold d-block" style="font-size: 0.78rem;">تاريخ بدء العمل</span>
                                <span class="fs-6 fw-bold text-dark">
                                    @if($user->hire_date)
                                        {{ \Carbon\Carbon::parse($user->hire_date)->format('d M Y') }}
                                        <small class="text-muted fw-normal" style="font-size: 0.72rem;">({{ \Carbon\Carbon::parse($user->hire_date)->diffForHumans(null, true) }})</small>
                                    @else
                                        <span class="text-muted small fw-normal">غير محدد</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 16px; background: #ffffff;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: #fdf4ff; color: #7e22ce; font-size: 1.3rem;">
                                ⭐
                            </div>
                            <div>
                                <span class="text-muted small fw-semibold d-block" style="font-size: 0.78rem;">تقييم الموظف</span>
                                @if($user->evaluation === 'A')
                                    <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.8rem;">⭐ فئة A (ممتاز)</span>
                                @elseif($user->evaluation === 'B')
                                    <span class="badge bg-primary text-white px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.8rem;">🥈 فئة B (جيد جداً)</span>
                                @elseif($user->evaluation === 'C')
                                    <span class="badge bg-secondary text-white px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.8rem;">🥉 فئة C (متوسط)</span>
                                @else
                                    <span class="text-muted small fw-normal">بدون تقييم</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 1.5. Official Employee Documents Section (الوثائق والأوراق الرسمية) --}}
            @if(Auth::id() === $user->id || Auth::user()->can('employees.view_profiles'))
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; background: #ffffff;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-4">📁</span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">الأوراق والوثائق الرسمية</h5>
                            <small class="text-muted">مستندات الموظف الرسمية والمرفوعة والمعتمدة من قِبل إدارة الموارد البشرية (HR)</small>
                        </div>
                    </div>
                    @if(Auth::user()->can('employees.documents_upload') || Auth::user()->can('employees.edit_profiles'))
                        <a href="{{ route('employee_profiles.edit', $user->id) }}#documents-section" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-2 fw-semibold">
                            <span>✏️</span>
                            <span>إدارة / رفع الوثائق</span>
                        </a>
                    @endif
                </div>
                <div class="card-body p-4">
                    @php
                        $docsList = [
                            'national_id_card' => [
                                'title' => 'بطاقة الرقم القومي',
                                'icon'  => '🪪',
                                'desc'  => 'صورة بطاقة الرقم القومي (سارية)',
                                'color' => '#3b82f6',
                                'bg'    => '#eff6ff',
                            ],
                            'qualification_certificate' => [
                                'title' => 'صورة المؤهل الدراسي',
                                'icon'  => '🎓',
                                'desc'  => 'شهادة التخرج أو المؤهل الدراسي',
                                'color' => '#10b981',
                                'bg'    => '#ecfdf5',
                            ],
                            'other_document' => [
                                'title' => 'وثائق ومستندات أخرى',
                                'icon'  => '📑',
                                'desc'  => 'عقد العمل، شهادات خبرة أو أوراق إضافية',
                                'color' => '#8b5cf6',
                                'bg'    => '#f5f3ff',
                            ],
                        ];
                    @endphp

                    <div class="row g-3">
                        @foreach($docsList as $docKey => $docMeta)
                            @php
                                $files = $user->documentFilesOf($docKey);
                                $isUploaded = $files->isNotEmpty();
                                $canUploadDocs = Auth::user()->can('employees.documents_upload');
                                $canDeleteDocs = Auth::user()->can('employees.documents_delete');
                            @endphp
                            <div class="col-12 col-md-4" id="card_col_{{ $docKey }}">
                                @if($canUploadDocs)
                                    <input type="file" id="input_{{ $docKey }}" multiple accept="image/png, image/jpeg, image/jpg, image/webp, application/pdf" style="display: none;" onchange="uploadDocDirect('{{ $docKey }}', this)">
                                @endif

                                <div class="card h-100 border transition-all position-relative overflow-hidden" id="card_box_{{ $docKey }}" style="border-radius: 16px; border-color: {{ $isUploaded ? '#e2e8f0' : '#f1f5f9' }} !important; background: {{ $isUploaded ? '#ffffff' : '#fafafa' }};">
                                    {{-- Uploading overlay spinner --}}
                                    <div id="spinner_{{ $docKey }}" class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column align-items-center justify-content-center bg-white bg-opacity-95" style="z-index: 10;">
                                        <div class="spinner-border text-primary mb-2" role="status"></div>
                                        <span class="small fw-bold text-primary" id="spinner_text_{{ $docKey }}">جاري رفع وحفظ الملف... ⏳</span>
                                    </div>

                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div class="d-inline-flex align-items-center justify-content-center rounded-3 p-2" style="background: {{ $docMeta['bg'] }}; color: {{ $docMeta['color'] }}; font-size: 1.5rem; width: 45px; height: 45px;">
                                                    {{ $docMeta['icon'] }}
                                                </div>
                                                <div id="badge_{{ $docKey }}">
                                                    @if($isUploaded)
                                                        <span class="badge bg-success-subtle text-success px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 0.75rem;">
                                                            ✅ {{ $files->count() }} {{ $files->count() === 1 ? 'ملف' : 'ملفات' }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 0.75rem;">
                                                            ⏳ غير متوفر
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1">{{ $docMeta['title'] }}</h6>
                                            <p class="text-muted small mb-3" style="font-size: 0.82rem;">{{ $docMeta['desc'] }}</p>

                                            @forelse($files as $file)
                                                <div class="d-flex align-items-center gap-2 p-2 mb-2 rounded-3 border bg-light" id="file_row_{{ $file->id }}">
                                                    <a href="{{ $file->url() }}" target="_blank" class="flex-shrink-0 d-inline-flex align-items-center justify-content-center rounded-2 overflow-hidden border bg-white" style="width: 46px; height: 46px;">
                                                        @if($file->isImage())
                                                            <img src="{{ $file->url() }}" alt="{{ $docMeta['title'] }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                        @else
                                                            <span style="font-size: 1.5rem;">📕</span>
                                                        @endif
                                                    </a>
                                                    <div class="flex-grow-1 text-truncate small fw-semibold" title="{{ $file->original_name }}">
                                                        {{ $file->original_name ?: ('ملف ' . strtoupper($file->extension())) }}
                                                        <div class="text-muted" style="font-size: 0.7rem;">{{ strtoupper($file->extension()) }}</div>
                                                    </div>
                                                    <a href="{{ $file->url() }}" target="_blank" class="btn btn-light btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center border" style="width: 30px; height: 30px;" title="معاينة">👁️</a>
                                                    <a href="{{ $file->downloadUrl() }}" class="btn btn-primary btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px;" title="تحميل">📥</a>
                                                    @if($canDeleteDocs)
                                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px;" title="حذف الملف" onclick="deleteDocDirect('{{ $docKey }}', {{ $file->id }})">🗑️</button>
                                                    @endif
                                                </div>
                                            @empty
                                                <div class="mb-3 text-center rounded-3 p-3 border border-dashed text-muted d-flex flex-column align-items-center justify-content-center" style="height: 120px; background: #f8fafc;">
                                                    <span class="fs-4 opacity-50">📭</span>
                                                    <span class="extra-small text-muted mt-1" style="font-size: 0.75rem;">لم يتم رفع ملفات بعد</span>
                                                    <span class="extra-small text-muted opacity-75" style="font-size: 0.7rem;">(يُرفع حصراً بواسطة HR)</span>
                                                </div>
                                            @endforelse
                                        </div>

                                        <div class="mt-2 pt-2 border-top">
                                            @if($canUploadDocs)
                                                <button type="button" class="btn btn-primary btn-sm w-100 rounded-pill d-inline-flex align-items-center justify-content-center gap-2 fw-semibold shadow-xs" style="font-size: 0.85rem; padding: 7px 12px;" onclick="document.getElementById('input_{{ $docKey }}').click()">
                                                    <span>📤</span>
                                                    <span>{{ $isUploaded ? 'إضافة ملفات أخرى' : 'اختيار ورفع الملفات الآن' }}</span>
                                                </button>
                                            @elseif(!$isUploaded)
                                                <span class="text-muted extra-small d-block py-1 text-center" style="font-size: 0.78rem;">
                                                    🔒 متاح الرفع للإدارة فقط
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

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
    .profile-avatar-clickable:hover {
        transform: scale(1.04);
        box-shadow: 0 12px 30px -5px rgba(0, 0, 0, 0.45) !important;
    }
    .profile-avatar-clickable:hover .avatar-hover-overlay {
        opacity: 1 !important;
    }
</style>

{{-- Fullscreen Avatar Lightbox Modal --}}
<div id="avatarViewerModal" class="modal fade" tabindex="-1" aria-hidden="true" style="display: none; z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 650px;">
        <div class="modal-content border-0 shadow-lg text-white" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(12px); border-radius: 20px; overflow: hidden; border: 1px solid rgba(255,255,255,0.15) !important;">
            <div class="modal-header border-0 pb-0 pt-3 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-5">👤</span>
                    <h6 class="modal-title fw-bold mb-0 text-white" id="avatarViewerTitle">الصورة الشخصية — {{ $user->name }}</h6>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a id="avatarViewerDownloadBtn" href="{{ $user->profile_picture ? $user->profile_picture_url : '#' }}" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1 shadow-xs" title="فتح الصورة بالحجم الكامل">
                        <span>↗️</span>
                        <span class="small fw-semibold">الحجم الأصلي</span>
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
            </div>
            <div class="modal-body p-4 text-center d-flex flex-column align-items-center justify-content-center">
                <div id="avatarModalImgContainer" class="rounded-4 overflow-hidden shadow-lg border border-2 border-white border-opacity-25 bg-black" style="max-width: 100%; max-height: 72vh; display: inline-flex; align-items: center; justify-content: center;">
                    <img id="avatarViewerImg" src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}" style="max-width: 100%; max-height: 72vh; object-fit: contain; display: {{ $user->profile_picture ? 'block' : 'none' }}; border-radius: 12px;">
                    <div id="avatarViewerPlaceholder" style="width: 240px; height: 240px; display: {{ $user->profile_picture ? 'none' : 'flex' }}; align-items: center; justify-content: center; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 20px;">
                        <span class="text-white fw-bold" style="font-size: 6rem;">{{ mb_substr($user->name, 0, 1) }}</span>
                    </div>
                </div>
                <div class="mt-3 text-slate-300 small d-flex align-items-center justify-content-center gap-3">
                    <span>🏢 {{ $user->job_title ?? 'موظف' }}</span>
                    <span>•</span>
                    <span>✉️ {{ $user->email }}</span>
                    @if($user->is_insured)
                        <span>•</span>
                        <span class="badge bg-success text-white px-2 py-0.5">🛡️ مؤمن عليه</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

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

<script>
    const UPLOAD_URL = "{{ route('employee_profiles.upload_document_ajax', $user->id) }}";
    const DELETE_URL = "{{ route('employee_profiles.delete_document_ajax', $user->id) }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";
    const USER_ID    = "{{ $user->id }}";

    function showDocToast(msg, isSuccess = true) {
        let toast = document.getElementById('docGlobalToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'docGlobalToast';
            toast.style.cssText = 'position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 99999; padding: 12px 28px; border-radius: 50px; font-weight: bold; font-size: 0.95rem; box-shadow: 0 10px 30px rgba(0,0,0,0.25); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); pointer-events: none; text-align: center;';
            document.body.appendChild(toast);
        }
        toast.style.background = isSuccess ? '#10b981' : '#ef4444';
        toast.style.color = '#ffffff';
        toast.innerHTML = msg;
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(10px)';
            setTimeout(() => toast.style.display = 'none', 300);
        }, 4500);
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

    function openAvatarViewer() {
        const avatarImg = document.getElementById('profile_avatar_img');
        const viewerImg = document.getElementById('avatarViewerImg');
        const placeholder = document.getElementById('avatarViewerPlaceholder');
        const downloadBtn = document.getElementById('avatarViewerDownloadBtn');

        if (avatarImg && avatarImg.src && !avatarImg.src.includes('data:image/svg')) {
            if (viewerImg) {
                viewerImg.src = avatarImg.src;
                viewerImg.style.display = 'block';
            }
            if (placeholder) placeholder.style.display = 'none';
            if (downloadBtn) {
                downloadBtn.href = avatarImg.src;
                downloadBtn.style.display = 'inline-flex';
            }
        } else {
            if (viewerImg) viewerImg.style.display = 'none';
            if (placeholder) placeholder.style.display = 'flex';
            if (downloadBtn) downloadBtn.style.display = 'none';
        }

        const modalEl = document.getElementById('avatarViewerModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    function uploadAvatarDirect(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const spinner = document.getElementById('avatar_spinner');
        if (spinner) spinner.classList.remove('d-none');

        const formData = new FormData();
        formData.append('_token', CSRF_TOKEN);
        formData.append('type', 'profile_picture');

        shrinkImageFile(file).then(f => {
            formData.append('file', f, f.name);
            return fetch(UPLOAD_URL, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData
            });
        })
        .then(res => res.json())
        .then(data => {
            if (spinner) spinner.classList.add('d-none');
            if (data.success) {
                showDocToast(data.message || '✅ تم تحديث الصورة الشخصية بنجاح!', true);
                const wrapper = document.getElementById('profile_avatar_wrapper');
                if (wrapper) {
                    wrapper.innerHTML = `
                        <div id="avatar_spinner" class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column align-items-center justify-content-center bg-dark bg-opacity-75" style="z-index: 10;">
                            <div class="spinner-border spinner-border-sm text-light" role="status"></div>
                        </div>
                        <div class="avatar-hover-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.35); opacity: 0; transition: opacity 0.2s; z-index: 5;">
                            <span style="font-size: 1.6rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.6));">🔍</span>
                        </div>
                        <img id="profile_avatar_img" src="${data.avatar_url}?t=${Date.now()}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    `;
                }
                const viewerImg = document.getElementById('avatarViewerImg');
                const placeholder = document.getElementById('avatarViewerPlaceholder');
                const downloadBtn = document.getElementById('avatarViewerDownloadBtn');
                if (viewerImg) {
                    viewerImg.src = `${data.avatar_url}?t=${Date.now()}`;
                    viewerImg.style.display = 'block';
                }
                if (placeholder) placeholder.style.display = 'none';
                if (downloadBtn) {
                    downloadBtn.href = data.avatar_url;
                    downloadBtn.style.display = 'inline-flex';
                }
                const headerAvatar = document.querySelector('.header-avatar');
                if (headerAvatar && "{{ Auth::id() }}" === USER_ID) {
                    headerAvatar.innerHTML = `<img src="${data.avatar_url}?t=${Date.now()}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">`;
                }
                const sideAvatar = document.querySelector('.sidepanel-user-avatar');
                if (sideAvatar && "{{ Auth::id() }}" === USER_ID) {
                    sideAvatar.innerHTML = `<img src="${data.avatar_url}?t=${Date.now()}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">`;
                }
            } else {
                showDocToast(data.message || '⚠️ فشل في رفع الصورة.', false);
            }
            input.value = '';
        })
        .catch(err => {
            if (spinner) spinner.classList.add('d-none');
            showDocToast('⚠️ حدث خطأ أثناء الاتصال: ' + err.message, false);
            input.value = '';
        });
    }

    async function uploadDocDirect(docKey, input) {
        if (!input.files || !input.files.length) return;
        const files = Array.from(input.files);
        const spinner = document.getElementById('spinner_' + docKey);
        const spinnerText = document.getElementById('spinner_text_' + docKey);
        if (spinner) spinner.classList.remove('d-none');

        let okCount = 0;
        let lastError = null;

        for (let i = 0; i < files.length; i++) {
            if (spinnerText) spinnerText.textContent = `جاري رفع الملف ${i + 1} من ${files.length}... ⏳`;
            try {
                const f = await shrinkImageFile(files[i]);
                const formData = new FormData();
                formData.append('_token', CSRF_TOKEN);
                formData.append('type', docKey);
                formData.append('file', f, f.name);

                const res = await fetch(UPLOAD_URL, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    okCount++;
                } else {
                    lastError = data.message || '⚠️ فشل رفع الملف.';
                }
            } catch (err) {
                lastError = '⚠️ حدث خطأ في الاتصال: ' + err.message;
            }
        }

        input.value = '';

        if (okCount > 0) {
            showDocToast(`✅ تم رفع ${okCount} ${okCount === 1 ? 'ملف' : 'ملفات'} بنجاح!` + (lastError ? ' (مع وجود أخطاء في بعضها)' : ''), true);
            setTimeout(() => window.location.reload(), 700);
        } else {
            if (spinner) spinner.classList.add('d-none');
            showDocToast(lastError || '⚠️ فشل رفع الملف.', false);
        }
    }

    function deleteDocDirect(docKey, fileId) {
        if (!confirm('هل أنت متأكد من حذف هذا الملف نهائياً؟')) return;
        const spinner = document.getElementById('spinner_' + docKey);
        if (spinner) spinner.classList.remove('d-none');

        const formData = new FormData();
        formData.append('_token', CSRF_TOKEN);
        formData.append('type', docKey);
        formData.append('file_id', fileId);

        fetch(DELETE_URL, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showDocToast(data.message || '🗑️ تم حذف الملف بنجاح!', true);
                setTimeout(() => window.location.reload(), 500);
            } else {
                if (spinner) spinner.classList.add('d-none');
                showDocToast(data.message || '⚠️ فشل حذف الملف.', false);
            }
        })
        .catch(err => {
            if (spinner) spinner.classList.add('d-none');
            showDocToast('⚠️ حدث خطأ في الاتصال: ' + err.message, false);
        });
    }
</script>
@endsection
