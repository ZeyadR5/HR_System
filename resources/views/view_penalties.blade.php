@extends('layout')
@section('title', 'الجزاءات')
@section('content')

<div class="max-w-7xl mx-auto px-4 mt-8">
    <div class="bg-white border border-default rounded-base shadow-xs p-5 mb-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-heading">
                    @if(in_array(Auth::user()->role, ['admin', 'super_admin', 'developer']))
                        جزاءات جميع الموظفين
                    @else
                        جزاءاتي
                    @endif
                </h1>
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
                <a href="{{ route('view_penalties', ['period_start' => $previousPeriodStart, 'user_id' => request('user_id')]) }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    الدورة السابقة
                </a>
                @unless($isCurrentPeriod)
                    <a href="{{ route('view_penalties', ['period_start' => $nextPeriodStart, 'user_id' => request('user_id')]) }}"
                       class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        الدورة التالية
                    </a>
                @endunless
                <a href="{{ route('view_penalties', ['user_id' => request('user_id')]) }}"
                   class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    العودة للدورة الحالية
                </a>
            </div>
        </div>

        @if(in_array(Auth::user()->role, ['admin', 'super_admin', 'developer']))
        <hr class="my-4 border-default">
        <form action="{{ route('view_penalties') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="period_start" value="{{ $selectedPeriodStart }}">
            <div class="w-full md:w-64">
                <label for="user_id" class="block text-xs font-medium text-body mb-1">بحث بموظف معين:</label>
                <select name="user_id" id="user_id" class="user-search-select w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">كل الموظفين</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700">
                بحث
            </button>
            @if(request('user_id'))
                <a href="{{ route('view_penalties', ['period_start' => $selectedPeriodStart]) }}" class="text-sm text-red-600 hover:underline">
                    إلغاء الفلتر
                </a>
            @endif
        </form>
        @endif
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
                    <th class="px-2 py-2 font-medium">N</th>
                    @if(in_array(Auth::user()->role, ['admin', 'super_admin', 'developer']))
                    <th class="px-2 py-2 font-medium">اسم الموظف</th>
                    @endif
                    <th class="px-2 py-2 font-medium">سبب الجزاء</th>
                    <th class="px-2 py-2 font-medium">القيمة</th>
                    <th class="px-2 py-2 font-medium">ملاحظات</th>
                    <th class="px-2 py-2 font-medium">بواسطة</th>
                    <th class="px-2 py-2 font-medium">الحالة</th>
                    <th class="px-2 py-2 font-medium">التاريخ (Create At)</th>
                    @if(in_array(Auth::user()->role, ['super_admin', 'developer']))
                        <th class="px-2 py-2 font-medium">IP Address</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($penalties as $index => $item)
                <tr class="odd:bg-neutral-primary even:bg-neutral-secondary-soft border-b border-default">
                    <td class="px-2 py-2">{{ $index + 1 }}</td>
                    @if(in_array(Auth::user()->role, ['admin', 'super_admin', 'developer']))
                    <td class="px-2 py-2 font-medium text-heading">{{ $item->name }}</td>
                    @endif
                    <td class="px-2 py-2">{{ $item->reason }}</td>
                    <td class="px-2 py-2">
                        <span class="bg-red-100 text-red-700 text-xs font-medium px-2.5 py-1 rounded-full">
                            {{ $item->amount }}
                        </span>
                    </td>
                    <td class="px-2 py-2">{{ $item->notes ?? '—' }}</td>
                    <td class="px-2 py-2">
                        <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            {{ $item->actioned_by ?? '—' }}
                        </span>
                    </td>
                    {{-- حالة الجزاء --}}
                    <td class="px-2 py-2">
                        @if(isset($item->status) && $item->status === 'accepted')
                            <span class="bg-green-100 text-green-700 text-xs font-medium px-2.5 py-1 rounded-full">تم القبول</span>
                        @elseif(isset($item->status) && $item->status === 'refused')
                            <span class="bg-gray-100 text-gray-600 text-xs font-medium px-2.5 py-1 rounded-full">تم الرفض</span>
                        @else
                            <span class="bg-yellow-100 text-yellow-700 text-xs font-medium px-2.5 py-1 rounded-full">قيد المراجعة</span>
                        @endif
                    </td>
                    <td class="px-2 py-2">
                        {{ $item->created_at->format('d M Y') }}
                        @if(in_array(Auth::user()->role, ['super_admin', 'developer']))
                            <br><span class="text-xs text-gray-500">{{ $item->created_at->format('H:i:s') }}</span>
                        @endif
                    </td>
                    @if(in_array(Auth::user()->role, ['super_admin', 'developer']))
                        <td class="px-6 py-4 text-xs text-gray-500" dir="ltr" style="text-align: left;">{{ $item->created_ip ?? '-' }}</td>
                    @endif
                </tr>
                @empty
                <tr>
                <td colspan="15" class="px-2 py-2 text-center text-body">
                        لا توجد جزاءات في هذه الدورة.
                    </td>
                </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr class="bg-neutral-secondary-soft font-semibold">
                    <td colspan="15" class="px-2 py-2 text-heading">
                        إجمالي الدورة
                        <span class="text-xs font-normal text-body block">
                            {{ $periodStart->format('d M Y') }} → {{ $periodEnd->format('d M Y') }}
                        </span>
                    </td>
                    <td class="px-2 py-2">
                        <span class="bg-red-100 text-red-700 text-xs font-medium px-2.5 py-1 rounded-full">
                            {{ $penalties->count() }} جزاء
                        </span>
                    </td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- جدول الإجمالي لكل موظف - أدمن فقط --}}
    @if(in_array(Auth::user()->role, ['admin', 'super_admin', 'developer']) && $penalties->count() > 0)
    <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default mt-6">
        <div class="px-2 py-2 bg-neutral-secondary-soft border-b border-default">
            <h6 class="font-semibold text-heading">
                إجمالي الجزاءات لكل موظف
                <span class="text-xs font-normal text-body ms-2">
                    {{ $periodStart->format('d M Y') }} → {{ $periodEnd->format('d M Y') }}
                </span>
            </h6>
        </div>
        <table class="w-full text-sm text-left text-body">
            <thead class="bg-neutral-secondary-soft border-b border-default">
                <tr>
                    <th class="px-2 py-2 font-medium">اسم الموظف</th>
                    <th class="px-2 py-2 font-medium">عدد الجزاءات</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $employeeTotals = $penalties->groupBy('name')
                        ->map(fn($g) => ['name' => $g->first()->name, 'total' => $g->count()])
                        ->sortByDesc('total');
                @endphp
                @foreach($employeeTotals as $record)
                <tr class="odd:bg-neutral-primary even:bg-neutral-secondary-soft border-b border-default">
                    <td class="px-2 py-2 font-medium text-heading">{{ $record['name'] }}</td>
                    <td class="px-2 py-2">
                        <span class="bg-red-100 text-red-700 text-xs font-medium px-2.5 py-1 rounded-full">
                            {{ $record['total'] }} جزاء
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if(in_array(Auth::user()->role, ['super_admin', 'developer']))
    <div class="flex justify-end mt-4 mb-8">
        <a href="{{ route('penalties.export', ['period_start' => $selectedPeriodStart]) }}"
           class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
            </svg>
            Export To Excel
        </a>
    </div>
    @endif
    @endif

</div>

@endsection
