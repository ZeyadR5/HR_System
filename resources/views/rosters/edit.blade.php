@extends('layout')

@section('title', 'تعديل جدول: ' . $roster->title)

@section('content')
<div style="max-width: 900px; margin: 0 auto; padding-bottom: 2rem;">

    <div style="margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('rosters.show', $roster->id) }}" style="color: #64748b; text-decoration: none; font-size: 1.2rem;">←</a>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0;">تعديل بيانات الجدول</h1>
        </div>
        <a href="{{ route('rosters.show', $roster->id) }}" style="background: #f1f5f9; color: #334155; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.85rem; text-decoration: none;">
            👁️ العودة للشيت
        </a>
    </div>

    @if(session('error'))
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
        <span style="font-size: 1.25rem;">⚠️</span>
        <div style="font-weight: 600;">{{ session('error') }}</div>
    </div>
    @endif

    <div style="background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 2rem; box-shadow: 0 4px 20px -2px rgba(0,0,0,0.05);">
        <form action="{{ route('rosters.update', $roster->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.5rem;">
                        القسم: <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="department" required style="width: 100%; padding: 0.7rem 0.9rem; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.95rem; font-weight: 600; outline: none;">
                        <option value="تمريض" {{ old('department', $roster->department) == 'تمريض' ? 'selected' : '' }}>🩺 تمريض (الأشعة، الرنين، السونار، المقطعية)</option>
                        <option value="استقبال" {{ old('department', $roster->department) == 'استقبال' ? 'selected' : '' }}>🛎️ استقبال (الكاونتر، الطوارئ، الكول سنتر)</option>
                        <option value="عمال" {{ old('department', $roster->department) == 'عمال' ? 'selected' : '' }}>🧹 خدمات معاونة وعمال</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.5rem;">
                        عنوان الجدول: <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title', $roster->title) }}" required style="width: 100%; padding: 0.7rem 0.9rem; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.95rem; font-weight: 600; outline: none;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.5rem;">
                        تاريخ بداية الفترة: <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="date" name="start_date" value="{{ old('start_date', $roster->start_date->format('Y-m-d')) }}" required style="width: 100%; padding: 0.7rem 0.9rem; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.95rem; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.5rem;">
                        تاريخ نهاية الفترة: <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="date" name="end_date" value="{{ old('end_date', $roster->end_date->format('Y-m-d')) }}" required style="width: 100%; padding: 0.7rem 0.9rem; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.95rem; outline: none;">
                </div>
            </div>

            {{-- خيار إعادة رفع إكسيل محدث --}}
            <div style="margin-bottom: 1.5rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 1.25rem;">
                <label style="display: block; font-weight: 700; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.3rem;">
                    🔄 إعادة رفع شيت إكسيل محدث (اختياري):
                </label>
                <p style="color: #64748b; font-size: 0.82rem; margin: 0 0 0.8rem 0;">
                    إذا قمت برفع ملف إكسيل جديد، سيتم استبدال الشيفتات الحالية ببيانات الملف المرفوع.
                </p>
                <input type="file" name="excel_file" accept=".xlsx,.xls" style="font-size: 0.9rem;">
            </div>

            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-weight: 700; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.5rem;">
                    ملاحظات:
                </label>
                <textarea name="notes" rows="3" style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.9rem; outline: none;">{{ old('notes', $roster->notes) }}</textarea>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; align-items: center;">
                <a href="{{ route('rosters.show', $roster->id) }}" style="background: #f1f5f9; color: #475569; padding: 0.75rem 1.4rem; border-radius: 10px; font-weight: 600; text-decoration: none;">إلغاء</a>
                <button type="submit" style="background: #2563eb; color: #fff; border: none; padding: 0.75rem 2rem; border-radius: 10px; font-weight: 800; font-size: 1rem; cursor: pointer; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);">
                    حفظ التعديلات
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
