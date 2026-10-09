@extends('layout')
@section('title', 'Permission Request')
@section('content')

@if($errors->any())
    @foreach($errors->all() as $error)
        <div class="alert alert-danger">{{ $error }}</div>
    @endforeach
@endif

<div class="max-w-md mx-auto mt-28 px-4">
    <div class="bg-white border border-purple-100 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-3 opacity-5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-xs font-bold text-purple-600 uppercase tracking-widest mb-1">رصيد الأذونات الشهري</p>
                <h3 class="text-2xl font-black text-gray-800 tracking-tight">
                    @php
                        $remainingMinutes = max(0, $limitMinutes - $totalMinutes);
                        $h_rem = intdiv($remainingMinutes, 60);
                        $m_rem = $remainingMinutes % 60;
                    @endphp
                    {{ $h_rem }}س و {{ $m_rem }}د <span class="text-sm font-medium text-gray-400">متبقية</span>
                </h3>
            </div>
            <div class="bg-purple-50 px-3 py-1 rounded-full border border-purple-100">
                <span class="text-xs font-bold text-purple-700">
                    {{ intdiv($totalMinutes, 60) }}س {{ $totalMinutes % 60 }}د / 3س
                </span>
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex justify-between text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                <span>الاستهلاك</span>
                <span>{{ round(min(100, ($totalMinutes / $limitMinutes) * 100)) }}%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                <div class="bg-purple-500 h-full transition-all duration-700 ease-out" 
                    style="width: {{ min(100, ($totalMinutes / $limitMinutes) * 100) }}%"></div>
            </div>
        </div>
        
        @if($totalMinutes >= $limitMinutes)
            <div class="mt-4 p-3 bg-red-50 border border-red-100 rounded-xl">
                <p class="text-[11px] text-red-600 font-bold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    لقد تخطيت الحد المطلوب خلال الشهر ولازم تكلم المسؤول.
                </p>
            </div>
        @else
            <p class="mt-4 text-[11px] text-gray-400 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                الحد الأقصى للأذونات هو 3 ساعات شهرياً.
            </p>
        @endif
    </div>
</div>

<form class="max-w-md mx-auto mt-4" action="{{ route('permission_post') }}" method="POST">
    @csrf
    <div class="grid md:grid-cols-2 md:gap-6">
        <div class="mb-3">
            <label for="date" class="block mb-2.5 text-sm font-medium text-heading">التاريخ</label>
            <input type="date" name="date" id="date" min="{{ $dateBounds['min'] }}" max="{{ $dateBounds['max'] }}" value="{{ old('date', $dateBounds['max']) }}" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs" required />
            <p class="mt-1 text-xs text-body">Allowed dates: today or yesterday only.</p>
        </div>
        <div class="mb-3">
            <label for="day" class="block mb-2.5 text-sm font-medium text-heading">اليوم</label>
            <input type="text" name="day" id="day" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs" required />
        </div>
    </div>

    @if(Auth::user()->email === 'guest@gamma.com')
        <div class="mb-3 mt-4">
            <label for="name" class="block mb-2.5 text-sm font-medium text-heading">الاسم</label>
            <input type="text" name="name" id="name" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs" required />
        </div>
    @else
        <input type="hidden" name="name" value="{{ Auth::user()->name }}" />
        <div hidden class="mb-3 mt-4">
            <label class="block mb-2.5 text-sm font-medium text-heading">الاسم</label>
            <input type="text" value="{{ Auth::user()->name }}" disabled class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base block w-full px-3 py-2.5 shadow-xs opacity-70" />
        </div>
    @endif

    <div class="mb-3">
        <label for="permission_type" class="block mb-2.5 text-sm font-medium text-heading">نوع الاذن</label>
        <select name="permission_type" id="permission_type" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs" required>
            <option value="إذن تأخير">إذن تأخير</option>
            <option value="إذن انصراف باكر">إذن انصراف باكر</option>
            <option value="إذن نسيان بصمة حضور">إذن نسيان بصمة حضور</option>
            <option value="إذن نسيان بصمة انصراف">إذن نسيان بصمة انصراف</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="reason" class="block mb-2.5 text-sm font-medium text-heading">السبب</label>
        <input type="text" name="reason" id="reason" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs" required />
    </div>

<div id="timeFieldsSection" class="grid grid-cols-2 gap-3">
    <div class="mb-3">
        <label for="from" class="block mb-2 text-sm font-medium text-heading">من</label>
        <input
            type="text"
            name="from"
            id="from"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2 py-2 shadow-xs placeholder:text-body"
            placeholder="00:00 AM"
        />
    </div>

    <div class="mb-3">
        <label for="to" class="block mb-2 text-sm font-medium text-heading">الى</label>
        <input
            type="text"
            name="to"
            id="to"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2 py-2 shadow-xs placeholder:text-body"
            placeholder="00:00 PM"
        />
    </div>
</div>

<div id="eznDurationPreview" class="mb-3 text-xs font-semibold text-purple-600 hidden">
    ⏱️ المدة: <span id="eznDurationDisplay"></span>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateEznDuration() {
            const fromVal = document.getElementById('from').value;
            const toVal   = document.getElementById('to').value;
            const preview = document.getElementById('eznDurationPreview');
            const display = document.getElementById('eznDurationDisplay');

            if (!fromVal || !toVal) { preview?.classList.add('hidden'); return; }

            const [fh, fm] = fromVal.split(':').map(Number);
            const [th, tm] = toVal.split(':').map(Number);
            let totalMins = (th * 60 + tm) - (fh * 60 + fm);
            if (totalMins < 0) totalMins += 24 * 60;

            if (totalMins <= 0) { preview?.classList.add('hidden'); return; }

            const h = Math.floor(totalMins / 60);
            const m = totalMins % 60;
            let text = '';
            if (h > 0 && m > 0) text = `${h} ساعة و ${m} دقيقة`;
            else if (h > 0) text = `${h} ساعة`;
            else text = `${m} دقيقة`;

            if (display) display.textContent = text;
            preview?.classList.remove('hidden');
        }

        const config = {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            altInput: true,
            altFormat: "h:i K",
            time_24hr: false,
            locale: {
                amPM: ["ص", "م"]
            },
            onChange: function() {
                updateEznDuration();
            }
        };
        flatpickr("#from", config);
        flatpickr("#to", config);

        const typeSelect = document.getElementById('permission_type');
        const timeFields = document.getElementById('timeFieldsSection');

        function toggleTimeFields() {
            const val = typeSelect.value;
            if (val === 'إذن نسيان بصمة حضور' || val === 'إذن نسيان بصمة انصراف') {
                timeFields.classList.add('hidden');
                document.getElementById('eznDurationPreview')?.classList.add('hidden');
                // Clear values when hidden to avoid confusion
                if (document.querySelector('#from')._flatpickr) document.querySelector('#from')._flatpickr.clear();
                if (document.querySelector('#to')._flatpickr) document.querySelector('#to')._flatpickr.clear();
            } else {
                timeFields.classList.remove('hidden');
                updateEznDuration();
            }
        }

        typeSelect.addEventListener('change', toggleTimeFields);
        toggleTimeFields(); // Initial state
    });
</script>

<button type="submit" class="text-white bg-gradient-to-l from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 focus:ring-4 focus:ring-blue-300 font-semibold rounded-xl text-sm px-5 py-2.5 shadow-md transition-all duration-200 border border-black">
    Submit
</button>

</form>


@endsection
