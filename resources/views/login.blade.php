@extends('layout')
@section('title', 'تسجيل الدخول')
@section('content')

@if($errors->any())
    @foreach($errors->all() as $error)
        <div class="alert alert-danger max-w-sm mx-auto mb-4">
            {{ $error }}
        </div>
    @endforeach
@endif

<div class="max-w-sm mx-auto mt-16 px-4">
    <!-- Biometric Login Box (Shown on Native Mobile when fingerprint is registered) -->
    <div id="bioLoginSection" style="display:none; background:#0f172a; color:#ffffff; border:1px solid rgba(99,102,241,0.4);" class="mb-6 p-6 rounded-2xl text-center shadow-2xl">
        <div style="width:68px; height:68px; background:rgba(99,102,241,0.2); border:2px solid rgba(99,102,241,0.5); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; font-size:34px;">
            👆
        </div>
        <div style="font-size:19px; font-weight:700; color:#ffffff; margin-bottom:6px;">تسجيل الدخول بالبصمة</div>
        <p style="font-size:13px; color:#cbd5e1; margin-bottom:18px;" id="bioStatus">اضغط على الزر أدناه وضع بصمتك للدخول</p>
        <button type="button" id="bioLoginBtn" style="width:100%; padding:13px 24px; background:linear-gradient(135deg, #4f46e5, #7c3aed); color:#ffffff; font-weight:700; font-size:15px; border-radius:12px; border:0; cursor:pointer; box-shadow:0 4px 15px rgba(79,70,229,0.4);">
            👆 استخدام بصمة الإصبع
        </button>
        <div style="margin-top:14px;">
            <button type="button" id="bioForgetBtn" style="background:transparent; border:0; font-size:12px; color:#94a3b8; text-decoration:underline; cursor:pointer;">
                إلغاء البصمة المحفوظة لهذا الجهاز
            </button>
        </div>
    </div>

    <!-- Divider when Biometrics are active -->
    <div id="bioDivider" style="display:none;" class="relative flex py-2 items-center mb-6">
        <div class="flex-grow border-t border-gray-400"></div>
        <span class="flex-shrink mx-4 text-xs text-gray-500 font-medium">أو الدخول بكلمة المرور</span>
        <div class="flex-grow border-t border-gray-400"></div>
    </div>

    <!-- Standard Login Form -->
    <form id="loginForm" action="{{ route('login_post') }}" method="POST">
        @csrf
        <div class="mb-5">
            <label for="email" class="block mb-2.5 text-sm font-medium text-heading">البريد الإلكتروني (Email)</label>
            <input type="email" id="email" name="email"
                class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder-gray-400"
                placeholder="name@gamma.scan" required />
        </div>
        <div class="mb-5">
            <label for="password" class="block mb-2.5 text-sm font-medium text-heading">كلمة المرور (Password)</label>
            <input type="password" id="password" name="password"
                class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder-gray-400"
                placeholder="••••••••" required />
        </div>

        <!-- Option to enable Biometrics on next login (Native Mobile only) -->
        <div id="saveBioOption" style="display:none;" class="flex items-center gap-2 mb-5">
            <input type="checkbox" id="enable_biometric" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 bg-neutral-secondary-medium border-default-medium cursor-pointer" checked>
            <label for="enable_biometric" class="text-xs text-gray-600 select-none cursor-pointer">
                تفعيل الدخول ببصمة الإصبع مستقبلاً على هذا الجهاز
            </label>
        </div>

        <button type="submit" id="submitBtn" class="w-full text-white bg-gradient-to-l from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 focus:ring-4 focus:ring-blue-300 font-semibold rounded-xl text-sm px-5 py-3 shadow-md transition-all duration-200 border border-black cursor-pointer">
            تسجيل الدخول
        </button>
    </form>
</div>

<script>
(function () {
    var isNative = !!(window.Capacitor && Capacitor.isNativePlatform && Capacitor.isNativePlatform());
    var bioSection = document.getElementById('bioLoginSection');
    var bioDivider = document.getElementById('bioDivider');
    var bioStatus  = document.getElementById('bioStatus');
    var bioBtn     = document.getElementById('bioLoginBtn');
    var forgetBtn  = document.getElementById('bioForgetBtn');
    var saveBioOpt = document.getElementById('saveBioOption');
    var form       = document.getElementById('loginForm');
    var emailInput = document.getElementById('email');
    var passInput  = document.getElementById('password');

    if (!isNative) return;

    var Bio = Capacitor.Plugins && Capacitor.Plugins.NativeBiometric;
    if (!Bio) return;

    Bio.isAvailable().then(function (result) {
        if (!result || !result.isAvailable) return;

        // دعم البصمة متاح على هذا الموبايل
        saveBioOpt.style.display = 'flex';

        var hasSaved = localStorage.getItem('has_bio_saved') === '1';
        if (hasSaved) {
            bioSection.style.display = 'block';
            bioDivider.style.display = 'flex';

            // طلب البصمة فوراً عبر نافذة النظام
            triggerBioAuth();
        }

        if (bioBtn) {
            bioBtn.onclick = function () {
                triggerBioAuth();
            };
        }

        if (forgetBtn) {
            forgetBtn.onclick = function () {
                if (confirm('هل أنت متأكد من إلغاء البصمة المحفوظة لهذا الجهاز؟')) {
                    Bio.deleteCredentials({ server: 'gamma.hrsystem' }).finally(function () {
                        localStorage.removeItem('has_bio_saved');
                        bioSection.style.display = 'none';
                        bioDivider.style.display = 'none';
                    });
                }
            };
        }
    }).catch(function (e) {
        console.warn('Biometrics check error:', e);
    });

    function triggerBioAuth() {
        if (!Bio) return;
        bioStatus.textContent = 'ضع إصبعك على حساس البصمة الآن...';

        // 1. إظهار نافذة البصمة الرسمية للنظام أولاً للتأكد من بصمة الموظف
        Bio.verifyIdentity({
            title: 'تسجيل الدخول بالبصمة',
            subtitle: 'ضع بصمة إصبعك لتسجيل الدخول',
            reason: 'تأكيد الهوية للمتابعة',
            maxAttempts: 3,
            useFallback: true
        })
        .then(function () {
            // 2. بعد نجاح البصمة فقط، نجلب البيانات ونسجل الدخول
            bioStatus.textContent = 'تم التحقق من البصمة بنجاح! جاري الدخول...';
            return Bio.getCredentials({ server: 'gamma.hrsystem' });
        })
        .then(function (creds) {
            if (creds && creds.username && creds.password) {
                emailInput.value = creds.username;
                passInput.value = creds.password;
                form.submit();
            } else {
                bioStatus.textContent = 'لم يتم العثور على بيانات محفوظة، يرجى كتابة كلمة المرور';
            }
        })
        .catch(function (err) {
            console.log('Biometric prompt canceled/failed:', err);
            bioStatus.textContent = 'تم إلغاء طلب البصمة. يمكنك المحاولة مجدداً أو كتابة كلمة المرور';
        });
    }

    form.addEventListener('submit', function () {
        var chk = document.getElementById('enable_biometric');
        if (chk && chk.checked) {
            var em = emailInput.value.trim();
            var pw = passInput.value;
            if (em && pw) {
                sessionStorage.setItem('pending_bio_save', JSON.stringify({
                    username: em,
                    password: pw
                }));
            }
        } else {
            sessionStorage.removeItem('pending_bio_save');
        }
    });
})();
</script>
@endsection
