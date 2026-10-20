@extends('layout')
@section('title', 'View Leaves')
@section('content')

<div class="max-w-7xl mx-auto px-4 mt-8">
    <div class="bg-white border border-default rounded-base shadow-xs p-5 mb-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-heading">Leave Cycle View</h1>
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
                <a href="{{ route('view_leave', ['period_start' => $previousPeriodStart, 'user_id' => request('user_id')]) }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    الدورة السابقة
                </a>
                @unless($isCurrentPeriod)
                    <a href="{{ route('view_leave', ['period_start' => $nextPeriodStart, 'user_id' => request('user_id')]) }}"
                       class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        الدورة التالية
                    </a>
                @endunless
                <a href="{{ route('view_leave', ['user_id' => request('user_id')]) }}"
                   class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    العودة للدورة الحالية
                </a>
            </div>
        </div>

        @can('leave.view_all')
        <hr class="my-4 border-default">
        <form action="{{ route('view_leave') }}" method="GET" class="flex flex-wrap items-end gap-3">
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
                <a href="{{ route('view_leave', ['period_start' => $selectedPeriodStart]) }}" class="text-sm text-red-600 hover:underline">
                    إلغاء الفلتر
                </a>
            @endif
        </form>
        @endcan
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
                    <th class="px-2 py-2 font-medium">Employee Name</th>
                    <th class="px-2 py-2 font-medium">Date</th>
                    <th class="px-2 py-2 font-medium">Day</th>
                    <th class="px-2 py-2 font-medium">Days Count</th>
                    <th class="px-2 py-2 font-medium">Reason</th>
                    <th class="px-2 py-2 font-medium">Substitute</th>
                    <th class="px-2 py-2 font-medium">Status</th>
                    @can('leave.approve')
                    <th class="px-2 py-2 font-medium">Actions</th>
                    @endcan
                    <th class="px-2 py-2 font-medium">Created At</th>
                    @can('system.view_ip')
                        <th class="px-2 py-2 font-medium">IP Address</th>
                    @endcan
                    <th class="px-2 py-2 font-medium">بواسطة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $index => $item)
                <tr class="odd:bg-neutral-primary even:bg-neutral-secondary-soft border-b border-default">
                    <td class="px-2 py-2">{{ $index + 1 }}</td>
                    <td class="px-2 py-2 font-medium text-heading">{{ $item->name }}</td>
                    <td class="px-2 py-2">{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</td>
                    <td class="px-2 py-2">{{ $item->day }}</td>
                    <td class="px-2 py-2">{{ $item->days_count }} days</td>
                    <td class="px-2 py-2">{{ $item->reason }}</td>
                    <td class="px-2 py-2">{{ $item->substitute }}</td>
                    <td class="px-2 py-2">
                        @if($item->status === 'accepted')
                            <span class="bg-green-100 text-green-700 text-xs font-medium px-2.5 py-1 rounded-full">تم القبول</span>
                        @elseif($item->status === 'refused')
                            <span class="bg-red-100 text-red-700 text-xs font-medium px-2.5 py-1 rounded-full">تم الرفض</span>
                        @else
                            <span class="bg-yellow-100 text-yellow-700 text-xs font-medium px-2.5 py-1 rounded-full">قيد المراجعة</span>
                        @endif
                    </td>
                    @can('leave.approve')
                    <td class="px-2 py-2">
                        @if($item->status === 'pending')
                        <div class="flex gap-2">
                            <form action="{{ route('leave.accept', $item->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-white bg-green-600 hover:bg-green-700 text-sm px-3 py-1.5 rounded">Accept</button>
                            </form>
                            <button type="button"
                            onclick="document.getElementById('leaveRefuseModal{{ $item->id }}').classList.remove('hidden')"
                            class="text-white bg-red-600 hover:bg-red-700 text-sm px-3 py-1.5 rounded">
                            Refuse
                        </button>
                    </div>
                    <div id="leaveRefuseModal{{ $item->id }}" class="hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50">
                        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                            <h5 class="text-lg font-semibold mb-4">Reason for Refusal</h5>
                            <form action="{{ route('leave.refuse', $item->id) }}" method="POST">
                                @csrf
                                <textarea name="refuse_reason" rows="4" required placeholder="Enter reason..."
                                class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 mb-4"></textarea>
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                    onclick="document.getElementById('leaveRefuseModal{{ $item->id }}').classList.add('hidden')"
                                    class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded">Cancel</button>
                                    <button type="submit" class="px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded">Confirm Refuse</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @else
                    <span class="text-gray-400 text-xs">Done</span>
                    @endif
                </td>
                @endcan
                <td class="px-6 py-4">
                    {{ $item->created_at->toDateString() }}
                    @can('system.view_ip')
                        <br><span class="text-xs text-gray-500">{{ $item->created_at->format('H:i:s') }}</span>
                    @endcan
                </td>
                @can('system.view_ip')
                    <td class="px-6 py-4 text-xs text-gray-500" dir="ltr" style="text-align: left;">{{ $item->created_ip ?? '-' }}</td>
                @endcan
                    <td class="px-2 py-2">
                        <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            {{ $item->actioned_by ?? '—' }}
                        </span>
                    </td>
            </tr>
            @if($item->status === 'refused' && $item->refuse_reason)
            <tr class="bg-red-50 border-b border-default">
                <td colspan="15" class="px-6 py-2">
                        <span class="text-red-600 text-xs font-medium">Refuse Reason: </span>
                        <span class="text-red-500 text-xs">{{ $item->refuse_reason }}</span>
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="10" class="px-2 py-2 text-center text-body">No leave records found for this cycle.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-neutral-secondary-soft font-semibold">
                    <td colspan="2" class="px-2 py-2 text-heading">
                        Total This Period
                        <span class="text-xs font-normal text-body block">
                            {{ $periodStart->format('d M Y') }} → {{ $periodEnd->format('d M Y') }}
                        </span>
                    </td>
                    <td colspan="2" class="px-2 py-2 text-heading">
                        @can('leave.view_totals')
                            {{ $leaves->where('status', 'accepted')->sum('days_count') }} days
                        @else
                            @if(request('user_id') || !Auth::user()->can('leave.view_all'))
                                {{ $leaves->where('status', 'accepted')->sum('days_count') }} days
                            @else
                                —
                            @endif
                        @endcan
                    </td>
                    <td colspan="6"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    @can('leave.view_totals')
    <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default mt-6">
        <div class="px-2 py-2 bg-neutral-secondary-soft border-b border-default">
            <h6 class="font-semibold text-heading">
                Leave Totals Per Employee
                <span class="text-xs font-normal text-body ms-2">
                    {{ $periodStart->format('d M Y') }} → {{ $periodEnd->format('d M Y') }}
                </span>
            </h6>
        </div>
        <table class="w-full text-sm text-left text-body">
            <thead class="bg-neutral-secondary-soft border-b border-default">
                <tr>
                    <th class="px-2 py-2 font-medium">Employee Name</th>
                    <th class="px-2 py-2 font-medium">Total Days This Period</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $periodTotals = $leaves->where('status', 'accepted')
                        ->groupBy('name')
                        ->map(fn($g) => ['name' => $g->first()->name, 'total' => $g->sum('days_count')])
                        ->sortByDesc('total');
                @endphp
                @forelse($periodTotals as $record)
                <tr class="odd:bg-neutral-primary even:bg-neutral-secondary-soft border-b border-default">
                    <td class="px-2 py-2">{{ $record['name'] }}</td>
                    <td class="px-2 py-2">
                        <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2.5 py-1 rounded-full">
                            {{ $record['total'] }} days
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="px-6 py-6 text-center text-body">No records.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endcan

    @can('leave.export')
    <div class="flex justify-end mt-4 mb-8">
        <a href="{{ route('leave.export', ['period_start' => $selectedPeriodStart]) }}"
           class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
            </svg>
            Export displayed cycle
        </a>
    </div>
    @endcan
</div>

@endsection
