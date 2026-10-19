@extends('layout')

@section('title', $roster->title . ' - جدول النوبتجيات')

@section('styles')
<style>
    /* ألوان شيت النوبتجيات المطابقة 1:1 لصورة العميل */
    :root {
        --roster-maroon: #9c3b46;
        --roster-dark-maroon: #7c202a;
        --roster-rose-header: #f3d3d6;
        --roster-rose-sub: #fce8ea;
        --roster-border: #d8a7ad;
        --roster-text: #4a151b;
        --roster-friday-header: #8a2a35;
    }

    .roster-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px -2px rgba(0,0,0,0.06);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    /* جدول النوبتجيات */
    .roster-table {
        width: 100%;
        border-collapse: collapse;
        direction: rtl;
        text-align: center;
        font-size: 0.95rem;
    }

    .roster-table th, .roster-table td {
        border: 1px solid var(--roster-border);
        padding: 0.65rem 0.5rem;
        vertical-align: middle;
        font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
    }

    /* رؤوس الأعمدة */
    .th-modality {
        background-color: var(--roster-rose-header);
        color: var(--roster-text);
        font-weight: 800;
        font-size: 1.05rem;
        letter-spacing: 0.5px;
    }

    .th-shift {
        background-color: var(--roster-rose-header);
        color: var(--roster-text);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .th-sub {
        background-color: var(--roster-rose-sub);
        color: var(--roster-text);
        font-weight: 600;
        font-size: 0.88rem;
    }

    .th-day-col {
        background-color: var(--roster-rose-header);
        color: var(--roster-text);
        font-weight: 800;
        font-size: 1.1rem;
        width: 90px;
    }

    /* صفوف الأيام */
    .td-day {
        background-color: #fff;
        color: var(--roster-text);
        font-weight: 800;
        font-size: 1rem;
    }

    .roster-table tr:hover td {
        background-color: #fff9fa;
    }

    /* خلايا الأسماء */
    .name-cell {
        font-weight: 700;
        color: #1e293b;
        min-width: 75px;
        cursor: default;
        position: relative;
        transition: all 0.2s;
    }

    .name-cell.editable {
        cursor: pointer;
    }

    .name-cell.editable:hover {
        background-color: #fef2f2 !important;
        outline: 2px dashed #9c3b46;
        outline-offset: -2px;
    }

    .name-cell.highlighted {
        background-color: #fef08a !important;
        color: #854d0e !important;
        transform: scale(1.04);
        box-shadow: 0 0 10px rgba(234, 179, 8, 0.4);
        z-index: 2;
    }

    /* شريط الجمعات */
    .friday-banner {
        background: var(--roster-dark-maroon);
        height: 28px;
        margin: 1.5rem 0 1rem 0;
        border-radius: 4px;
    }

    .friday-table-wrapper {
        display: flex;
        justify-content: flex-start;
        overflow-x: auto;
    }

    .friday-table {
        border-collapse: collapse;
        direction: rtl;
        text-align: center;
        font-size: 0.95rem;
        min-width: 600px;
        max-width: 850px;
    }

    .friday-table th, .friday-table td {
        border: 1px solid var(--roster-border);
        padding: 0.6rem 0.5rem;
        vertical-align: middle;
    }

    /* الطباعة */
    @media print {
        .main-nav, .no-print, .footer-bar, #global-loader, .alert {
            display: none !important;
        }

        body {
            background: #fff !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .roster-card {
            box-shadow: none !important;
            border: none !important;
        }

        .roster-table, .friday-table {
            font-size: 8pt !important;
        }

        .roster-table th, .roster-table td,
        .friday-table th, .friday-table td {
            padding: 3px 2px !important;
        }

        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 12px;
        }

        @page {
            size: A4 landscape;
            margin: 8mm;
        }
    }
</style>
@endsection

