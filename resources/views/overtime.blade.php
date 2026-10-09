@extends('layout')
@section('title', 'Overtime Request')
@section('content')

@if($errors->any())
    <div class="max-w-md mx-auto mt-4 px-4">
        @foreach($errors->all() as $error)
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-3 text-sm mb-2 shadow-sm animate-pulse">
                <span>⚠️</span>
                {{ $error }}
            </div>
        @endforeach
    </div>
@endif

<div class="max-w-md mx-auto mt-28 px-4">
    <div class="bg-white border border-amber-100 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-3 opacity-5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-xs font-bold text-amber-600 uppercase tracking-widest mb-1">رصيد الإضافي اليومي</p>
                <h3 class="text-2xl font-black text-gray-800 tracking-tight">
                    {{ $otLimit - $otUsage }} <span class="text-sm font-medium text-gray-400">ساعة متبقية</span>
                </h3>
            </div>
            <div class="bg-amber-50 px-3 py-1 rounded-full border border-amber-100">
                <span class="text-xs font-bold text-amber-700">{{ $otUsage }} / {{ $otLimit }}</span>
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex justify-between text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                <span>الاستهلاك</span>
                <span>{{ round(($otUsage / $otLimit) * 100) }}%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                <div class="bg-amber-500 h-full transition-all duration-700 ease-out" 
                    style="width: {{ ($otUsage / $otLimit) * 100 }}%"></div>
            </div>
        </div>
        
        <p class="mt-4 text-[11px] text-gray-400 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            الحد الأقصى للإضافي المسموح به هو 5 ساعات يومياً.
        </p>
    </div>
</div>

<form class="max-w-md mx-auto mt-4" action="{{ route('overtime_post') }}" method="POST">
    @csrf
    <div class="grid md:grid-cols-2 md:gap-6">
        <div class="mb-3">
            <label for="date" class="block mb-2.5 text-sm font-medium text-heading">التاريخ</label>
            <input type="date" name="date" id="date" min="{{ $dateBounds['min'] }}" max="{{ $dateBounds['max'] }}" value="{{ old('date', $dateBounds['max']) }}" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" required />
            <p class="mt-1 text-xs text-body">Allowed dates: today or yesterday only.</p>
        </div>
        <div class="mb-3">
            <label for="day" class="block mb-2.5 text-sm font-medium text-heading">اليوم</label>
            <input type="text" name="day" id="day" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" required />
        </div>
    </div>

    @if(Auth::user()->email === 'guest@gamma.com')
        <div class="mb-3 mt-4">
            <label for="name" class="block mb-2.5 text-sm font-medium text-heading">الاسم</label>
            <input type="text" name="name" id="name" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs" required />
        </div>
    @else
        <div class="mb-3" hidden>
            <label for="name" class="block mb-2.5 text-sm font-medium text-heading">Name</label>
            <input type="text" name="name" id="name" value="{{ Auth::user()->name }}" hidden class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" required />
        </div>
        <div class="mb-3 mt-4" hidden>
            <label class="block mb-2.5 text-sm font-medium text-heading">الاسم</label>
            <input type="text" value="{{ Auth::user()->name }}" disabled class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base block w-full px-3 py-2.5 shadow-xs opacity-70" />
        </div>
    @endif


    <div class="mb-3" hidden>
        <label for="total_hours" class="block mb-2.5 text-sm font-medium text-heading">total Hours</label>
        <input type="number" name="total_hours" id="total_hours" readonly  class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" required />
    </div>

    <div class="mb-3">
        <label for="reason" class="block mb-2.5 text-sm font-medium text-heading">السبب</label>
        <input type="text" name="reason" id="reason" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" required />
    </div>

<div class="grid grid-cols-2 gap-3">
    <div class="mb-3">
        <label for="from" class="block mb-2 text-sm font-medium text-heading">من</label>
        <input
            type="text"
            name="from"
            id="from"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2 py-2 shadow-xs placeholder:text-body"
            placeholder="00:00 AM"
            required
        />
    </div>

    <div class="mb-3">
        <label for="to" class="block mb-2 text-sm font-medium text-heading">الى</label>
        <input
            type="text"
            name="to"
            id="to"
            class=" bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2 py-2 shadow-xs placeholder:text-body"
            placeholder="00:00 PM"
            required
        />
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const config = {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            altInput: true,
            altFormat: "h:i K",
            time_24hr: false,
            disableMobile: true, // يمنع استخدام الـ Native Picker على الموبايل لتسهيل الضغط
            locale: {
                amPM: ["ص", "م"]
            }
        };
        flatpickr("#from", config);
        flatpickr("#to", config);
    });
</script>

<button type="submit" class="text-white bg-gradient-to-l from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 focus:ring-4 focus:ring-blue-300 font-semibold rounded-xl text-sm px-5 py-2.5 shadow-md transition-all duration-200 border border-black">
    Submit
</button>

</form>



@endsection
