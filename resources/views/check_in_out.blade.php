@extends('layout')
@section('title', 'تسجيل حضور أو انصراف')
@section('content')

@if($errors->any())
    @foreach($errors->all() as $error)
        <div class="alert alert-danger">{{ $error }}</div>
    @endforeach
@endif

@if(session('success'))
    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
        <span class="font-medium">{{ session('success') }}</span>
    </div>
@endif

<form id="attendanceForm" class="max-w-md mx-auto mt-40 bg-white shadow-md rounded-lg p-6 border border-gray-200" action="{{ route('check_in_out_post') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <h2 class="text-xl font-bold mb-6 text-center text-gray-800">تسجيل الحركة</h2>

    @if(Auth::user()->email === 'guest@gamma.com')
        <div class="mb-5">
            <label for="name" class="block mb-2.5 text-sm font-medium text-heading">الاسم</label>
            <input type="text" name="name" id="name" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs" required />
        </div>
    @else
        <div class="mb-5">
            <label class="block mb-2.5 text-sm font-medium text-heading">الاسم</label>
            <input type="text" value="{{ Auth::user()->name }}" disabled class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base block w-full px-3 py-2.5 shadow-xs opacity-70" />
        </div>
    @endif

    <div class="mb-6">
        <label class="block mb-3 text-sm font-medium text-heading">نوع الحركة</label>
        <div class="flex gap-6 items-center">
            <div class="flex items-center">
                <input id="type_in" type="radio" value="حضور" name="type" class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500" required @if($hasCheckedIn) disabled @endif>
                <label for="type_in" class="ms-2 text-md font-medium @if($hasCheckedIn) text-gray-400 @else text-gray-900 @endif">
                    حضور @if($hasCheckedIn) (تم التسجيل ✅) @endif
                </label>
            </div>
            <div class="flex items-center">
                <input id="type_out" type="radio" value="انصراف" name="type" class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500" required @if($hasCheckedOut) disabled @endif>
                <label for="type_out" class="ms-2 text-md font-medium @if($hasCheckedOut) text-gray-400 @else text-gray-900 @endif">
                    انصراف @if($hasCheckedOut) (تم التسجيل ✅) @endif
                </label>
            </div>
        </div>
    </div>

    @if($hasCheckedIn && $hasCheckedOut)
        <div class="p-3 mb-6 text-sm text-blue-800 rounded-lg bg-blue-50 text-center font-semibold border border-blue-200">
            لقد أتممت تسجيل الحضور والانصراف لهذا اليوم.
        </div>
    @endif

    @php
        $isAuthorized = Auth::user()->can('checkinout.self_register');
    @endphp

    @if(!$isAuthorized)
        <div class="p-3 mb-6 text-sm text-red-800 rounded-lg bg-red-50 text-center font-semibold border border-red-200">
            عفواً، لا تملك صلاحية تسجيل الحضور أو الانصراف.
        </div>
    @endif

    <input type="hidden" name="latitude" id="att_lat">
    <input type="hidden" name="longitude" id="att_lng">
    <input type="hidden" name="source" id="att_source" value="web">
    <input type="file" name="selfie" id="att_selfie" accept="image/*" capture="user" class="hidden">

    <div id="geoBox" class="p-3 mb-4 text-sm rounded-lg bg-gray-50 border border-gray-200 text-gray-700 text-center">
        📍 جاري تحديد موقعك...
    </div>

    <div id="selfieBox" class="mb-4 text-center" style="display:none;">
        <img id="selfiePreview" alt="selfie" class="mx-auto rounded-xl border border-gray-200 mb-2" style="max-height:140px; display:none;">
        <button type="button" id="selfieBtn" class="text-sm font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg px-4 py-2">
            📸 التقاط صورة سيلفي للإثبات
        </button>
    </div>

    <button type="submit" 
        @if(($hasCheckedIn && $hasCheckedOut) || !$isAuthorized) disabled @endif 
        class="w-full text-white @if(($hasCheckedIn && $hasCheckedOut) || !$isAuthorized) bg-gray-400 cursor-not-allowed @else bg-gradient-to-l from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 @endif focus:ring-4 focus:ring-blue-300 font-semibold rounded-xl text-lg px-5 py-3 shadow-md transition-all duration-200 border border-black">
        تأكيد (Submit)
    </button>