@section('content')
<div style="max-width: 1550px; margin: 0 auto; padding-bottom: 2rem;">

    {{-- تنبيهات النظام --}}
    @if(session('success'))
    <div class="alert no-print" style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span style="font-size: 1.25rem;">✅</span>
            <div style="font-weight: 600;">{{ session('success') }}</div>
        </div>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #065f46;">×</button>
    </div>
    @endif

    {{-- رأس الصفحة --}}
    <div class="no-print" style="background: #fff; border-radius: 16px; padding: 1.5rem 1.8rem; border: 1px solid #e2e8f0; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.3rem;">
                <a href="{{ route('rosters.index') }}" style="color: #64748b; text-decoration: none; font-size: 1.2rem;">←</a>
                <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0;">{{ $roster->title }}</h1>
                @php $badge = $roster->department_badge; @endphp
                <span style="background: {{ $badge['bg'] }}; color: {{ $badge['text'] }}; border: 1px solid {{ $badge['border'] }}; padding: 0.2rem 0.65rem; border-radius: 20px; font-weight: 700; font-size: 0.8rem;">
                    {{ $badge['icon'] }} قسم {{ $roster->department }}
                </span>
            </div>
            <div style="color: #64748b; font-size: 0.88rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <span>📅 الفترة: <strong>{{ $roster->formatted_period }}</strong></span>
                <span>•</span>
                <span>👥 الموظفين المسجلين: <strong>{{ $uniqueEmployees->count() }} موظف</strong></span>
                <span>•</span>
                <span>⏱️ إجمالي النوبتجيات: <strong>{{ $entriesCount }}</strong></span>
            </div>
        </div>

        {{-- أزرار التحكم السريع --}}
        <div style="display: flex; gap: 0.6rem; flex-wrap: wrap; align-items: center;">
            {{-- زر تفعيل التعديل التفاعلي --}}
            <button id="toggleEditModeBtn" onclick="toggleEditMode()" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 0.6rem 1rem; border-radius: 10px; font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; transition: all 0.2s;">
                <span>✏️</span>
                <span id="editBtnText">تفعيل التعديل المباشر</span>
            </button>

            {{-- تصدير إكسيل مطابق --}}
            <a href="{{ route('rosters.export', $roster->id) }}" style="background: #15803d; color: #fff; border: none; padding: 0.6rem 1.1rem; border-radius: 10px; font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; box-shadow: 0 4px 12px rgba(21, 128, 61, 0.25);">
                📥 تحميل Excel مطابق
            </a>

            {{-- طباعة الشيت --}}
            <button onclick="window.print()" style="background: #0f172a; color: #fff; border: none; padding: 0.6rem 1.1rem; border-radius: 10px; font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                🖨️ طباعة الجدول
            </button>

            @can('rosters.manage')
            <a href="{{ route('rosters.edit', $roster->id) }}" style="background: #f8fafc; color: #475569; border: 1px solid #cbd5e1; padding: 0.6rem 0.9rem; border-radius: 10px; font-size: 0.88rem; text-decoration: none;">
                ⚙️ إعدادات الجدول
            </a>
            @endcan
        </div>
    </div>

    {{-- شريط أدوات تصفية الأسماء والشيفتات --}}
    <div class="no-print" style="background: #fff; border-radius: 14px; padding: 0.9rem 1.25rem; border: 1px solid #e2e8f0; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1; max-width: 450px;">
            <span style="font-size: 0.9rem; font-weight: 700; color: #334155; white-space: nowrap;">🔍 تظليل موظف:</span>
            <input type="text" id="employeeSearchInput" oninput="highlightEmployee(this.value)" placeholder="اكتب اسم الموظف لتظليله في كامل الجدول (مثلاً: دعاء، هند، ريم)..." style="width: 100%; padding: 0.45rem 0.8rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; outline: none;">
        </div>

        {{-- مفتاح أوقات الشيفتات المعتمدة في النظام --}}
        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <span style="font-size: 0.82rem; font-weight: 700; color: #64748b;">توقيت الشيفتات:</span>
            <span style="background: #dbeafe; color: #1e40af; padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.78rem; font-weight: 700;">☀️ اول: 9 ص - 4 م</span>
            <span style="background: #fef3c7; color: #92400e; padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.78rem; font-weight: 700;">🌤️ وسط: 12 م - 7 م</span>
            <span style="background: #ffedd5; color: #9a3412; padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.78rem; font-weight: 700;">🌆 ثاني: 4 م - 11 م</span>
            <span style="background: #f3e8ff; color: #6b21a8; padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.78rem; font-weight: 700;">🌙 سهر: 11 م - 8 ص</span>
        </div>
    </div>

    {{-- رسالة وضع التعديل الفوري --}}
    <div id="editModeNotice" class="no-print" style="display: none; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 0.75rem 1.25rem; border-radius: 12px; margin-bottom: 1.25rem; font-size: 0.88rem; font-weight: 600; align-items: center; gap: 0.5rem;">
        <span>💡</span>
        <span>وضع التعديل المباشر مفعل: اضغط على أي خلية في الجدول لتعديل اسم الموظف فورياً وسيتم الحفظ تلقائياً!</span>
    </div>

    {{-- رأس ورقة الطباعة الرسمية --}}
    <div class="print-header" style="display: none;">
        <h2 style="font-size: 16pt; margin: 0; font-weight: 800; color: #7c202a;">{{ $roster->title }}</h2>
        <p style="font-size: 10pt; color: #475569; margin: 4px 0 10px 0;">قسم {{ $roster->department }} - الفترة: {{ $roster->formatted_period }}</p>
    </div>

    {{-- حاوية جدول النوبتجيات الرئيسي والجمعات --}}
    <div class="roster-card" style="padding: 1rem; overflow-x: auto;">

        {{-- 1. الجدول الرئيسي المطابق 1:1 لصورة الشيت --}}
        <table class="roster-table" id="mainRosterTable">
            <thead>
                {{-- الصف الأول: أسماء الأجهزة والأقسام --}}
                <tr>
                    <th rowspan="3" class="th-day-col">اليوم</th>

                    @foreach($grid['main_table']['sections'] as $sec)
                    @php $colSpan = count($sec['columns']); @endphp
                    <th colspan="{{ $colSpan }}" class="th-modality">
                        {{ $sec['name'] }}
                    </th>
                    @endforeach
                </tr>

                {{-- الصف الثاني: نوع الشيفت (أول، ثاني، سهر) مع مراعاة التقسيمات الفرعية مثل (4D / سونار تحت أول) --}}
                <tr>
                    @foreach($grid['main_table']['sections'] as $sec)
                        @php
                            $i = 0;
                            $count = count($sec['columns']);
                        @endphp
                        @while($i < $count)
                            @php
                                $col = $sec['columns'][$i];
                                // فحص هل هناك أعمدة متتالية بنفس الشيفت ولها sub (مثل 4D وسونار تحت أول)
                                $subCount = 0;
                                for ($j = $i; $j < $count; $j++) {
                                    if ($sec['columns'][$j]['shift'] === $col['shift'] && !empty($sec['columns'][$j]['sub'])) {
                                        $subCount++;
                                    } else {
                                        break;
                                    }
                                }
                            @endphp

                            @if($subCount > 1)
                                <th colspan="{{ $subCount }}" class="th-shift">
                                    {{ $col['shift'] }}
                                </th>
                                @php $i += $subCount; @endphp
                            @else
                                <th rowspan="{{ empty($col['sub']) ? 2 : 1 }}" class="th-shift">
                                    {{ $col['shift'] }}
                                </th>
                                @php $i++; @endphp
                            @endif
                        @endwhile
                    @endforeach
                </tr>

                {{-- الصف الثالث: التصنيفات الفرعية إن وجدت (مثل سونار و 4D) --}}
                <tr>
                    @foreach($grid['main_table']['sections'] as $sec)
                        @foreach($sec['columns'] as $col)
                            @if(!empty($col['sub']))
                                <th class="th-sub">{{ $col['sub'] }}</th>
                            @endif
                        @endforeach
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach($grid['main_table']['rows'] as $row)
                <tr data-day="{{ $row['day'] }}">
                    <td class="td-day">{{ $row['day'] }}</td>

                    @foreach($grid['main_table']['sections'] as $sec)
                        @foreach($sec['columns'] as $col)
                        @php
                            $val = $row['cells'][$col['key']] ?? '';
                        @endphp
                        <td class="name-cell"
                            data-table="main_table"
                            data-day="{{ $row['day'] }}"
                            data-cell="{{ $col['key'] }}"
                            data-name="{{ $val }}"
                            onclick="handleCellClick(this)">
                            <span class="cell-display">{{ $val }}</span>
                        </td>
                        @endforeach
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- 2. جدول الجمعات (أسفل الجدول مع الشريط العنابي الغامق) --}}
        @if(!empty($grid['friday_table']['sections']) && !empty($grid['friday_table']['rows']))
        <div class="friday-banner"></div>

        <div class="friday-table-wrapper">
            <table class="friday-table" id="fridayRosterTable">
                <thead>
                    {{-- الصف الأول للجمعات --}}
                    <tr>
                        <th rowspan="2" class="th-day-col" style="background: var(--roster-rose-header);">اليوم</th>
                        @foreach($grid['friday_table']['sections'] as $sec)
                        @php $fSpan = count($sec['columns']); @endphp
                        <th colspan="{{ $fSpan }}" class="th-modality" style="background: var(--roster-rose-header);">
                            {{ $sec['name'] }}
                        </th>
                        @endforeach
                    </tr>

                    {{-- الصف الثاني للجمعات: الشيفتات --}}
                    <tr>
                        @foreach($grid['friday_table']['sections'] as $sec)
                            @foreach($sec['columns'] as $col)
                            <th class="th-shift" style="background: var(--roster-rose-header);">
                                {{ $col['shift'] }}
                            </th>
                            @endforeach
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach($grid['friday_table']['rows'] as $row)
                    <tr data-day="{{ $row['day'] }}">
                        <td class="td-day" style="font-weight: 700;">{{ $row['day'] }}</td>

                        @foreach($grid['friday_table']['sections'] as $sec)
                            @foreach($sec['columns'] as $col)
                            @php
                                $val = $row['cells'][$col['key']] ?? '';
                            @endphp
                            <td class="name-cell"
                                data-table="friday_table"
                                data-day="{{ $row['day'] }}"
                                data-cell="{{ $col['key'] }}"
                                data-name="{{ $val }}"
                                onclick="handleCellClick(this)">
                                <span class="cell-display">{{ $val }}</span>
                            </td>
                            @endforeach
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>

    {{-- ملاحظات الجدول إن وجدت --}}
    @if($roster->notes)
    <div style="background: #fff; border-radius: 12px; padding: 1.25rem 1.5rem; border: 1px solid #e2e8f0; margin-top: 1rem;">
        <h4 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 0.4rem;">🗒️ ملاحظات إضافية على الجدول:</h4>
        <p style="color: #475569; font-size: 0.9rem; margin: 0; line-height: 1.6;">{{ $roster->notes }}</p>
    </div>
    @endif

