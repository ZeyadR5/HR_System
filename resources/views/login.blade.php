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

<div class="max-w-sm mx-auto mt-20 px-4">
    <!-- Biometric Login Box (Shown on Native Mobile when fingerprint is registered) -->
    <div id="bioLoginSection" style="display:none;" class="mb-6 p-5 rounded-2xl bg-slate-800/90 border border-indigo-500/40 text-center shadow-xl backdrop-blur-sm">
        <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-3xl shadow-inner">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004 11m0 0c0 .918.067 1.815.195 2.684m2.628 5.617A17.925 17.925 0 014 11"></path>
            </svg>
        </div>
        <div class="text-base font-bold text-white mb-1">تسجيل الدخول بالبصمة</div>
        <p class="text-xs text-gray-300 mb-4" id="bioStatus">اضغط أدناه أو استخدم بصمتك المسجلة للدخول الفوري</p>
        <button type="button" id="bioLoginBtn" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold shadow-lg transition-all active:scale-95 cursor-pointer">
            <span>دخول ببصمة الإصبع</span>
        </button>
        <div class="mt-3">
            <button type="button" id="bioForgetBtn" class="text-xs text-gray-400 hover:text-red-400 underline transition cursor-pointer">
                إلغاء البصمة المحفوظة لهذا الجهاز
            </button>
        </div>
    </div>

    <!-- Divider when Biometrics are active -->
    <div id="bioDivider" style="display:none;" class="relative flex py-2 items-center mb-6">
        <div class="flex-grow border-t border-gray-600"></div>
        <span class="flex-shrink mx-4 text-xs text-gray-400 font-medium">أو الدخول بكلمة المرور</span>
        <div class="flex-grow border-t border-gray-600"></div>
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
            <label for="enable_biometric" class="text-xs text-gray-300 select-none cursor-pointer">
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

        // Biometrics supported on this phone
        saveBioOpt.style.display = 'flex';

        var hasSaved = localStorage.getItem('has_bio_saved') === '1';
        if (hasSaved) {
            bioSection.style.display = 'block';
            bioDivider.style.display = 'flex';

            // Automatic prompt on first page load
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
        bioStatus.textContent = 'جاري التحقق من البصمة...';

        Bio.getCredentials({ server: 'gamma.hrsystem' })
            .then(function (creds) {
                if (creds && creds.username && creds.password) {
                    bioStatus.textContent = 'تم التعرف على البصمة! جاري الدخول...';
                    emailInput.value = creds.username;
                    passInput.value = creds.password;
                    form.submit();
                } else {
                    bioStatus.textContent = 'لم يتم العثور على بصمة مسجلة، أدخل كلمة المرور أدناه';
                }
            })
            .catch(function (err) {
                console.log('Biometric prompt canceled/failed:', err);
                bioStatus.textContent = 'يمكنك استخدام البصمة أو تسجيل الدخول بالباسورد أدناه';
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
