@extends('layout')
@section('title', 'Audit Log')
@section('content')

<div class="max-w-7xl mx-auto px-4 mt-8">
    <div class="bg-white border border-default rounded-base shadow-xs p-5 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-heading">System Audit Log</h1>
                <p class="text-sm text-body mt-1">سجل التحركات الشاملة في النظام - مخصص للمسؤول التقني فقط.</p>
            </div>
            <div class="bg-red-50 text-red-700 text-xs font-bold px-3 py-1.5 rounded-full border border-red-100 flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                </span>
                Restricted Access
            </div>
        </div>
    </div>

    <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">
        <table class="w-full text-sm text-left rtl:text-right text-body">
            <thead class="bg-neutral-secondary-soft border-b border-default">
                <tr>
                    <th class="px-6 py-3 font-medium">Time (Exactly)</th>
                    <th class="px-6 py-3 font-medium">Performed By</th>
                    <th class="px-6 py-3 font-medium">Action</th>
                    <th class="px-6 py-3 font-medium">Module</th>
                    <th class="px-6 py-3 font-medium">Target Employee</th>
                    <th class="px-6 py-3 font-medium">Details</th>
                    <th class="px-6 py-3 font-medium">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="odd:bg-neutral-primary even:bg-neutral-secondary-soft border-b border-default hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 font-mono text-xs text-blue-600">
                        {{ $log->created_at->format('d M Y | h:i:s A') }}
                    </td>
                    <td class="px-6 py-4 font-medium text-heading">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold text-xs">
                                {{ substr($log->user_name, 0, 1) }}
                            </div>
                            {{ $log->user_name }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $actionColors = [
                                'Accepted' => 'bg-green-100 text-green-700',
                                'Refused' => 'bg-red-100 text-red-700',
                                'Created' => 'bg-blue-100 text-blue-700',
                                'Login' => 'bg-purple-100 text-purple-700',
                                'Failed Login' => 'bg-red-100 text-red-800 font-bold',
                                'Logout' => 'bg-gray-100 text-gray-700',
                                'System Error' => 'bg-red-100 text-red-800 font-bold border border-red-200',
                            ];
                            $color = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <span class="{{ $color }} text-[10px] font-bold px-2.5 py-1 rounded-full uppercase">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs font-semibold">
                        {{ $log->module }}
                    </td>
                    <td class="px-6 py-4 font-medium text-heading">
                        {{ $log->target ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-xs text-body italic">
                        {{ $log->details ?? '—' }}
                    </td>
                    <td class="px-6 py-4 font-mono text-[10px] text-gray-500 bg-gray-50 text-center">
                        {{ $log->ip_address ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-sm font-medium">لم يتم تسجيل أي عمليات بعد.</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $logs->links() }}
    </div>
</div>

@endsection
