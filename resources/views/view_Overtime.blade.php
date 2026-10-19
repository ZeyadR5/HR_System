@extends('layout')
@section('title', 'View Overtime')
@section('content')

<div class="max-w-7xl mx-auto px-4 mt-8">
    <div class="bg-white border border-default rounded-base shadow-xs p-5 mb-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-heading">Overtime Cycle View</h1>
                <p class="text-sm text-body mt-1">
                    الدورة الحالية للإضافي تتحسب من يوم 26 إلى يوم 25.
                </p>
                <p class="text-sm font-medium text-heading mt-2">
                    الفترة المعروضة: {{ $periodLabel }}
                    @if($isCurrentPeriod)
                        <span class="ms-2 inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">
                            Current Cycle
                        </span>
                    @else
                        <span class="ms-2 inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700">
                            Archived Cycle
                        </span>
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('view_Overtime', ['period_start' => $previousPeriodStart, 'user_id' => request('user_id')]) }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    الدورة السابقة
                </a>

                @unless($isCurrentPeriod)
                    <a href="{{ route('view_Overtime', ['period_start' => $nextPeriodStart, 'user_id' => request('user_id')]) }}"
                       class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        الدورة التالية
                    </a>
                @endunless

                <a href="{{ route('view_Overtime', ['user_id' => request('user_id')]) }}"
                   class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    العودة للدورة الحالية
                </a>
            </div>
        </div>

        @if(in_array(Auth::user()->role, ['admin', 'super_admin', 'developer']))
        <hr class="my-4 border-default">
        <form action="{{ route('view_Overtime') }}" method="GET" class="flex flex-wrap items-end gap-3">
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
                <a href="{{ route('view_Overtime', ['period_start' => $selectedPeriodStart]) }}" class="text-sm text-red-600 hover:underline">
                    إلغاء الفلتر
                </a>
            @endif
        </form>
        @endif

        @if(in_array(Auth::user()->role, ['admin', 'super_admin', 'developer']) && $olderPendingCount > 0)
            <div class="mt-4 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                يوجد {{ $olderPendingCount }} طلب إضافي Pending من فترات أقدم.
                يمكنك مراجعتها من خلال زر <span class="font-semibold">الدورة السابقة</span>.
            </div>
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
                    <th scope="col" class="px-2 py-2 font-medium">N</th>
                    <th scope="col" class="px-2 py-2 font-medium">Employee Name</th>
                    <!-- <th scope="col" class="px-2 py-2 font-medium">Total Hours</th> -->
                    <th scope="col" class="px-2 py-2 font-medium">Reason</th>
                    <th scope="col" class="px-2 py-2 font-medium">Date</th>
                    <th scope="col" class="px-2 py-2 font-medium">Day</th>
                    <th scope="col" class="px-2 py-2 font-medium">From</th>
                    <th scope="col" class="px-2 py-2 font-medium">To</th>
                    <th scope="col" class="px-2 py-2 font-medium">Duration</th>
                    <th scope="col" class="px-2 py-2 font-medium">Status</th>
                    @if(in_array(Auth::user()->role, ['admin', 'super_admin', 'developer']))
                        <th scope="col" class="px-2 py-2 font-medium">Actions</th>
                    @endif
                    <th class="px-2 py-2 font-medium">Created At</th>
                    @if(in_array(Auth::user()->role, ['super_admin', 'developer']))
                        <th class="px-2 py-2 font-medium">IP Address</th>
                    @endif
                    <th class="px-2 py-2 font-medium">بواسطة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($Overtime as $index => $item)
                    <tr class="odd:bg-neutral-primary even:bg-neutral-secondary-soft border-b border-default">
                        <td class="px-2 py-2">{{ $index + 1 }}</td>
                        <th scope="row" class="px-2 py-2 font-medium text-heading">
                            <div>{{ $item->name }}</div>
                            @if($item->user?->shiftGroup)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full mt-1" style="background: {{ $item->user->shiftGroup->color }}15; color: {{ $item->user->shiftGroup->color }}; border: 1px solid {{ $item->user->shiftGroup->color }}30;">
                                    {{ $item->user->shiftGroup->icon }} {{ $item->user->shiftGroup->name }}
                                </span>
                            @endif
                        </th>
                        <!-- <td class="px-2 py-2">{{ $item->total_hours }}</td> -->
                        <td class="px-2 py-2">{{ $item->reason }}</td>
                        <td class="px-2 py-2">{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</td>
                        <td class="px-2 py-2">{{ $item->day }}</td>
                        <td class="px-2 py-2">
                            {{ $item->from
                                ? date('h:i', strtotime($item->from)) . (date('A', strtotime($item->from)) == 'AM' ? ' ص' : ' م')
                                : '-'
                            }}
                        </td>

                        <td class="px-2 py-2">
                            {{ $item->to
                                ? date('h:i', strtotime($item->to)) . (date('A', strtotime($item->to)) == 'AM' ? ' ص' : ' م')
                                : '-'
                            }}
                        </td>
                        <td class="px-2 py-2">
                            @php
                                $from = \Carbon\Carbon::parse($item->from);
                                $to = \Carbon\Carbon::parse($item->to);
                                $diff = $from->diffInMinutes($to);
                                $h = intdiv($diff, 60);
                                $m = $diff % 60;
                            @endphp
                            <span class="bg-purple-100 text-purple-700 text-xs font-medium px-2.5 py-1 rounded-full">
                                {{ $h }}h {{ $m }}m
                            </span>
                        </td>
                        <td class="px-2 py-2">
                            @if($item->status === 'accepted')
                                <span class="bg-green-100 text-green-700 text-xs font-medium px-2.5 py-1 rounded-full">تم القبول</span>
                            @elseif($item->status === 'refused')
                                <span class="bg-red-100 text-red-700 text-xs font-medium px-2.5 py-1 rounded-full">تم الرفض</span>
                            @else
                                <span class="bg-yellow-100 text-yellow-700 text-xs font-medium px-2.5 py-1 rounded-full">قيد المراجعة</span>
                            @endif
                        </td>
                        
                        @if(in_array(Auth::user()->role, ['admin', 'super_admin', 'developer']))
                        <td class="px-2 py-2">
                                @if($item->status === 'pending')
                                    <div class="flex gap-2">
                                        <form action="{{ route('overtime.accept', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-white bg-green-600 hover:bg-green-700 text-sm px-3 py-1.5 rounded">Accept</button>
                                        </form>
                                        <button type="button"
                                            onclick="document.getElementById('refuseModal{{ $item->id }}').classList.remove('hidden')"
                                            class="text-white bg-red-600 hover:bg-red-700 text-sm px-3 py-1.5 rounded">
                                            Refuse
                                        </button>
                                    </div>
                                    <div id="refuseModal{{ $item->id }}" class="hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50">
                                        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                                            <h5 class="text-lg font-semibold mb-4">Reason for Refusal</h5>
                                            <form action="{{ route('overtime.refuse', $item->id) }}" method="POST">
                                                @csrf
                                                <textarea
                                                name="refuse_reason"
                                                rows="4"
                                                required
                                                placeholder="Enter reason for refusal..."
                                                class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 mb-4"
                                                ></textarea>
                                                <div class="flex justify-end gap-2">
                                                    <button type="button"
                                                    onclick="document.getElementById('refuseModal{{ $item->id }}').classList.add('hidden')"
                                                    class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded">
                                                    Cancel
                                                </button>
                                                    <button type="submit"
                                                        class="px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded">
                                                        Confirm Refuse
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    @else
                                    <span class="text-gray-400 text-xs">Done</span>
                                    @endif
                                </td>
                                @endif
                                
                                <td class="px-2 py-2">
                                    {{ $item->created_at->toDateString() }}
                                    @if(in_array(Auth::user()->role, ['super_admin', 'developer']))
                                        <br><span class="text-xs text-gray-500">{{ $item->created_at->format('H:i:s') }}</span>
                                    @endif
                                </td>
                                @if(in_array(Auth::user()->role, ['super_admin', 'developer']))
                                    <td class="px-6 py-4 text-xs text-gray-500" dir="ltr" style="text-align: left;">{{ $item->created_ip ?? '-' }}</td>
                                @endif
                <td class="px-6 py-4">
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
                        <td colspan="15" class="px-2 py-2 text-center text-body">
                            No overtime records found for this cycle.
                        </td>
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
                    <td class="px-2 py-2 text-heading">
                        @if(in_array(Auth::user()->role, ['admin', 'super_admin', 'developer']))
                            @php $sumHours = $monthlyTotals->sum('total'); @endphp
                            {{ (int) round($sumHours * 60) }} دقيقة ({{ round($sumHours, 2) }} ساعة)
                        @else
                            @php $userTotalHours = $monthlyTotals->first()->total ?? 0; @endphp
                            {{ (int) round($userTotalHours * 60) }} دقيقة ({{ round($userTotalHours, 2) }} ساعة)
                        @endif
                    </td>
                    <td colspan="15"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if(in_array(Auth::user()->role, ['admin', 'super_admin', 'developer']))
        <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default mt-6">
            <div class="px-2 py-2 bg-neutral-secondary-soft border-b border-default flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <h6 class="font-semibold text-heading">
                    Totals Per Employee
                    <span class="text-xs font-normal text-body ms-2">
                        {{ $periodStart->format('d M Y') }} → {{ $periodEnd->format('d M Y') }}
                    </span>
                </h6>
            </div>
            <table class="w-full text-sm text-left text-body">
                <thead class="bg-neutral-secondary-soft border-b border-default">
                    <tr>
                        <th class="px-2 py-2 font-medium">Employee Name</th>
                        <th class="px-2 py-2 font-medium">Accepted Overtime This Cycle</th>
                        <th class="px-2 py-2 font-medium">OT Amount (EGP)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthlyTotals as $record)
                        <tr class="odd:bg-neutral-primary even:bg-neutral-secondary-soft border-b border-default">
                            <td class="px-2 py-2 font-medium text-heading">{{ $record->name ?? 'غير محدد' }}</td>
                            <td class="px-2 py-2">
                                <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2.5 py-1 rounded-full">
                                    {{ (int) round($record->total * 60) }} دقيقة ({{ round($record->total, 2) }} h)
                                </span>
                            </td>
                            <td class="px-2 py-2">
                                @if($record->user && $record->user->hourly_rate > 0)
                                    @php
                                        $amount = $record->total * 1.5 * $record->user->hourly_rate;
                                    @endphp
                                    <span class="bg-green-100 text-green-700 text-xs font-bold px-2.5 py-1 rounded-full">
                                        {{ number_format($amount, 2) }} ج.م
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs italic">قيمة الساعة غير محددة</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-6 text-center text-body">No accepted overtime in this cycle.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(in_array(Auth::user()->role, ['super_admin', 'developer']))
        <div class="flex justify-end mt-4 mb-8">
            <a href="{{ route('overtime.export', ['period_start' => $selectedPeriodStart]) }}"
               class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                </svg>
                Export displayed cycle
            </a>
        </div>
        @endif
    @endif
</div>

@endsection