</div>

{{-- نافذة التعديل السريع للخلية (Inline Edit Modal / Quick Autocomplete) --}}
<div id="quickEditModal" class="no-print" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 10000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 16px; width: 100%; max-width: 440px; padding: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); border: 1px solid #e2e8f0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0;">تعديل نوبتجية الموظف</h3>
            <button onclick="closeQuickEdit()" style="background: none; border: none; font-size: 1.3rem; color: #94a3b8; cursor: pointer;">×</button>
        </div>

        <div style="background: #f8fafc; border-radius: 10px; padding: 0.75rem; margin-bottom: 1.2rem; font-size: 0.85rem; color: #475569; line-height: 1.6;">
            <div>📅 اليوم: <strong id="modalDayLabel" style="color: #0f172a;"></strong></div>
            <div>🏥 الخلية: <strong id="modalCellLabel" style="color: #0f172a;"></strong></div>
        </div>

        <div style="margin-bottom: 1.2rem;">
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem;">اسم الموظف / النوبتجي:</label>
            <input type="text" id="modalNameInput" list="employeesDataList" placeholder="اكتب اسم الموظف..." style="width: 100%; padding: 0.65rem 0.85rem; border: 2px solid #2563eb; border-radius: 8px; font-size: 0.95rem; font-weight: 600; outline: none;">
            <datalist id="employeesDataList">
                @foreach($allUsers as $u)
                <option value="{{ $u->name }}">{{ $u->job_title ? '(' . $u->job_title . ')' : '' }}</option>
                @endforeach
            </datalist>
        </div>

        {{-- اختيارات سريعة من موظفي الجدول المعتادين --}}
        <div style="margin-bottom: 1.5rem;">
            <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 0.4rem;">اختيار سريع:</div>
            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; max-height: 100px; overflow-y: auto;">
                @foreach($uniqueEmployees as $emp)
                @if(trim($emp) !== '')
                <button type="button" onclick="selectQuickName('{{ $emp }}')" style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #334155; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer;">
                    {{ $emp }}
                </button>
                @endif
                @endforeach
                <button type="button" onclick="selectQuickName('')" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer;">
                    ✕ مسح (فارغ)
                </button>
            </div>
        </div>

        <div style="display: flex; gap: 0.6rem; justify-content: flex-end;">
            <button type="button" onclick="closeQuickEdit()" style="background: #f1f5f9; color: #475569; border: none; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 600; cursor: pointer;">إلغاء</button>
            <button type="button" onclick="saveQuickEdit()" id="modalSaveBtn" style="background: #2563eb; color: #fff; border: none; padding: 0.6rem 1.4rem; border-radius: 8px; font-weight: 700; cursor: pointer;">حفظ التغيير</button>
        </div>
    </div>
