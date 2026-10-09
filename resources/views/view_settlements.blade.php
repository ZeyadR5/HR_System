@extends('layout')
@section('title', 'عرض التسويات')
@section('content')

<div class="max-w-7xl mx-auto px-4 mt-8">
    <div class="bg-white border border-default rounded-base shadow-xs p-5 mb-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-heading">سجل التسويات</h1>
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
                <a href="{{ route('settlements.index', ['period_start' => $previousPeriodStart, 'user_id' => request('user_id')]) }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    الدورة السابقة
                </a>
                @unless($isCurrentPeriod)
                    <a href="{{ route('settlements.index', ['period_start' => $nextPeriodStart, 'user_id' => request('user_id')]) }}"
                       class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        الدورة التالية
                    </a>
                @endunless
                <a href="{{ route('settlements.index', ['user_id' => request('user_id')]) }}"
                   class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    العودة للدورة الحالية
                </a>
            </div>
        </div>

        @if(in_array(Auth::user()->role, ['super_admin', 'developer']))
        <hr class="my-4 border-default">
        <form action="{{ route('settlements.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
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
                <a href="{{ route('settlements.index', ['period_start' => $selectedPeriodStart]) }}" class="text-sm text-red-600 hover:underline">
                    إلغاء الفلتر
                </a>
            @endif
        </form>
        @endif

        @if(in_array(Auth::user()->role, ['super_admin', 'developer']) && $olderPendingCount > 0)
            <div class="mt-4 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                يوجد {{ $olderPendingCount }} طلب تسوية Pending من فترات أقدم.
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
                    <th class="px-4 py-3 font-medium">N</th>
                    <th class="px-4 py-3 font-medium">اسم الموظف</th>
                    <th class="px-4 py-3 font-medium">الملاحظة</th>
                    <th class="px-4 py-3 font-medium">تاريخ الطلب</th>
                    <th class="px-4 py-3 font-medium">اليوم</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    @if(in_array(Auth::user()->role, ['super_admin', 'developer']))
                        <th class="px-4 py-3 font-medium">الإجراءات</th>
                    @endif
                    <th class="px-4 py-3 font-medium">بواسطة</th>
                    @if(in_array(Auth::user()->role, ['super_admin', 'developer']))
                        <th class="px-4 py-3 font-medium">IP Address</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($settlements as $index => $item)
                <tr class="odd:bg-neutral-primary even:bg-neutral-secondary-soft border-b border-default">
                    <td class="px-4 py-3">{{ $index + 1 }}</td>
                    <td class="px-4 py-3 font-medium text-heading">{{ $item->name }}</td>
                    <td class="px-4 py-3 whitespace-pre-line">{{ $item->note }}</td>
                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</td>
                    <td class="px-4 py-3">{{ $item->day }}</td>
                    <td class="px-4 py-3">
                        @if($item->status === 'accepted')
                            <span class="bg-green-100 text-green-700 text-xs font-medium px-2.5 py-1 rounded-full">تم القبول</span>
                        @elseif($item->status === 'refused')
                            <span class="bg-red-100 text-red-700 text-xs font-medium px-2.5 py-1 rounded-full">تم الرفض</span>
                        @else
                            <span class="bg-yellow-100 text-yellow-700 text-xs font-medium px-2.5 py-1 rounded-full">قيد المراجعة</span>
                        @endif
                    </td>
                    @if(in_array(Auth::user()->role, ['super_admin', 'developer']))
                    <td class="px-4 py-3">
                        @if($item->status === 'pending')
                            <div class="flex gap-2">
                                <button type="button" 
                                    onclick="openSettlementModal('accept', {{ $item->id }})"
                                    class="text-white bg-green-600 hover:bg-green-700 text-xs px-3 py-1.5 rounded">Accept</button>
                                <button type="button"
                                    onclick="openSettlementModal('refuse', {{ $item->id }})"
                                    class="text-white bg-red-600 hover:bg-red-700 text-xs px-3 py-1.5 rounded">Refuse</button>
                            </div>
                        @else
                            <span class="text-gray-400 text-xs">Done</span>
                        @endif
                    </td>
                    @endif
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            {{ $item->actioned_by ?? '—' }}
                        </span>
                    </td>
                    @if(in_array(Auth::user()->role, ['super_admin', 'developer']))
                        <td class="px-4 py-3 text-xs text-gray-500" dir="ltr" style="text-align: left;">{{ $item->created_ip ?? '-' }}</td>
                    @endif
                </tr>
                
                @if($item->status === 'accepted' && $item->accept_note)
                    <tr class="bg-green-50 border-b border-default">
                        <td colspan="10" class="px-6 py-2">
                            <span class="text-green-600 text-xs font-medium">ملاحظات القبول: </span>
                            <span class="text-green-500 text-xs">{{ $item->accept_note }}</span>
                        </td>
                    </tr>
                @endif
                
                @if($item->status === 'refused' && $item->refuse_reason)
                    <tr class="bg-red-50 border-b border-default">
                        <td colspan="10" class="px-6 py-2">
                            <span class="text-red-600 text-xs font-medium">سبب الرفض: </span>
                            <span class="text-red-500 text-xs">{{ $item->refuse_reason }}</span>
                        </td>
                    </tr>
                @endif

                @empty
                <tr>
                <td colspan="10" class="px-4 py-10 text-center text-body">
                        لا توجد طلبات تسوية في هذه الدورة.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(in_array(Auth::user()->role, ['super_admin', 'developer']) && $settlements->count() > 0)
    <div class="flex justify-end mt-4 mb-8">
        <a href="{{ route('settlements.export', ['period_start' => $selectedPeriodStart]) }}"
           class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
            </svg>
            تصدير تسويات الدورة
        </a>
    </div>
    @endif
