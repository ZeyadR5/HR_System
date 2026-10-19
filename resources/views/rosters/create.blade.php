@extends('layout')

@section('title', 'إنشاء جدول نوبتجيات جديد / رفع إكسيل')

@section('content')
<div style="max-width: 900px; margin: 0 auto; padding-bottom: 2rem;">

    <div style="margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <a href="{{ route('rosters.index') }}" style="color: #64748b; text-decoration: none; font-size: 1.2rem;">←</a>
                <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0;">إنشاء جدول نوبتجيات جديد</h1>
            </div>
            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">
                ارفع شيت الإكسيل مباشرة وسيقوم السيستم بفهمه تلقائياً، أو أنشئ جدولاً فارغاً بالهيكل الافتراضي.
            </p>
        </div>

        <a href="{{ route('rosters.template.download', ['department' => 'تمريض']) }}" id="templateDownloadBtn" style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #334155; padding: 0.6rem 1rem; border-radius: 10px; font-weight: 700; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
            📥 تحميل نموذج Excel فارغ للقسم
        </a>
    </div>

    @if(session('error'))
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
        <span style="font-size: 1.25rem;">⚠️</span>
        <div style="font-weight: 600;">{{ session('error') }}</div>
    </div>
    @endif

    <div style="background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 2rem; box-shadow: 0 4px 20px -2px rgba(0,0,0,0.05);">
        <form action="{{ route('rosters.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- 1. القسم والعنوان --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.5rem;">
                        القسم المستهدف: <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="department" id="departmentSelect" onchange="updateDepartmentHelper(this.value)" required style="width: 100%; padding: 0.7rem 0.9rem; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.95rem; font-weight: 600; outline: none;">
                        <option value="تمريض" {{ old('department', $defaultDept) == 'تمريض' ? 'selected' : '' }}>🩺 تمريض (الأشعة، الرنين، السونار، المقطعية)</option>
                        <option value="استقبال" {{ old('department', $defaultDept) == 'استقبال' ? 'selected' : '' }}>🛎️ استقبال (الكاونتر، الطوارئ، الكول سنتر)</option>
                        <option value="عمال" {{ old('department', $defaultDept) == 'عمال' ? 'selected' : '' }}>🧹 خدمات معاونة وعمال</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.5rem;">
                        عنوان الجدول: <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="title" id="titleInput" value="{{ old('title', $defaultTitle) }}" required placeholder="مثال: جدول نوبتجيات تمريض أكتوبر 2026" style="width: 100%; padding: 0.7rem 0.9rem; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.95rem; font-weight: 600; outline: none;">
                </div>
            </div>

            {{-- 2. الفترة الزمنية --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.5rem;">
                        تاريخ بداية الفترة: <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="date" name="start_date" value="{{ old('start_date', $defaultStart) }}" required style="width: 100%; padding: 0.7rem 0.9rem; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.95rem; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.5rem;">
                        تاريخ نهاية الفترة: <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="date" name="end_date" value="{{ old('end_date', $defaultEnd) }}" required style="width: 100%; padding: 0.7rem 0.9rem; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.95rem; outline: none;">
                </div>
            </div>

            {{-- 3. منطقة رفع الإكسيل --}}
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 700; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.5rem;">
                    ملف الإكسيل (اختياري - يمكنك تركه فارغاً للبدء بجدول جاهز وتعديله يدورياً):
                </label>

                <div id="dropZone" style="border: 2px dashed #9c3b46; background: #fff9fa; border-radius: 14px; padding: 2rem 1.5rem; text-align: center; cursor: pointer; transition: all 0.2s;" onclick="document.getElementById('excelFileInput').click()">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📊</div>
                    <div style="font-weight: 700; color: #0f172a; font-size: 1rem; margin-bottom: 0.25rem;">
                        اضغط هنا لاختيار ملف الإكسيل (.xlsx أو .xls) أو اسحبه إلى هنا
                    </div>
                    <p style="color: #64748b; font-size: 0.85rem; margin: 0;">
                        يقوم السيستم بالتعرف التلقائي على أعمدة النوبتجيات (MRI, CT, X-Ray, US...) وجدول الجمعات
                    </p>
                    <div id="selectedFileName" style="display: none; margin-top: 1rem; background: #fdf2f8; border: 1px solid #fbcfe8; color: #9d174d; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                        📄 <span id="fileNameText"></span>
                    </div>
                </div>
                <input type="file" name="excel_file" id="excelFileInput" accept=".xlsx,.xls" style="display: none;" onchange="handleFileSelected(this)">
            </div>

            {{-- 4. ملاحظات --}}
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-weight: 700; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.5rem;">
                    ملاحظات أو توجيهات إدارية:
                </label>
                <textarea name="notes" rows="3" placeholder="أي تعليمات أو ملاحظات تخص توزيع الشيفتات في هذه الدورة..." style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.9rem; outline: none;">{{ old('notes') }}</textarea>
            </div>

            {{-- أزرار الإجراءات --}}
            <div style="display: flex; gap: 1rem; justify-content: flex-end; align-items: center;">
                <a href="{{ route('rosters.index') }}" style="background: #f1f5f9; color: #475569; padding: 0.75rem 1.4rem; border-radius: 10px; font-weight: 600; text-decoration: none;">إلغاء</a>
                <button type="submit" style="background: linear-gradient(135deg, #9c3b46 0%, #7c202a 100%); color: #fff; border: none; padding: 0.75rem 2rem; border-radius: 10px; font-weight: 800; font-size: 1rem; cursor: pointer; box-shadow: 0 4px 14px rgba(156, 59, 70, 0.4);">
                    🚀 حفظ وعرض الجدول
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    function updateDepartmentHelper(dept) {
        const downloadBtn = document.getElementById('templateDownloadBtn');
        downloadBtn.href = "{{ route('rosters.template.download') }}?department=" + encodeURIComponent(dept);
    }

    function handleFileSelected(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('fileNameText').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            document.getElementById('selectedFileName').style.display = 'inline-flex';
        }
    }

    // Drag and drop support
    const dropZone = document.getElementById('dropZone');
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.style.borderColor = '#2563eb';
            dropZone.style.background = '#eff6ff';
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.style.borderColor = '#9c3b46';
            dropZone.style.background = '#fff9fa';
        });
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length) {
            document.getElementById('excelFileInput').files = files;
            handleFileSelected(document.getElementById('excelFileInput'));
        }
    });
</script>
@endsection
