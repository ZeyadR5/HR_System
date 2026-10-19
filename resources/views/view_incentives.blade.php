@extends('layout')
@section('title', 'عرض الحوافز')
@section('content')

<div class="max-w-7xl mx-auto px-4 mt-8">
    <div class="bg-white border border-default rounded-base shadow-xs p-5 mb-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-heading">قائمة الحوافز والتقييمات</h1>
                <p class="text-sm text-body mt-1">الدورة تتحسب من يوم 26 إلى يوم 25.</p>
                <p class="text-sm font-medium text-heading mt-2">
                    الفترة المعروضة: {{ $periodLabel }}
                    @if($isCurrentPeriod)
                        <span class="ms-2 inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">Current Cycle</span>
                    @else
                        <span class="ms-2 inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700">Archived Cycle</span>
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('incentives.index', ['period_start' => $previousPeriodStart, 'user_id' => request('user_id')]) }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    الدورة السابقة
                </a>
                @unless($isCurrentPeriod)
                    <a href="{{ route('incentives.index', ['period_start' => $nextPeriodStart, 'user_id' => request('user_id')]) }}"
                       class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        الدورة التالية
                    </a>
                @endunless
                <a href="{{ route('incentives.index', ['user_id' => request('user_id')]) }}"
                   class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    العودة للدورة الحالية
                </a>
            </div>
        </div>

        <hr class="my-4 border-default">
        <form action="{{ route('incentives.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="period_start" value="{{ $selectedPeriodStart }}">
            @if(request('sort_by'))
                <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                <input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}">
            @endif

            <div class="w-full md:w-56">
                <label for="job_title" class="block text-xs font-medium text-body mb-1">فلترة بالوظيفة:</label>
                <select name="job_title" id="job_title" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3" onchange="this.form.submit()">
                    <option value="">جميع الوظائف (الكل)</option>
                    @foreach($jobTitles as $jt)
                        <option value="{{ $jt }}" {{ request('job_title') == $jt ? 'selected' : '' }}>
                            {{ $jt }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-64">
                <label for="user_id" class="block text-xs font-medium text-body mb-1">بحث بموظف معين:</label>
                <select name="user_id" id="user_id" class="user-search-select w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
                    <option value="">كل الموظفين</option>
                    @foreach($filterUsers as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->job_title ?? 'بدون مسمى' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="bg-blue-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700">
                بحث
            </button>

            @if(request('user_id') || request('job_title') || request('sort_by'))
                <a href="{{ route('incentives.index', ['period_start' => $selectedPeriodStart]) }}" class="text-sm text-red-600 hover:underline">
                    ✖ إلغاء الفلتر
                </a>
            @endif
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (document.querySelector('.user-search-select')) {
                new TomSelect(".user-search-select", {
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    },
                    placeholder: "اختر موظف...",
                    allowEmptyOption: true,
                });
            }
        });
    </script>

    <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">
        <table class="w-full text-sm text-left rtl:text-right text-body">
            <thead class="bg-neutral-secondary-soft border-b border-default">
                <tr>
                    <th class="px-4 py-3 font-medium text-center" style="width: 50px;">N</th>
                    <th class="px-4 py-3 font-medium">
                        @php
                            $nextNameDir = (request('sort_by') === 'name' && request('sort_dir', 'asc') === 'asc') ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_dir' => $nextNameDir]) }}" class="inline-flex items-center gap-1 text-heading hover:underline">
                            <span>اسم الموظف</span>
                            @if(request('sort_by') === 'name' || !request('sort_by'))
                                <span class="text-blue-600 text-xs">{{ request('sort_dir') === 'desc' ? '▼' : '▲' }}</span>
                            @else
                                <span class="text-gray-400 text-xs">↕</span>
                            @endif
                        </a>
                    </th>
                    <th class="px-4 py-3 font-medium">
                        @php
                            $nextJobDir = (request('sort_by') === 'job_title' && request('sort_dir') === 'asc') ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'job_title', 'sort_dir' => $nextJobDir]) }}" class="inline-flex items-center gap-1 text-heading hover:underline">
                            <span>الوظيفة / المسمى</span>
                            @if(request('sort_by') === 'job_title')
                                <span class="text-blue-600 text-xs">{{ request('sort_dir') === 'desc' ? '▼' : '▲' }}</span>
                            @else
                                <span class="text-gray-400 text-xs">↕</span>
                            @endif
                        </a>
                    </th>
                    <th class="px-4 py-3 font-medium">التقييم / الحافز</th>
                    <th class="px-4 py-3 font-medium">ملاحظة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $index => $user)
                <tr class="odd:bg-neutral-primary even:bg-neutral-secondary-soft border-b border-default">
                    <td class="px-4 py-3 text-center">{{ $index + 1 }}</td>
                    <td class="px-4 py-3 font-medium text-heading">{{ $user->name }}</td>
                    <td class="px-4 py-3">
                        @if($user->job_title)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $user->job_title }}
                            </span>
                        @else
                            <span class="text-gray-400 italic text-xs">غير محدد</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $userIncentive = $incentives->where('user_id', $user->id)->first();
                        @endphp
                        {{ $userIncentive ? $userIncentive->evaluation : '' }}
                    </td>
                    <td class="px-4 py-3"></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                        لا توجد بيانات موظفين تطابق الفلتر المحدد.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(in_array(Auth::user()->role, ['super_admin', 'developer']))
    <div class="flex justify-end mt-4 mb-8">
        <a href="{{ route('incentives.export', ['period_start' => $selectedPeriodStart]) }}"
           class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg no-loader">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
            </svg>
           Export To Excel
        </a>
    </div>
    @endif

</div>

@endsection
