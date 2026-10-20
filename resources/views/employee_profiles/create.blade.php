@extends('layout')

@section('title', 'إضافة موظف جديد (ذكية)')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-9 col-xl-8">
            
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold mb-1" style="color: #0f172a; font-size: 1.6rem;">⚡ إضافة موظف جديد (ذكية)</h2>
                    <p class="text-muted small mb-0">اكتب اسم الموظف وسيقوم النظام بتوليد البريد الإلكتروني وكلمة السر تلقائياً!</p>
                </div>
                <a href="{{ route('employee_profiles.index') }}" class="btn btn-outline-secondary px-3" style="border-radius: 10px; font-weight: 600;">
                    ← كل الموظفين
                </a>
            </div>

            {{-- التنبيهات ونبذة عن الموظف المُنشأ --}}
            @if(session('created_user'))
                @php $created = session('created_user'); @endphp
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 18px; background: linear-gradient(135deg, #064e3b 0%, #065f46 100%); color: #fff;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                            <div>
                                <span class="badge bg-light text-success mb-2 px-3 py-1" style="font-size: .8rem; border-radius: 999px;">🎉 تم إنشاء الحساب بنجاح</span>
                                <h4 class="fw-bold mb-1" style="color: #fff;">{{ $created['name'] }}</h4>
                                <p class="mb-0 text-emerald-200 small">الفئة: {{ $created['role_name'] }} | الوظيفة: {{ $created['job_title'] }}</p>
                            </div>
                            <button type="button" class="btn btn-light btn-sm fw-bold px-3 py-2" id="btn-copy-creds" style="border-radius: 10px; color: #065f46;">
                                📋 نسخ بيانات الدخول للواتساب
                            </button>
                        </div>
                        <div class="row g-2 p-3 rounded-3" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(6px); font-family: monospace; font-size: .95rem;">
                            <div class="col-12 col-md-6">📧 <b>الإيميل:</b> <span id="copy-email">{{ $created['email'] }}</span></div>
                            <div class="col-12 col-md-6">🔑 <b>كلمة السر:</b> <span id="copy-pass">{{ $created['password'] }}</span></div>
                        </div>
                    </div>
                </div>
            @elseif(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4 rounded-3">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-3">
                    ⛔ {{ $errors->first() }}
                </div>
            @endif

            {{-- الكارت الرئيسي للإدخال --}}
            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 20px; background: #fff;">
                <div class="card-body p-4 p-md-5">
                    
                    <form action="{{ route('employee_profiles.store') }}" method="POST" id="create-user-form">
                        @csrf

                        {{-- 1. اسم الموظف --}}
                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold text-slate-800" style="font-size: .95rem;">
                                👤 اسم الموظف بالكامل *
                            </label>
                            <input type="text" name="name" id="name" required class="form-control form-control-lg @error('name') is-invalid @enderror"
                                   placeholder="مثال: أحمد محمد محمود" value="{{ old('name') }}"
                                   style="border-radius: 12px; font-size: 1rem; padding: .75rem 1rem;">
                            <div class="form-text text-muted">اكتب الاسم بالعربي أو الإنجليزي وسيتم اقتراح بريد إلكتروني تلقائياً.</div>
                        </div>

                        {{-- 2. الإيميل و كلمة السر التلقائية --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-7">
                                <label for="email" class="form-label fw-bold text-slate-800" style="font-size: .95rem;">
                                    📧 البريد الإلكتروني *
                                </label>
                                <div class="input-group">
                                    <input type="email" name="email" id="email" required class="form-control form-control-lg @error('email') is-invalid @enderror"
                                           placeholder="ahmed.mohamed@gama.com" value="{{ old('email') }}" dir="ltr" style="text-align: left; border-radius: 12px 0 0 12px; font-size: .95rem;">
                                    <button class="btn btn-outline-secondary" type="button" id="btn-re-gen-email" title="إعادة توليد الإيميل" style="border-radius: 0 12px 12px 0;">
                                        🔄 اقتراح
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-md-5">
                                <label for="password" class="form-label fw-bold text-slate-800" style="font-size: .95rem;">
                                    🔑 كلمة السر *
                                </label>
                                <div class="input-group">
                                    <input type="text" name="password" id="password" required class="form-control form-control-lg @error('password') is-invalid @enderror"
                                           placeholder="123456" value="{{ old('password', '123456') }}" dir="ltr" style="text-align: left; border-radius: 12px 0 0 12px; font-size: .95rem;">
                                    <button class="btn btn-outline-secondary" type="button" id="btn-rand-pass" title="توليد كلمة سر عشوائية" style="border-radius: 0 12px 12px 0;">
                                        🎲 123456
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- 3. الوظيفة وقيمة الساعة --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label for="job_title" class="form-label fw-bold text-slate-800" style="font-size: .95rem;">
                                    💼 المسمى الوظيفي
                                </label>
                                <select name="job_title" id="job_title" class="form-select form-select-lg" style="border-radius: 12px; font-size: .95rem;">
                                    <option value="">— اختر المسمى الوظيفي —</option>
                                    <option value="Rec" {{ old('job_title') == 'Rec' ? 'selected' : '' }}>استقبال (Rec)</option>
                                    <option value="Nur" {{ old('job_title') == 'Nur' ? 'selected' : '' }}>تمريض (Nur)</option>
                                    <option value="Tec" {{ old('job_title') == 'Tec' ? 'selected' : '' }}>فني اشعة (Tec)</option>
                                    <option value="Acc" {{ old('job_title') == 'Acc' ? 'selected' : '' }}>حسابات (Acc)</option>
                                    <option value="Super_V" {{ old('job_title') == 'Super_V' ? 'selected' : '' }}>مشرف (Super_V)</option>
                                    <option value="IT" {{ old('job_title') == 'IT' ? 'selected' : '' }}>(IT Tecnicall Support)</option>
                                    <option value="IT Manager" hidden {{ old('job_title') == 'IT' ? 'selected' : '' }}>(IT Manager)</option>
                                    <option value="Call_Center" {{ old('job_title') == 'Call_Center' ? 'selected' : '' }}>كول سنتر (Call_Center)</option>
                                    <option value="آخر" {{ old('job_title') == 'آخر' ? 'selected' : '' }}>آخر</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="hourly_rate" class="form-label fw-bold text-slate-800" style="font-size: .95rem;">
                                    ⏱️ قيمة الساعة (ج.م)
                                </label>
                                <input type="number" step="0.01" name="hourly_rate" id="hourly_rate" class="form-control form-control-lg"
                                       placeholder="0.00" value="{{ old('hourly_rate', '0.00') }}"
                                       style="border-radius: 12px; font-size: .95rem;">
                            </div>
                        </div>

                        {{-- 4. فئة الموظف --}}
                        <div class="mb-4">
                            <label for="role" class="form-label fw-bold text-slate-800" style="font-size: .95rem;">
                                🛡️ فئة الموظف والصلاحيات *
                            </label>
                            <select name="role" id="role" required class="form-select form-select-lg" style="border-radius: 12px; font-size: .95rem;">
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}" {{ old('role', 'user') == $r->name ? 'selected' : '' }}>
                                        {{ $r->display_name }} — ({{ $r->description ?: $r->name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 5. كارت المعاينة الفورية --}}
                        <div class="p-3 mb-4 rounded-3 border" id="preview-card" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-primary-subtle text-primary">👁️ معاينة سريعة للحساب</span>
                            </div>
                            <div class="row g-2 small text-slate-700">
                                <div class="col-12 col-sm-6">الاسم: <b id="pv-name">...</b></div>
                                <div class="col-12 col-sm-6">الإيميل: <b id="pv-email" dir="ltr">...</b></div>
                                <div class="col-12 col-sm-6">كلمة السر: <b id="pv-pass" dir="ltr">123456</b></div>
                                <div class="col-12 col-sm-6">الفئة: <b id="pv-role">...</b></div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold py-3" style="border-radius: 14px; background: linear-gradient(135deg, #2563eb, #1d4ed8); border: 0;">
                                ✨ إنشاء حساب الموظف الآن
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
(function() {
    // خريطة تحويل الحروف العربية لإنجليزي سليم
    const arMap = {
        'أ':'a','إ':'a','آ':'a','ا':'a','ب':'b','ت':'t','ث':'th','ج':'j','ح':'h','خ':'kh',
        'د':'d','ذ':'dh','ر':'r','ز':'z','س':'s','ش':'sh','ص':'s','ض':'d','ط':'t','ظ':'z',
        'ع':'a','غ':'gh','ف':'f','ق':'q','ك':'k','ل':'l','م':'m','ن':'n','ه':'h','و':'w',
        'ي':'y','ى':'a','ة':'h','ء':'a','ئ':'e','ؤ':'o'
    };

    function transliterate(str) {
        let clean = str.trim().toLowerCase();
        let result = '';
        for (let i = 0; i < clean.length; i++) {
            let ch = clean[i];
            if (arMap[ch]) {
                result += arMap[ch];
            } else if (/[a-z0-9]/i.test(ch)) {
                result += ch;
            } else if (/\s/.test(ch)) {
                result += '.';
            }
        }
        // تنظيف النقاط المتكررة
        return result.replace(/\.+/g, '.').replace(/^\.|\.$/g, '');
    }

    const nameInp = document.getElementById('name');
    const emailInp = document.getElementById('email');
    const passInp = document.getElementById('password');
    const roleSel = document.getElementById('role');

    let isEmailManuallyEdited = false;
    let typingTimer;

    emailInp.addEventListener('input', () => { isEmailManuallyEdited = true; updatePreview(); });

    async function autoGenEmail() {
        const val = nameInp.value.trim();
        if (!val) return;
        let slug = transliterate(val);
        if (!slug) return;

        let parts = slug.split('.').filter(Boolean);
        let emailHandle = '';
        if (parts.length >= 2) {
            emailHandle = parts[0] + '.' + parts[1];
        } else if (parts.length === 1) {
            emailHandle = parts[0];
        } else {
            emailHandle = 'employee';
        }

        let baseEmail = emailHandle + '@gamma.com';

        // Check availability dynamically
        try {
            const response = await fetch('{{ route("employee_profiles.check_email") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email: baseEmail })
            });
            const data = await response.json();
            emailInp.value = data.email || baseEmail;
        } catch (e) {
            emailInp.value = baseEmail;
        }

        updatePreview();
    }

    nameInp.addEventListener('input', () => {
        clearTimeout(typingTimer);
        if (!isEmailManuallyEdited || !emailInp.value) {
            typingTimer = setTimeout(autoGenEmail, 400); // 400ms debounce
        } else {
            updatePreview();
        }
    });

    document.getElementById('btn-re-gen-email').addEventListener('click', () => {
        isEmailManuallyEdited = false;
        autoGenEmail();
    });

    document.getElementById('btn-rand-pass').addEventListener('click', () => {
        const presets = ['123456', '12345678', 'Gama@123', Math.floor(100000 + Math.random() * 900000)];
        passInp.value = presets[Math.floor(Math.random() * presets.length)];
        updatePreview();
    });

    passInp.addEventListener('input', updatePreview);
    roleSel.addEventListener('change', updatePreview);

    function updatePreview() {
        document.getElementById('pv-name').textContent = nameInp.value || '...';
        document.getElementById('pv-email').textContent = emailInp.value || '...';
        document.getElementById('pv-pass').textContent = passInp.value || '123456';
        document.getElementById('pv-role').textContent = roleSel.options[roleSel.selectedIndex]?.text || '...';
    }

    // نسخ البيانات عند إنشاء الحساب
    const btnCopy = document.getElementById('btn-copy-creds');
    if (btnCopy) {
        btnCopy.addEventListener('click', () => {
            const email = document.getElementById('copy-email').innerText;
            const pass = document.getElementById('copy-pass').innerText;
            const name = "{{ session('created_user.name') ?? '' }}";
            const text = `مرحباً ${name} 👋\nتم إنشاء حسابك في نظام HR System بنجاح:\n\n📧 البريد: ${email}\n🔑 كلمة السر: ${pass}\n\nرابط تسجيل الدخول:\n${window.location.origin}/login`;

            navigator.clipboard.writeText(text).then(() => {
                btnCopy.innerText = '✅ تم النسخ بنجاح!';
                setTimeout(() => btnCopy.innerText = '📋 نسخ بيانات الدخول للواتساب', 3000);
            });
        });
    }

    updatePreview();
})();
</script>

<style>
    .bg-primary-subtle { background-color: rgba(37, 99, 235, 0.1) !important; }
    .text-primary { color: #2563eb !important; }
</style>
@endsection