</div>

<!-- Modal for Accept/Refuse -->
<div id="settlementActionModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h5 id="modalTitle" class="text-lg font-semibold mb-4 text-heading"></h5>
        <form id="modalForm" method="POST">
            @csrf
            <textarea
                id="modalTextarea"
                name=""
                rows="4"
                required
                class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:outline-none focus:ring-2 mb-4"
            ></textarea>
            <div class="flex justify-end gap-2">
                <button type="button"
                    onclick="document.getElementById('settlementActionModal').classList.add('hidden')"
                    class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded">إلغاء</button>
                <button type="submit" id="modalSubmitBtn"
                    class="px-4 py-2 text-sm text-white rounded"></button>
            </div>
        </form>
    </div>
</div>

<script>
    function openSettlementModal(type, id) {
        const modal = document.getElementById('settlementActionModal');
        const title = document.getElementById('modalTitle');
        const form = document.getElementById('modalForm');
        const textarea = document.getElementById('modalTextarea');
        const submitBtn = document.getElementById('modalSubmitBtn');

        if (type === 'accept') {
            title.innerText = 'ملاحظات القبول';
            form.action = `/settlements/${id}/accept`;
            textarea.name = 'accept_note';
            textarea.placeholder = 'اكتب ملاحظات القبول (اختياري)...';
            textarea.classList.remove('focus:ring-red-400');
            textarea.classList.add('focus:ring-green-400');
            textarea.required = false;
            submitBtn.innerText = 'تأكيد القبول';
            submitBtn.className = 'px-4 py-2 text-sm text-white bg-green-600 hover:bg-green-700 rounded';
        } else {
            title.innerText = 'سبب الرفض';
            form.action = `/settlements/${id}/refuse`;
            textarea.name = 'refuse_reason';
            textarea.placeholder = 'يجب كتابة سبب الرفض...';
            textarea.classList.remove('focus:ring-green-400');
            textarea.classList.add('focus:ring-red-400');
            textarea.required = true;
            submitBtn.innerText = 'تأكيد الرفض';
            submitBtn.className = 'px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
</script>

@endsection
