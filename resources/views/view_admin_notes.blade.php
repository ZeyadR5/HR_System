@extends('layout')
@section('title', 'عرض ملاحظات الإدارة')
@section('content')

<div class="max-w-7xl mx-auto px-4 mt-8">
    <div class="bg-white border border-default rounded-base shadow-xs p-5 mb-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-heading">ملاحظات الإدارة</h1>
                <p class="text-sm text-body mt-1">الدورة الحالية للملاحظات تتحسب من يوم 26 إلى يوم 25.</p>
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
                <a href="{{ route('admin_notes.index', ['period_start' => $previousPeriodStart]) }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    الدورة السابقة
                </a>

                @unless($isCurrentPeriod)
                    <a href="{{ route('admin_notes.index', ['period_start' => $nextPeriodStart]) }}"
                       class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        الدورة التالية
                    </a>
                @endunless

                <a href="{{ route('admin_notes.index') }}"
                   class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    العودة للدورة الحالية
                </a>
            </div>
        </div>
    </div>

    <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">
        <table class="w-full text-sm text-left rtl:text-right text-body">
            <thead class="bg-neutral-secondary-soft border-b border-default">
                <tr>
                    <th scope="col" class="px-4 py-3 font-medium">N</th>
                    <th scope="col" class="px-4 py-3 font-medium">الملاحظة</th>
                    <th scope="col" class="px-4 py-3 font-medium">تاريخ الملاحظة</th>
                    <th scope="col" class="px-4 py-3 font-medium">تاريخ الإضافة</th>
                    @if(in_array(Auth::user()->role, ['super_admin', 'developer']))
                        <th class="px-4 py-3 font-medium">IP Address</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($notes as $index => $item)
                    <tr class="odd:bg-neutral-primary even:bg-neutral-secondary-soft border-b border-default">
                        <td class="px-4 py-3">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 text-heading">{{ $item->note }}</td>
                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            {{ $item->created_at->format('d M Y') }}
                            <br><span class="text-xs text-gray-500">{{ $item->created_at->format('H:i:s') }}</span>
                        </td>
                        @if(in_array(Auth::user()->role, ['super_admin', 'developer']))
                            <td class="px-4 py-3 text-xs text-gray-500" dir="ltr" style="text-align: left;">{{ $item->created_ip ?? '-' }}</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-body">
                            لا يوجد ملاحظات في هذه الدورة.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(in_array(Auth::user()->role, ['super_admin', 'developer']) && $notes->count() > 0)
    <div class="flex justify-end mt-4 mb-8">
        <a href="{{ route('admin_notes.export', ['period_start' => $selectedPeriodStart]) }}"
           class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
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
