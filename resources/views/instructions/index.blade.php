@extends('layout')
@section('title', 'التعليمات والتنبيهات')

@section('content')
<div class="max-w-7xl mx-auto mt-8 px-4 sm:px-6 lg:px-8 pb-12">

    <!-- Header Section -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight flex items-center gap-3">
                <span class="bg-blue-100 text-blue-600 p-2 rounded-xl">📢</span>
                التعليمات والتنبيهات
            </h1>
            <p class="mt-2 text-sm text-gray-500 font-medium">لوحة الإعلانات الخاصة بالشركة، يرجى متابعتها باستمرار.</p>
        </div>
        
        @can('instructions.manage')
        <button onclick="document.getElementById('addInstructionModal').classList.remove('hidden')" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-sm shadow-blue-200 transition-all active:scale-95">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            إضافة تنبيه جديد
        </button>
        @endcan
    </div>

    <!-- Instructions List -->
    <div class="grid grid-cols-1 gap-6">
        @forelse($instructions as $instruction)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative transition-all hover:shadow-md {{ $instruction->is_pinned ? 'ring-2 ring-yellow-400/50' : '' }}">
                
                @if($instruction->is_pinned)
                <div class="absolute top-0 right-0 w-16 h-16 overflow-hidden">
                    <div class="bg-yellow-400 text-yellow-900 text-[10px] font-bold uppercase tracking-wider text-center py-1 absolute top-4 -right-4 w-24 rotate-45 shadow-sm">
                        مثبت 📌
                    </div>
                </div>
                @endif

                <div class="p-6 md:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <h2 class="text-xl font-bold text-gray-900 mb-2 {{ $instruction->is_pinned ? 'text-yellow-700' : '' }}">
                                {{ $instruction->title }}
                            </h2>
                            <div class="flex items-center gap-4 text-xs font-medium text-gray-500 mb-4">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    {{ $instruction->user->name ?? 'غير معروف' }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $instruction->created_at->format('d M Y - h:i A') }}
                                </span>
                            </div>
                        </div>

                        @can('instructions.manage')
                        <div class="flex items-center gap-2">
                            <!-- Pin Toggle Form -->
                            <form action="{{ route('instructions.pin', $instruction->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="p-2 rounded-lg transition-colors {{ $instruction->is_pinned ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-900' }}" title="{{ $instruction->is_pinned ? 'إلغاء التثبيت' : 'تثبيت' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
                                </button>
                            </form>

                            <!-- Delete Form -->
                            <form action="{{ route('instructions.destroy', $instruction->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا التنبيه؟');">
                                @csrf
                                <button type="submit" class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="حذف">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                        @endcan
                    </div>
                    
                    <div class="prose prose-sm md:prose-base max-w-none text-gray-700 bg-gray-50/50 p-4 rounded-xl border border-gray-100 whitespace-pre-wrap leading-relaxed">
                        {{ $instruction->content }}
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-300">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">لا توجد تعليمات</h3>
                <p class="text-gray-500 text-sm">لم يتم إضافة أي تعليمات أو تنبيهات حتى الآن.</p>
            </div>
        @endforelse
    </div>
</div>

@can('instructions.manage')
<!-- Add Instruction Modal -->
<div id="addInstructionModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/60 backdrop-blur-sm p-4">
    <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl p-6 md:p-8 transform transition-all">
        <!-- Close Button -->
        <button type="button" onclick="document.getElementById('addInstructionModal').classList.add('hidden')" class="absolute top-4 left-4 text-gray-400 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 rounded-full p-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <span class="text-blue-600">✍️</span> إضافة تنبيه جديد
        </h3>

        <form action="{{ route('instructions.store') }}" method="POST">
            @csrf
            
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">عنوان التعليمة/التنبيه</label>
                    <input type="text" name="title" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base py-2.5 px-4 bg-gray-50 focus:bg-white transition-colors" placeholder="مثال: تعليمات بخصوص الإجازات السنوية...">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">التفاصيل الكاملة</label>
                    <textarea name="content" required rows="6" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base py-3 px-4 bg-gray-50 focus:bg-white transition-colors resize-y" placeholder="اكتب كل التفاصيل المطلوبة هنا..."></textarea>
                </div>

                <div class="flex items-center bg-yellow-50/50 border border-yellow-100 p-4 rounded-xl cursor-pointer hover:bg-yellow-50 transition-colors" onclick="document.getElementById('is_pinned').click()">
                    <input id="is_pinned" name="is_pinned" type="checkbox" value="1" class="w-5 h-5 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500 cursor-pointer">
                    <div class="mr-3 select-none">
                        <label for="is_pinned" class="text-sm font-bold text-yellow-900 cursor-pointer">تثبيت في الأعلى 📌</label>
                        <p class="text-xs text-yellow-700 mt-0.5">ستظهر هذه التعليمة دائماً في أعلى الصفحة لضمان رؤيتها.</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-xl font-bold text-sm hover:bg-blue-700 transition-colors shadow-sm shadow-blue-200">
                    نشر التنبيه
                </button>
                <button type="button" onclick="document.getElementById('addInstructionModal').classList.add('hidden')" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-200 transition-colors">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>
@endcan

@endsection
