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
                    @can('employees.create')
                    <a href="{{ route('employee_profiles.create') }}" class="btn btn-primary fw-bold px-3 py-2" style="border-radius: 10px; background: linear-gradient(135deg, #2563eb, #1d4ed8); border: 0;">
                        ⚡ إضافة موظف جديد
                    </a>
                    @endcan
                </div>

                <form action="{{ route('employee_profiles.index') }}" method="GET" class="d-flex gap-2 w-100 w-md-auto align-items-end">
                    <div style="min-width: 250px;">
                        <label class="text-muted small mb-1 d-block">بحث بموظف معين:</label>
                        <select name="user_id" id="user_id" class="user-search-select" onchange="this.form.submit()">
                            <option value="">كل الموظفين</option>
                            @foreach($allUsers as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(request('user_id'))
                        <a href="{{ route('employee_profiles.index') }}" class="btn btn-outline-secondary" style="border-radius: 8px; height: 38px; display: flex; align-items: center;">✖</a>
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
                                <th class="px-4 py-3 text-end">الاسم</th>
                                <th class="px-4 py-3 text-end">الايميل</th>
                                <th class="px-4 py-3 text-end">الوظيفة</th>
                                <!-- <th class="px-4 py-3 text-end">قيمة الساعة</th> -->
                                <th class="px-4 py-3 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td class="px-4 py-3 fw-semibold text-slate-700">{{ $user->name }}</td>
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
                                    <!-- <td class="px-4 py-3 fw-bold text-info">
                                        {{ number_format($user->hourly_rate, 2) }} ج.م
                                    </td> -->
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" class="btn btn-sm btn-outline-info px-3 me-1" style="border-radius: 8px;" onclick="openPasswordModal({{ $user->id }}, '{{ addslashes($user->name) }}', 'show')">
                                            👁️ عرض
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary px-3" style="border-radius: 8px;" onclick="openPasswordModal({{ $user->id }}, '{{ addslashes($user->name) }}', 'edit')">
                                            ✏️ تعديل
                                        </button>
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

<!-- Password Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 18px; overflow: hidden; background: #ffffff;">
            <div class="modal-header border-0 pb-0 pt-3 px-4 d-flex justify-content-between align-items-center w-100" style="direction: rtl;">
                <h5 class="modal-title fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2" id="passwordModalLabel" style="font-size: 1.15rem;">
                    <span>🔒</span> <span>أمان النظام</span>
                </h5>
                <button type="button" class="modal-custom-close" onclick="closePasswordModal()" aria-label="إغلاق">
                    ✕
                </button>
            </div>
            <form id="passwordForm" method="POST" action="">
                @csrf
                <div class="modal-body text-center pt-3 pb-4 px-4">
                    <p class="text-muted mb-4 fs-6">أدخل كلمة مرور النظام ل<span id="modalActionText">تعديل</span> بروفايل <strong id="modalEmployeeName" class="text-primary"></strong></p>
                    <div class="form-group text-start">
                        <input type="password" name="system_password" id="modal_system_password" class="form-control form-control-lg text-center fw-bold" required placeholder="كلمة المرور" style="border-radius: 12px; letter-spacing: 3px; font-size: 1.2rem;">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-semibold" onclick="closePasswordModal()" style="border-radius: 10px; min-width: 90px;">إلغاء</button>
                    <button type="submit" class="btn btn-primary px-5 fw-bold" style="border-radius: 10px; background: linear-gradient(135deg, #2563eb, #1d4ed8); border: 0; min-width: 140px;">تأكيد الدخول</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openPasswordModal(userId, userName, actionType = 'edit') {
        document.getElementById('modalEmployeeName').innerText = userName;
        const passInput = document.getElementById('modal_system_password');
        if (passInput) passInput.value = '';

        if (actionType === 'show') {
            document.getElementById('modalActionText').innerText = 'عرض';
            document.getElementById('passwordForm').action = "/employee-profiles/" + userId + "/unlock-show";
        } else {
            document.getElementById('modalActionText').innerText = 'تعديل';
            document.getElementById('passwordForm').action = "/employee-profiles/" + userId + "/unlock-edit";
        }

        var modalEl = document.getElementById('passwordModal');
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

    function closePasswordModal() {
        var modalEl = document.getElementById('passwordModal');
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

        const passInput = document.getElementById('modal_system_password');
        if (passInput) passInput.value = '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = document.getElementById('passwordModal');
        if (modalEl) {
            modalEl.addEventListener('click', function(e) {
                if (e.target === modalEl) {
                    closePasswordModal();
                }
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.key === 'Esc') {
                closePasswordModal();
            }
        });
    });
</script>

@if($errors->has('system_password'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        alert("{{ $errors->first('system_password') }}");
    });
</script>
@endif

<style>
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
