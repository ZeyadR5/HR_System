@extends('layout')
@section('title', 'إضافة حافز / تقييم')
@section('content')

<div class="max-w-lg mx-auto mt-20">
    <div class="bg-white border border-default rounded-base shadow-xs p-6">
        <h2 class="text-xl font-semibold text-heading mb-6">🌟 إضافة حافز أو تقييم</h2>

        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('incentives.store') }}" method="POST" class="flex flex-col gap-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-heading mb-1">اختر الموظف</label>
                <select name="user_id" id="incentive_user_select" required
                    class="incentive-user-select w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">-- اكتب اسم الموظف للبحث --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} - ({{ $user->job_title ?? 'بدون مسمى' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-heading mb-1">التقييم / ملاحظات الحافز</label>
                <textarea name="evaluation" rows="5" required placeholder="اكتب التقييم أو تفاصيل الحافز..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">{{ old('evaluation') }}</textarea>
            </div>

            <button type="submit"
                class="w-full bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium py-2 rounded-lg">
                إضافة الحافز
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.querySelector('.incentive-user-select')) {
            new TomSelect(".incentive-user-select", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: "ابحث عن موظف...",
                allowEmptyOption: true,
            });
        }
    });
</script>

@endsection