</div>

{{-- Toast Feedback notification --}}
<div id="rosterToast" style="display: none; position: fixed; bottom: 25px; left: 50%; transform: translateX(-50%); background: #0f172a; color: #fff; padding: 0.75rem 1.5rem; border-radius: 30px; font-weight: 700; font-size: 0.9rem; box-shadow: 0 10px 25px rgba(0,0,0,0.3); z-index: 10001; align-items: center; gap: 0.5rem; border: 1px solid rgba(255,255,255,0.15);">
    <span id="toastIcon">✅</span>
    <span id="toastMessage">تم الحفظ بنجاح</span>
</div>

<script>
    let isEditMode = false;
    let activeCell = null;

    // تبديل وضع التعديل الفوري
    function toggleEditMode() {
        isEditMode = !isEditMode;
        const btn = document.getElementById('toggleEditModeBtn');
        const text = document.getElementById('editBtnText');
        const notice = document.getElementById('editModeNotice');
        const cells = document.querySelectorAll('.name-cell');

        if (isEditMode) {
            btn.style.background = '#9c3b46';
            btn.style.color = '#fff';
            btn.style.borderColor = '#7c202a';
            text.textContent = 'إيقاف التعديل المباشر';
            notice.style.display = 'flex';
            cells.forEach(c => c.classList.add('editable'));
            showToast('تم تفعيل وضع التعديل المباشر! اضغط على أي خلية لتغيير الاسم.', '✏️');
        } else {
            btn.style.background = '#f1f5f9';
            btn.style.color = '#334155';
            btn.style.borderColor = '#cbd5e1';
            text.textContent = 'تفعيل التعديل المباشر';
            notice.style.display = 'none';
            cells.forEach(c => c.classList.remove('editable'));
            closeQuickEdit();
        }
    }

    // الضغط على خلية
    function handleCellClick(cell) {
        if (!isEditMode) return;

        activeCell = cell;
        const tableType = cell.getAttribute('data-table');
        const day = cell.getAttribute('data-day');
        const cellKey = cell.getAttribute('data-cell');
        const currentName = cell.getAttribute('data-name') || '';

        document.getElementById('modalDayLabel').textContent = day;
        document.getElementById('modalCellLabel').textContent = cellKey;
        const input = document.getElementById('modalNameInput');
        input.value = currentName;

        document.getElementById('quickEditModal').style.display = 'flex';
        input.focus();
        input.select();
    }

    function selectQuickName(name) {
        document.getElementById('modalNameInput').value = name;
        saveQuickEdit();
    }

    function closeQuickEdit() {
        document.getElementById('quickEditModal').style.display = 'none';
        activeCell = null;
    }

    // حفظ التعديل عبر AJAX
    function saveQuickEdit() {
        if (!activeCell) return;

        const newName = document.getElementById('modalNameInput').value.trim();
        const saveBtn = document.getElementById('modalSaveBtn');
        saveBtn.disabled = true;
        saveBtn.textContent = 'جاري الحفظ...';

        const tableType = activeCell.getAttribute('data-table');
        const day = activeCell.getAttribute('data-day');
        const cellKey = activeCell.getAttribute('data-cell');

        fetch("{{ route('rosters.update_cell', $roster->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                table_type: tableType,
                day: day,
                cell_key: cellKey,
                name: newName
            })
        })
        .then(res => res.json())
        .then(data => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'حفظ التغيير';

            if (data.success) {
                // تحديث الخلية في الواجهة مباشرة
                activeCell.setAttribute('data-name', newName);
                activeCell.querySelector('.cell-display').textContent = newName;
                showToast('تم تحديث ' + day + ' بنجاح!', '✅');
                closeQuickEdit();
            } else {
                alert(data.message || 'حدث خطأ أثناء الحفظ.');
            }
        })
        .catch(err => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'حفظ التغيير';
            alert('تعذر الاتصال بالسيرفر. يرجى المحاولة لاحقاً.');
        });
    }

    // تظليل اسم موظف في كافة الخلايا
    function highlightEmployee(query) {
        const q = query.trim().toLowerCase();
        const cells = document.querySelectorAll('.name-cell');

        cells.forEach(cell => {
            const name = (cell.getAttribute('data-name') || '').toLowerCase();
            if (q.length > 0 && name.includes(q)) {
                cell.classList.add('highlighted');
            } else {
                cell.classList.remove('highlighted');
            }
        });
    }

    // إظهار Toast صغير
    function showToast(message, icon = '✅') {
        const toast = document.getElementById('rosterToast');
        document.getElementById('toastIcon').textContent = icon;
        document.getElementById('toastMessage').textContent = message;
        toast.style.display = 'flex';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
    }

    // دعم زر Enter داخل حقل الاسم
    document.getElementById('modalNameInput')?.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            saveQuickEdit();
        } else if (e.key === 'Escape') {
            closeQuickEdit();
        }
    });
</script>
@endsection
