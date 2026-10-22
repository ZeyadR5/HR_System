@extends('layout')

@section('title', 'ملفات الموظفين')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold text-slate-800 mb-1" style="color: #1e293b;">👤 ملفات الموظفين</h2>
                    <p class="text-muted small mb-0">إدارة المسمى الوظيفي وقيمة الشيفت لكل موظف</p>
                </div>
                
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('employee_profiles.archive') }}" class="btn btn-outline-dark fw-bold px-3 py-2 d-inline-flex align-items-center gap-2 shadow-xs" style="border-radius: 10px; background: #ffffff;" title="عرض سجلات ووثائق الموظفين المؤرشفين">
                        <span>🗄️ أرشيف الموظفين</span>
                        @php $archivedCount = \App\Models\User::onlyTrashed()->count(); @endphp
                        @if($archivedCount > 0)
                            <span class="badge bg-danger text-white rounded-pill px-2 py-1" style="font-size: 0.75rem;">{{ $archivedCount }}</span>
                        @endif
                    </a>
                    @can('employees.create')
                    <a href="{{ route('employee_profiles.create') }}" class="btn btn-primary fw-bold px-3 py-2" style="border-radius: 10px; background: linear-gradient(135deg, #2563eb, #1d4ed8); border: 0;">
                        ⚡ إضافة موظف جديد
                    </a>
                    @endcan
                    <form action="{{ route('employee_profiles.lock_module') }}" method="POST" class="d-inline mb-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary px-3 py-2 fw-semibold" style="border-radius: 10px;" title="إعادة قفل ملفات الموظفين">
                            🔒 قفل
                        </button>
                    </form>
                </div>

                <form action="{{ route('employee_profiles.index') }}" method="GET" class="d-flex gap-2 w-100 w-md-auto align-items-end flex-wrap">
                    @if(request('sort_by'))
                        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                        <input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}">
                    @endif

                    <div style="min-width: 170px;">
                        <label class="text-muted small mb-1 d-block">التأمين الاجتماعي:</label>
                        <select name="insured" id="insured" class="form-select" style="border-radius: 8px; height: 38px; font-size: 0.88rem;" onchange="this.form.submit()">
                            <option value="">جميع الحالات (الكل)</option>
                            <option value="1" {{ request('insured') === '1' ? 'selected' : '' }}>🛡️ مؤمن عليهم فقط</option>
                            <option value="0" {{ request('insured') === '0' ? 'selected' : '' }}>⚠️ غير مؤمن عليهم فقط</option>
                        </select>
                    </div>

                    <div style="min-width: 180px;">
                        <label class="text-muted small mb-1 d-block">فلترة بالوظيفة:</label>
                        <select name="job_title" id="job_title" class="form-select" style="border-radius: 8px; height: 38px; font-size: 0.88rem;" onchange="this.form.submit()">
                            <option value="">جميع الوظائف (الكل)</option>
                            @foreach($jobTitles as $jt)
                                <option value="{{ $jt }}" {{ request('job_title') == $jt ? 'selected' : '' }}>
                                    {{ $jt }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="min-width: 200px;">
                        <label class="text-muted small mb-1 d-block">بحث بموظف معين:</label>
                        <select name="user_id" id="user_id" class="user-search-select" onchange="this.form.submit()">
                            <option value="">كل الموظفين</option>
                            @foreach($allUsers as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} {{ $u->job_title ? '(' . $u->job_title . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(request('user_id') || request('job_title') || request('sort_by') || request()->filled('insured'))
                        <a href="{{ route('employee_profiles.index') }}" class="btn btn-outline-secondary" title="إلغاء الفلتر والترتيب" style="border-radius: 8px; height: 38px; display: flex; align-items: center;">✖ إعادة ضبط</a>
                    @endif
                </form>
            </div>

            <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
            <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    new TomSelect(".user-search-select", {
                        create: false,
                        sortField: { field: "text", direction: "asc" },
                        placeholder: "اختر موظف...",
                        allowEmptyOption: true,
                    });
                });
            </script>

            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center" role="alert">
                    <span class="fs-5 me-2">✅</span>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 16px;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-slate-600">
                            <tr>
                                <th class="px-4 py-3 text-end">
                                    @php
                                        $nextDir = (request('sort_by') === 'name' && request('sort_dir', 'asc') === 'asc') ? 'desc' : 'asc';
                                    @endphp
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_dir' => $nextDir]) }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1 fw-bold">
                                        الموظف
                                        @if(request('sort_by') === 'name' || !request('sort_by'))
                                            <span style="font-size: 0.8rem; color: #2563eb;">{{ request('sort_dir') === 'desc' ? '▼' : '▲' }}</span>
                                        @else
                                            <span class="text-muted" style="opacity: 0.4;">↕</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-end">الايميل</th>
                                <th class="px-4 py-3 text-end">
                                    @php
                                        $nextJobDir = (request('sort_by') === 'job_title' && request('sort_dir') === 'asc') ? 'desc' : 'asc';
                                    @endphp
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'job_title', 'sort_dir' => $nextJobDir]) }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1 fw-bold">
                                        الوظيفة / المسمى الوظيفي
                                        @if(request('sort_by') === 'job_title')
                                            <span style="font-size: 0.8rem; color: #2563eb;">{{ request('sort_dir') === 'desc' ? '▼' : '▲' }}</span>
                                        @else
                                            <span class="text-muted" style="opacity: 0.4;">↕</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-center" style="min-width: 130px;">
                                    @php
                                        $nextInsuredDir = (request('sort_by') === 'is_insured' && request('sort_dir') === 'asc') ? 'desc' : 'asc';
                                    @endphp
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'is_insured', 'sort_dir' => $nextInsuredDir]) }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1 fw-bold">
                                        🛡️ مؤمن عليه
                                        @if(request('sort_by') === 'is_insured')
                                            <span style="font-size: 0.8rem; color: #2563eb;">{{ request('sort_dir') === 'desc' ? '▼' : '▲' }}</span>
                                        @else
                                            <span class="text-muted" style="opacity: 0.4;">↕</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                @php
                                    $queryParams = request()->only(['page', 'job_title', 'user_id', 'sort_by', 'sort_dir', 'insured']);
                                    $isHighlighted = session('highlight_user_id') == $user->id;
                                @endphp
                                <tr id="user-row-{{ $user->id }}" class="{{ $isHighlighted ? 'table-warning' : '' }}" style="{{ $isHighlighted ? 'background-color: #fef9c3 !important; box-shadow: inset 0 0 0 2px #f59e0b; transition: all 0.5s;' : '' }}">
                                    <td class="px-4 py-3 fw-semibold text-slate-700">
                                        <div class="d-flex align-items-center gap-3">
                                            <a href="{{ route('employee_profiles.show', array_merge(['id' => $user->id], $queryParams)) }}" class="text-decoration-none flex-shrink-0" title="عرض بروفايل {{ $user->name }}">
                                                <img src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}" class="rounded-circle shadow-xs" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #e2e8f0; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                                            </a>
                                            <div>
                                                <div class="d-flex align-items-center gap-1">
                                                    <a href="{{ route('employee_profiles.show', array_merge(['id' => $user->id], $queryParams)) }}" class="text-decoration-none text-slate-800 fw-bold" style="transition: color 0.2s;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#1e293b'">
                                                        {{ $user->name }}
                                                    </a>
                                                    @if($user->evaluation === 'A')
                                                        <span class="badge bg-warning text-dark px-1.5 py-0.5 rounded-pill small" title="تقييم A (ممتاز)" style="font-size: 0.7rem;">⭐ A</span>
                                                    @elseif($user->evaluation === 'B')
                                                        <span class="badge bg-primary text-white px-1.5 py-0.5 rounded-pill small" title="تقييم B (جيد جداً)" style="font-size: 0.7rem;">🥈 B</span>
                                                    @elseif($user->evaluation === 'C')
                                                        <span class="badge bg-secondary text-white px-1.5 py-0.5 rounded-pill small" title="تقييم C (متوسط)" style="font-size: 0.7rem;">🥉 C</span>
                                                    @endif
                                                    @if($isHighlighted)
                                                        <span class="badge bg-warning text-dark ms-1 small">✔</span>
                                                    @endif
                                                </div>
                                                @if($user->role && $user->role !== 'user')
                                                    <div class="text-muted" style="font-size: 0.72rem;">{{ $user->role }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-muted small">{{ $user->email }}</td>
                                    <td class="px-4 py-3">
                                        @if($user->job_title)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2" style="border-radius: 8px;">
                                                {{ $user->job_title }}
                                            </span>
                                        @else
                                            <span class="text-muted italic small">غير محدد</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-center" id="insurance-cell-{{ $user->id }}">
                                        <div class="d-inline-flex align-items-center justify-content-center">
                                            @if($user->is_insured)
                                                <span class="badge d-inline-flex align-items-center gap-1.5 px-3 py-1.5 text-success border border-success-subtle shadow-xs" 
                                                      style="border-radius: 9999px; font-size: 0.82rem; font-weight: 700; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); color: #065f46 !important; border-color: #10b981 !important; user-select: none;"
                                                      title="حالة التأمين: مؤمن عليه">
                                                    <span style="font-size: 0.95rem;">🛡️</span>
                                                    <span>مؤمن عليه</span>
                                                </span>
                                            @else
                                                <span class="badge d-inline-flex align-items-center gap-1.5 px-3 py-1.5 text-secondary border shadow-xs" 
                                                      style="border-radius: 9999px; font-size: 0.82rem; font-weight: 600; background: #f8fafc; color: #64748b !important; border-color: #cbd5e1 !important; border-style: dashed !important; user-select: none;"
                                                      title="حالة التأمين: غير مؤمن عليه">
                                                    <span style="font-size: 0.85rem; opacity: 0.7;">🛡️</span>
                                                    <span>غير مؤمن</span>
                                                </span>
                                            @endif

                                            {{-- Hidden checkbox element for backwards-compatibility & tests --}}
                                            <input type="checkbox" 
                                                   id="insurance_switch_{{ $user->id }}" 
                                                   class="d-none" 
                                                   {{ $user->is_insured ? 'checked' : '' }}
                                                   disabled>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <a href="{{ route('employee_profiles.show', array_merge(['id' => $user->id], $queryParams)) }}" class="btn btn-sm btn-outline-info px-3" style="border-radius: 8px;">
                                                👁️ عرض
                                            </a>
                                            @can('employees.edit_profiles')
                                            <a href="{{ route('employee_profiles.edit', array_merge(['id' => $user->id], $queryParams)) }}" class="btn btn-sm {{ $isHighlighted ? 'btn-primary' : 'btn-outline-primary' }} px-3" style="border-radius: 8px;">
                                                ✏️ تعديل
                                            </a>
                                            @if(auth()->id() !== $user->id && !$user->isDeveloper())
                                            <button type="button" class="btn btn-sm btn-outline-danger px-3" style="border-radius: 8px;" onclick="openDeleteModal({{ $user->id }}, '{{ addslashes($user->name) }}')" title="أرشفة ونقل الموظف للأرشيف بأمان">
                                                🗄️ أرشفة
                                            </button>
                                            @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <div class="mb-2 fs-3">{{ request('user_id') ? '🔍' : 'Empty' }}</div>
                                        @if(request('user_id'))
                                             لا توجد نتائج لهذا البحث
                                        @else
                                            لا يوجد موظفين حالياً
                                        @endif
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

<!-- Delete User Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 18px; overflow: hidden; background: #ffffff;">
            <div class="modal-header border-0 pb-0 pt-3 px-4 d-flex justify-content-between align-items-center w-100" style="direction: rtl;">
                <h5 class="modal-title fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2" id="deleteModalLabel" style="font-size: 1.15rem;">
                    <span>🗄️</span> <span>نقل الموظف إلى الأرشيف</span>
                </h5>
                <button type="button" class="modal-custom-close" onclick="closeDeleteModal()" aria-label="إغلاق">
                    ✕
                </button>
            </div>
            <form id="deleteForm" method="POST" action="">
                @csrf
                <input type="hidden" name="return_page" value="{{ request('page') }}">
                <input type="hidden" name="return_job_title" value="{{ request('job_title') }}">
                <input type="hidden" name="return_user_id" value="{{ request('user_id') }}">
                <input type="hidden" name="return_sort_by" value="{{ request('sort_by') }}">
                <input type="hidden" name="return_sort_dir" value="{{ request('sort_dir') }}">
                <div class="modal-body text-center pt-3 pb-4 px-4">
                    <div class="alert alert-info py-2 px-3 small border-0 mb-3 text-start d-flex align-items-start gap-2" style="border-radius: 10px; background: #eff6ff; color: #1e40af;">
                        <span class="fs-5">💡</span>
                        <div>
                            <strong>حفظ كامل للبيانات:</strong> لن يتم فقدان أي بيانات، حضور، جزاءات، حوافز، أو وثائق. سيتم حفظ الموظف في صفحة <strong>أرشيف الموظفين</strong> مع إمكانية مراجعة ملفاته واسترجاع حسابه في أي وقت.
                        </div>
                    </div>
                    <p class="text-muted mb-3 fs-6">أدخل كلمة مرور النظام لتأكيد أرشفة حساب الموظف <strong id="deleteEmployeeName" class="text-primary"></strong></p>
                    <div class="form-group text-start">
                        <input type="password" name="system_password" id="delete_system_password" class="form-control form-control-lg text-center fw-bold" required placeholder="كلمة مرور النظام" style="border-radius: 12px; letter-spacing: 3px; font-size: 1.2rem;">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-semibold" onclick="closeDeleteModal()" style="border-radius: 10px; min-width: 90px;">إلغاء</button>
                    <button type="submit" class="btn btn-danger px-4 fw-bold" style="border-radius: 10px; background: linear-gradient(135deg, #475569, #1e293b); border: 0; min-width: 150px;">🗄️ تأكيد الأرشفة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openDeleteModal(userId, userName) {
        document.getElementById('deleteEmployeeName').innerText = userName;
        const passInput = document.getElementById('delete_system_password');
        if (passInput) passInput.value = '';

        document.getElementById('deleteForm').action = "/employee-profiles/" + userId + "/delete";

        var modalEl = document.getElementById('deleteModal');
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var myModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl, { backdrop: true, keyboard: true });
            myModal.show();
        } else {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            modalEl.removeAttribute('aria-hidden');
            modalEl.setAttribute('aria-modal', 'true');
            if (!document.querySelector('.modal-backdrop')) {
                const bd = document.createElement('div');
                bd.className = 'modal-backdrop fade show';
                document.body.appendChild(bd);
            }
            document.body.classList.add('modal-open');
        }

        setTimeout(() => { if (passInput) passInput.focus(); }, 300);
    }

    function closeDeleteModal() {
        var modalEl = document.getElementById('deleteModal');
        if (!modalEl) return;

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var myModal = bootstrap.Modal.getInstance(modalEl);
            if (myModal) {
                myModal.hide();
            }
        }

        modalEl.classList.remove('show');
        modalEl.style.display = 'none';
        modalEl.setAttribute('aria-hidden', 'true');
        modalEl.removeAttribute('aria-modal');
        document.body.classList.remove('modal-open');

        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(b => b.remove());

        const passInput = document.getElementById('delete_system_password');
        if (passInput) passInput.value = '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        var delModalEl = document.getElementById('deleteModal');
        if (delModalEl) {
            delModalEl.addEventListener('click', function(e) {
                if (e.target === delModalEl) {
                    closeDeleteModal();
                }
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.key === 'Esc') {
                closeDeleteModal();
            }
        });

        @if(session('highlight_user_id'))
        setTimeout(function() {
            var el = document.getElementById('user-row-{{ session('highlight_user_id') }}');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 150);
        @endif
    });

    /**
     * الحفظ الفوري لحالة التأمين بالزر الكرييتف المريح والأنيق
     */
    function toggleCreativeInsurance(btn) {
        if (btn.disabled || btn.classList.contains('is-loading')) return;

        const userId = btn.dataset.userId;
        const userName = btn.dataset.userName || 'الموظف';
        const currentInsured = btn.dataset.insured === '1';
        const nextInsured = !currentInsured;

        const text = document.getElementById('insurance_text_' + userId);
        const hiddenCheckbox = document.getElementById('insurance_switch_' + userId);

        // وضع التحميل السلس
        btn.classList.add('is-loading');
        btn.disabled = true;
        const originalText = text ? text.innerText : '';
        if (text) text.innerText = 'جاري الحفظ...';

        fetch(`{{ url('/employee-profiles') }}/${userId}/toggle-insurance`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ is_insured: nextInsured ? 1 : 0 })
        })
        .then(async response => {
            const data = await response.json();
            btn.classList.remove('is-loading');
            btn.disabled = false;

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'حدث خطأ أثناء حفظ حالة التأمين.');
            }

            const isInsured = data.is_insured;
            btn.dataset.insured = isInsured ? '1' : '0';

            if (isInsured) {
                btn.classList.remove('is-not-insured');
                btn.classList.add('is-insured');
                if (text) text.innerText = 'مؤمن عليه';
                btn.title = 'مؤمن عليه — انقر للإلغاء';
                if (hiddenCheckbox) hiddenCheckbox.checked = true;
            } else {
                btn.classList.remove('is-insured');
                btn.classList.add('is-not-insured');
                if (text) text.innerText = 'غير مؤمن';
                btn.title = 'غير مؤمن — انقر لتعيينه كمؤمن عليه';
                if (hiddenCheckbox) hiddenCheckbox.checked = false;
            }

            // حركة اهتزاز/نبضة مبهجة تدل على النجاح
            btn.classList.add('animate-pop');
            setTimeout(() => btn.classList.remove('animate-pop'), 450);

            showInsuranceToast(data.message || (isInsured ? 'تم تأكيد التأمين للموظف' : 'تم إلغاء حالة التأمين'), 'success');
        })
        .catch(error => {
            btn.classList.remove('is-loading');
            btn.disabled = false;
            if (text) text.innerText = originalText;
            showInsuranceToast(error.message, 'danger');
        });
    }

    /**
     * إظهار إشعار عائم Toast أنيق ومريح في أسفل الشاشة
     */
    function showInsuranceToast(message, type = 'success') {
        let container = document.getElementById('insurance-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'insurance-toast-container';
            container.style.position = 'fixed';
            container.style.bottom = '25px';
            container.style.left = '25px';
            container.style.zIndex = '99999';
            container.style.display = 'flex';
            container.style.flexDirection = 'column';
            container.style.gap = '10px';
            container.style.direction = 'rtl';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} shadow-lg py-2 px-3 d-flex align-items-center gap-2 mb-0`;
        toast.style.borderRadius = '14px';
        toast.style.fontSize = '0.9rem';
        toast.style.minWidth = '280px';
        toast.style.boxShadow = '0 12px 30px rgba(0,0,0,0.18)';
        toast.style.transition = 'all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1)';
        toast.style.transform = 'translateY(20px) scale(0.95)';
        toast.style.opacity = '0';
        toast.innerHTML = `<span>${type === 'success' ? '🛡️' : '⚠️'}</span> <span class="fw-semibold">${message}</span>`;
        container.appendChild(toast);

        requestAnimationFrame(() => {
            toast.style.transform = 'translateY(0) scale(1)';
            toast.style.opacity = '1';
        });

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(15px) scale(0.95)';
            setTimeout(() => toast.remove(), 350);
        }, 3500);
    }
</script>

@if($errors->has('system_password'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        alert("{{ $errors->first('system_password') }}");
    });
</script>
@endif

<style>
    /* زر التأمين الكرييتف التفاعلي المريح */
    .creative-insurance-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 4px 14px 4px 6px;
        border-radius: 9999px;
        font-size: 0.82rem;
        font-weight: 700;
        line-height: 1;
        cursor: pointer;
        border: 1.5px solid transparent;
        transition: all 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
        outline: none;
        background: transparent;
        user-select: none;
        text-decoration: none;
        height: 36px;
        box-sizing: border-box;
    }

    .creative-insurance-btn:active {
        transform: scale(0.93) !important;
    }

    .creative-insurance-btn.animate-pop {
        animation: popFeedback 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes popFeedback {
        0% { transform: scale(0.92); }
        50% { transform: scale(1.08); }
        100% { transform: scale(1); }
    }

    /* حالة: مؤمن عليه */
    .creative-insurance-btn.is-insured {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border-color: #10b981;
        color: #065f46;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.16), inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .creative-insurance-btn.is-insured:hover {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border-color: #059669;
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.26);
        transform: translateY(-1.5px);
    }

    /* حالة: غير مؤمن */
    .creative-insurance-btn.is-not-insured {
        background: #f8fafc;
        border: 1.5px dashed #cbd5e1;
        color: #64748b;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .creative-insurance-btn.is-not-insured:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
        color: #1e293b;
        transform: translateY(-1.5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .creative-insurance-btn[disabled] {
        opacity: 0.6;
        cursor: not-allowed !important;
        transform: none !important;
        box-shadow: none !important;
    }

    .creative-insurance-btn.is-loading {
        pointer-events: none;
        opacity: 0.75;
    }

    /* التراك الداخلي للمفتاح */
    .creative-toggle-track {
        width: 40px;
        height: 24px;
        border-radius: 9999px;
        position: relative;
        transition: background-color 0.28s ease;
        flex-shrink: 0;
        display: inline-block;
    }

    .is-insured .creative-toggle-track {
        background: #10b981;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .is-not-insured .creative-toggle-track {
        background: #cbd5e1;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    /* زرار التبديل الدائري Thumb */
    .creative-toggle-thumb {
        width: 20px;
        height: 20px;
        background: #ffffff;
        border-radius: 50%;
        position: absolute;
        top: 2px;
        right: 2px;
        transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
    }

    /* اتجاه الـ RTL: عند اليمين مؤمن عليه، وعند السحب لليسار غير مؤمن */
    .is-not-insured .creative-toggle-thumb {
        transform: translateX(-16px);
    }
    .is-insured .creative-toggle-thumb {
        transform: translateX(0);
    }

    .thumb-icon-shield {
        display: none;
        font-size: 11px;
        line-height: 1;
    }
    .thumb-icon-dash {
        display: none;
        font-size: 9px;
        color: #94a3b8;
        font-weight: 800;
        line-height: 1;
    }

    .is-insured .thumb-icon-shield {
        display: inline-block;
    }
    .is-not-insured .thumb-icon-dash {
        display: inline-block;
    }

    .creative-toggle-content {
        display: flex;
        align-items: center;
        gap: 6px;
        padding-left: 2px;
        white-space: nowrap;
    }

    .creative-pulse-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
        transition: all 0.25s ease;
    }

    .is-insured .creative-pulse-dot {
        background: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);
        animation: creativePulse 2s infinite;
    }

    .is-not-insured .creative-pulse-dot {
        background: #cbd5e1;
        box-shadow: none;
    }

    @keyframes creativePulse {
        0% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5);
        }
        70% {
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }

    .modal-custom-close {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: none;
        background: #f1f5f9;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        margin: 0 !important;
        padding: 0 !important;
        line-height: 1;
    }
    .modal-custom-close:hover {
        background: #e2e8f0;
        color: #0f172a;
        transform: scale(1.08);
    }
    .modal-custom-close:active {
        transform: scale(0.95);
    }
    .bg-primary-subtle { background-color: rgba(37, 99, 235, 0.1) !important; }
    .text-primary { color: #2563eb !important; }
    .table-hover tbody tr:hover { background-color: rgba(248, 250, 252, 0.8); }
    th { font-weight: 700; font-size: 0.85rem; letter-spacing: 0.5px; }
</style>
@endsection