</form>

<script>
(function () {
    const cfg = @json($geofence);
    const cap = window.Capacitor;
    const isApp = !!(cap && cap.isNativePlatform && cap.isNativePlatform());
    const plugins = (cap && cap.Plugins) || {};

    const form = document.getElementById('attendanceForm');
    const geoBox = document.getElementById('geoBox');
    const latEl = document.getElementById('att_lat');
    const lngEl = document.getElementById('att_lng');
    const fileEl = document.getElementById('att_selfie');
    const selfieBox = document.getElementById('selfieBox');
    const selfieBtn = document.getElementById('selfieBtn');
    const preview = document.getElementById('selfiePreview');

    document.getElementById('att_source').value = isApp ? 'app' : 'web';
    if (cfg.require_selfie) selfieBox.style.display = 'block';

    function setGeo(html, cls) {
        geoBox.className = 'p-3 mb-4 text-sm rounded-lg border text-center ' + (cls || 'bg-gray-50 border-gray-200 text-gray-700');
        geoBox.innerHTML = html;
    }

    async function getPosition() {
        if (isApp && plugins.Geolocation) {
            try { await plugins.Geolocation.requestPermissions(); } catch (e) {}
            const p = await plugins.Geolocation.getCurrentPosition({ enableHighAccuracy: true, timeout: 15000 });
            return { lat: p.coords.latitude, lng: p.coords.longitude };
        }
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) return reject(new Error('unsupported'));
            navigator.geolocation.getCurrentPosition(
                p => resolve({ lat: p.coords.latitude, lng: p.coords.longitude }),
                reject,
                { enableHighAccuracy: true, timeout: 15000 }
            );
        });
    }

    async function locate() {
        setGeo('📍 جاري تحديد موقعك...');
        try {
            const pos = await getPosition();
            latEl.value = pos.lat;
            lngEl.value = pos.lng;
            setGeo('✅ تم تحديد موقعك بنجاح' + (cfg.enabled ? ' (النطاق المسموح ' + cfg.radius + ' متر من الشركة)' : ''), 'bg-green-50 border-green-200 text-green-800');
        } catch (e) {
            setGeo('⚠️ تعذّر تحديد الموقع. فعّل الـ GPS واسمح للتطبيق بالوصول للموقع <button type="button" id="geoRetry" class="underline font-semibold">إعادة المحاولة</button>', 'bg-red-50 border-red-200 text-red-800');
            document.getElementById('geoRetry')?.addEventListener('click', locate);
        }
    }

    async function takeSelfie() {
        if (isApp && plugins.Camera) {
            try {
                const photo = await plugins.Camera.getPhoto({
                    quality: 70, width: 800, resultType: 'dataUrl', source: 'CAMERA', direction: 'FRONT', saveToGallery: false
                });
                const blob = await (await fetch(photo.dataUrl)).blob();
                const file = new File([blob], 'selfie.jpg', { type: blob.type || 'image/jpeg' });
                const dt = new DataTransfer();
                dt.items.add(file);
                fileEl.files = dt.files;
                preview.src = photo.dataUrl;
                preview.style.display = 'block';
            } catch (e) { /* المستخدم ألغى التصوير */ }
            return;
        }
        fileEl.click();
    }

    fileEl.addEventListener('change', () => {
        const f = fileEl.files[0];
        if (f) {
            preview.src = URL.createObjectURL(f);
            preview.style.display = 'block';
        }
    });
    selfieBtn.addEventListener('click', takeSelfie);

    form.addEventListener('submit', function (e) {
        if (cfg.enabled && (!latEl.value || !lngEl.value)) {
            e.preventDefault();
            alert('لا يمكن تسجيل الحضور قبل تحديد موقعك الجغرافي.');
            return;
        }
        const type = form.querySelector('input[name="type"]:checked');
        if (cfg.require_selfie && type && type.value === 'حضور' && !fileEl.files.length) {
            e.preventDefault();
            alert('يجب التقاط صورة سيلفي لإثبات الحضور.');
        }
    });

    locate();
})();
</script>

@endsection
