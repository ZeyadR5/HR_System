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

<form class="max-w-md mx-auto mt-40 bg-white shadow-md rounded-lg p-6 border border-gray-200" action="{{ route('check_in_out_post') }}" method="POST">
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

    <button type="submit" 
        @if(($hasCheckedIn && $hasCheckedOut) || !$isAuthorized) disabled @endif 
        class="w-full text-white @if(($hasCheckedIn && $hasCheckedOut) || !$isAuthorized) bg-gray-400 cursor-not-allowed @else bg-gradient-to-l from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 @endif focus:ring-4 focus:ring-blue-300 font-semibold rounded-xl text-lg px-5 py-3 shadow-md transition-all duration-200 border border-black">
        تأكيد (Submit)
    </button>

</form>

@endsection
