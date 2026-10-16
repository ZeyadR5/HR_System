@extends('layout')
@section('title', 'تسجيل الدخول')
@section('content')

@if($errors->any())
    @foreach($errors->all() as $error)
        <div class="alert alert-danger">
            {{ $error }}
        </div>
    @endforeach
@endif

    <form class="max-w-sm mx-auto" action="{{ route('login_post') }}" method="POST">
        @csrf
        <div class="mb-5 mt-40">
            <label for="email" class="block mb-2.5 text-sm font-medium text-heading">Your email</label>
            <input type="email" id="email" name="email"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder-gray-400"
            placeholder="name@gamma.scan" required />
        </div>
        <div class="mb-5">
            <label for="password" class="block mb-2.5 text-sm font-medium text-heading">Your password</label>
            <input type="password" id="password" name="password"
                class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder-gray-400"
                placeholder="••••••••" required />
        </div>

<button type="submit" class="text-white bg-gradient-to-l from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 focus:ring-4 focus:ring-blue-300 font-semibold rounded-xl text-sm px-5 py-2.5 shadow-md transition-all duration-200 border border-black">
    Submit
</button>

</form>
@endsection
