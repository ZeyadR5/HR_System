@extends('layout')
@section('title', 'Leave Request')
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
    <div class="grid grid-cols-2 gap-4 mb-6">
        <!-- Monthly Balance -->
        <div class="bg-white border border-blue-100 rounded-2xl p-4 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-2 opacity-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-1">هذا الشهر</p>
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-black text-gray-800">{{ $monthLimit - $monthUsage }}</span>
                <span class="text-xs text-gray-400">/ {{ $monthLimit }} يوم باقي</span>
            </div>
            <div class="mt-2 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-blue-500 h-full transition-all duration-500" style="width: {{ ($monthUsage / $monthLimit) * 100 }}%"></div>
            </div>
        </div>

        <!-- Yearly Balance -->
        <div class="bg-white border border-indigo-100 rounded-2xl p-4 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-2 opacity-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-1">الرصيد السنوي</p>
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-black text-gray-800">{{ $yearLimit - $yearUsage }}</span>
                <span class="text-xs text-gray-400">/ {{ $yearLimit }} يوم باقي</span>
            </div>
            <div class="mt-2 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-indigo-500 h-full transition-all duration-500" style="width: {{ ($yearUsage / $yearLimit) * 100 }}%"></div>
            </div>
        </div>
    </div>
</div>

<form class="max-w-md mx-auto mt-2" action="{{ route('leave_post') }}" method="POST">
    @csrf

    @if(Auth::user()->email === 'guest@gamma.com')
        <div class="mb-3 mt-4">
            <label for="name" class="block mb-2.5 text-sm font-medium text-heading">الاسم</label>
            <input type="text" name="name" id="name" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs" required />
        </div>
    @else
        <input type="hidden" name="name" value="{{ Auth::user()->name }}" />
        <div hidden class="mb-3 mt-4">
            <label class="block mb-2.5 text-sm font-medium text-heading ">الاسم</label>
            <input type="text" value="{{ Auth::user()->name }}" disabled class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base block w-full px-3 py-2.5 shadow-xs opacity-70" />
        </div>
    @endif

    <div class="grid md:grid-cols-2 md:gap-6">
        <div class="mb-3">
            <label for="date" class="block mb-2.5 text-sm font-medium text-heading">التاريخ</label>
            <input type="text" name="date" id="date" value="{{ old('date', $dateBounds['max']) }}" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs" required placeholder="YYYY-MM-DD" />
            <p class="mt-1 text-xs text-body">Allowed dates: today or yesterday only.</p>
        </div>
        <div class="mb-3">
            <label for="day" class="block mb-2.5 text-sm font-medium text-heading">اليوم</label>
            <input type="text" name="day" id="day" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs" required />
        </div>
    </div>


    <div class="mb-3">
        <label for="reason" class="block mb-2.5 text-sm font-medium text-heading">السبب</label>
        <input type="text" name="reason" id="reason" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs" required />
    </div>

    <div class="mb-3">
        <label for="substitute" class="block mb-2.5 text-sm font-medium text-heading">الشخص البديل</label>
        <input type="text" name="substitute" id="substitute" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs" required />
    </div>

    {{--
    <div class="mb-3" hidden>
        <label for="days_count" class="block mb-2.5 text-sm font-medium text-heading">الايام</label>
        <input type="number" name="days_count" id="days_count_visible" min="1" value="1" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs" required />
    </div>
    --}}
    <input type="hidden" name="days_count" id="days_count" value="1" required />

<button type="submit" class="text-white bg-gradient-to-l from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 focus:ring-4 focus:ring-blue-300 font-semibold rounded-xl text-sm px-5 py-2.5 shadow-md transition-all duration-200 border border-black">
    Submit
</button>

</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const disabledDates = @json($takenLeaves ?? []);
        
        flatpickr("#date", {
            dateFormat: "Y-m-d",
            minDate: "{{ $dateBounds['min'] }}",
            maxDate: "{{ $dateBounds['max'] }}",
            disable: disabledDates,
            defaultDate: "{{ old('date', $dateBounds['max']) }}"
        });
    });
</script>



@endsection
