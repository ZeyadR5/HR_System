@extends('layout')

@section('title', 'جدول الشيفتات وتوزيع الشيفتات')

@section('content')
<div class="rosters-container" style="max-width: 1400px; margin: 0 auto; padding-bottom: 2rem;">

    {{-- رأس الصفحة والترحيب --}}
    <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 16px; padding: 1.8rem 2rem; color: #fff; margin-bottom: 2rem; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3); border: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.2rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 0.4rem;">
                <span style="background: linear-gradient(135deg, #9c3b46 0%, #7c202a 100%); width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 1.4rem; box-shadow: 0 4px 12px rgba(156, 59, 70, 0.4);">📅</span>
                <h1 style="font-size: 1.6rem; font-weight: 800; margin: 0; color: #fff;">جدول الشيفتات وتوزيع الشيفتات</h1>
            </div>
            <p style="color: #94a3b8; font-size: 0.95rem; margin: 0; line-height: 1.6;">
                إدارة وتوزيع شيفتات التمريض، الاستقبال، والعمال أسبوعياً وشهرياً مع إمكانية الرفع المباشر لملفات الإكسيل والتعديل الفوري.
            </p>
        </div>

        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a href="{{ route('rosters.template.download', ['department' => request('department', 'تمريض')]) }}" class="btn no-loader" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: #e2e8f0; padding: 0.65rem 1.1rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; transition: all 0.2s;">
                📥 تحميل نموذج Excel فارغ
            </a>

            @can('rosters.manage')
            <a href="{{ route('rosters.create') }}" class="btn" style="background: linear-gradient(135deg, #9c3b46 0%, #b84351 100%); color: #fff; padding: 0.65rem 1.3rem; border-radius: 10px; font-weight: 700; font-size: 0.92rem; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; box-shadow: 0 6px 18px rgba(156, 59, 70, 0.35); transition: transform 0.2s;">
                ➕ إضافة جدول جديد / رفع إكسيل
            </a>
            @endcan
        </div>
    </div>

    {{-- بطاقات الإحصائيات --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: #fff; border-radius: 14px; padding: 1.2rem 1.4rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
            <div style="background: #f1f5f9; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">📊</div>
            <div>
                <div style="color: #64748b; font-size: 0.85rem; font-weight: 600;">إجمالي الجداول المسجلة</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #0f172a;">{{ $counts['total'] }}</div>
            </div>
        </div>

        <div style="background: #fff; border-radius: 14px; padding: 1.2rem 1.4rem; border: 1px solid #fbcfe8; display: flex; align-items: center; gap: 1rem; box-shadow: 0 2px 8px rgba(251, 207, 232, 0.2);">
            <div style="background: #fdf2f8; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">🩺</div>
            <div>
                <div style="color: #9d174d; font-size: 0.85rem; font-weight: 600;">جداول التمريض</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #9d174d;">{{ $counts['nursing'] }}</div>
            </div>
        </div>

        <div style="background: #fff; border-radius: 14px; padding: 1.2rem 1.4rem; border: 1px solid #c7d2fe; display: flex; align-items: center; gap: 1rem; box-shadow: 0 2px 8px rgba(199, 210, 254, 0.2);">
            <div style="background: #eef2ff; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">🛎️</div>
            <div>
                <div style="color: #3730a3; font-size: 0.85rem; font-weight: 600;">جداول الاستقبال</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #3730a3;">{{ $counts['reception'] }}</div>
            </div>
        </div>

        <div style="background: #fff; border-radius: 14px; padding: 1.2rem 1.4rem; border: 1px solid #a7f3d0; display: flex; align-items: center; gap: 1rem; box-shadow: 0 2px 8px rgba(167, 243, 208, 0.2);">
            <div style="background: #ecfdf5; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">🧹</div>
            <div>
                <div style="color: #065f46; font-size: 0.85rem; font-weight: 600;">الخدمات والعمال</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #065f46;">{{ $counts['workers'] }}</div>
            </div>
        </div>
    </div>

    {{-- رسائل التنبيه والنجاح --}}
    @if(session('success'))
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
        <span style="font-size: 1.25rem;">✅</span>
        <div style="font-weight: 600;">{{ session('success') }}</div>
    </div>
    @endif

    @if(session('error'))
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
        <span style="font-size: 1.25rem;">⚠️</span>
        <div style="font-weight: 600;">{{ session('error') }}</div>
    </div>
    @endif

    {{-- شريط التصفية والبحث --}}
    <div style="background: #fff; border-radius: 14px; padding: 1.2rem; border: 1px solid #e2e8f0; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('rosters.index') }}" style="padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; font-size: 0.85rem; text-decoration: none; transition: all 0.2s; {{ !request('department') || request('department') == 'all' ? 'background: #0f172a; color: #fff;' : 'background: #f1f5f9; color: #475569;' }}">
                الكل ({{ $counts['total'] }})
            </a>
            <a href="{{ route('rosters.index', ['department' => 'تمريض']) }}" style="padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; font-size: 0.85rem; text-decoration: none; transition: all 0.2s; {{ request('department') == 'تمريض' ? 'background: #9d174d; color: #fff;' : 'background: #fdf2f8; color: #9d174d; border: 1px solid #fbcfe8;' }}">
                🩺 تمريض ({{ $counts['nursing'] }})
            </a>
            <a href="{{ route('rosters.index', ['department' => 'استقبال']) }}" style="padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; font-size: 0.85rem; text-decoration: none; transition: all 0.2s; {{ request('department') == 'استقبال' ? 'background: #3730a3; color: #fff;' : 'background: #eef2ff; color: #3730a3; border: 1px solid #c7d2fe;' }}">
                🛎️ استقبال ({{ $counts['reception'] }})
            </a>
            <a href="{{ route('rosters.index', ['department' => 'عمال']) }}" style="padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; font-size: 0.85rem; text-decoration: none; transition: all 0.2s; {{ request('department') == 'عمال' ? 'background: #065f46; color: #fff;' : 'background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;' }}">
                🧹 عمال ({{ $counts['workers'] }})
            </a>
        </div>

        <form method="GET" action="{{ route('rosters.index') }}" style="display: flex; gap: 0.5rem; flex: 1; max-width: 380px;">
            @if(request('department'))
            <input type="hidden" name="department" value="{{ request('department') }}">
            @endif
            <input type="text" name="q" value="{{ request('q') }}" placeholder="بحث بالاسم أو الفترة أو الملاحظات..." style="flex: 1; padding: 0.5rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.85rem; outline: none;">
            <button type="submit" style="background: #2563eb; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer;">بحث</button>
        </form>
    </div>

    {{-- جدول الجداول المسجلة --}}
    <div style="background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
        @if($rosters->count() > 0)
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: right;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-size: 0.88rem;">
                        <th style="padding: 1rem 1.25rem;">عنوان الجدول</th>
                        <th style="padding: 1rem 1.25rem;">القسم</th>
                        <th style="padding: 1rem 1.25rem;">فترة الشيفتات</th>
                        <th style="padding: 1rem 1.25rem;">عدد التوزيعات المسجلة</th>
                        <th style="padding: 1rem 1.25rem;">أضيف بواسطة</th>
                        <th style="padding: 1rem 1.25rem; text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.9rem; color: #1e293b;">
                    @foreach($rosters as $roster)
                    @php $badge = $roster->department_badge; @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#fbfcfe'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1rem 1.25rem; font-weight: 700;">
                            <a href="{{ route('rosters.show', $roster->id) }}" style="color: #0f172a; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                                <span>{{ $roster->title }}</span>
                            </a>
                            @if($roster->notes)
                            <div style="font-size: 0.78rem; color: #64748b; font-weight: 400; margin-top: 0.25rem;">{{ Str::limit($roster->notes, 60) }}</div>
                            @endif
                        </td>
                        <td style="padding: 1rem 1.25rem;">
                            <span style="background: {{ $badge['bg'] }}; color: {{ $badge['text'] }}; border: 1px solid {{ $badge['border'] }}; padding: 0.25rem 0.65rem; border-radius: 20px; font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                <span>{{ $badge['icon'] }}</span>
                                <span>{{ $roster->department }}</span>
                            </span>
                        </td>
                        <td style="padding: 1rem 1.25rem;">
                            <span style="font-family: monospace; font-size: 0.88rem; background: #f8fafc; padding: 0.2rem 0.5rem; border-radius: 6px; border: 1px solid #e2e8f0;">
                                {{ $roster->formatted_period }}
                            </span>
                        </td>
                        <td style="padding: 1rem 1.25rem;">
                            <span style="font-weight: 700; color: #2563eb;">{{ $roster->entries_count }}</span> نوبتجية
                        </td>
                        <td style="padding: 1rem 1.25rem; color: #64748b; font-size: 0.85rem;">
                            {{ $roster->creator?->name ?? 'غير محدد' }}
                            <div style="font-size: 0.75rem; color: #94a3b8;">{{ $roster->created_at->diffForHumans() }}</div>
                        </td>
                        <td style="padding: 1rem 1.25rem; text-align: center;">
                            <div style="display: inline-flex; gap: 0.4rem; align-items: center;">
                                <a href="{{ route('rosters.show', $roster->id) }}" title="عرض الجدول" style="background: #eff6ff; color: #1d4ed8; padding: 0.45rem 0.75rem; border-radius: 8px; font-weight: 600; font-size: 0.82rem; text-decoration: none; border: 1px solid #bfdbfe;">
                                    👁️ عرض وتعديل
                                </a>

                                <a href="{{ route('rosters.export', $roster->id) }}" class="no-loader" title="تصدير Excel بنفس التنسيق" style="background: #f0fdf4; color: #15803d; padding: 0.45rem 0.75rem; border-radius: 8px; font-weight: 600; font-size: 0.82rem; text-decoration: none; border: 1px solid #bbf7d0;">
                                    📥 إكسيل
                                </a>

                                @can('rosters.manage')
                                <a href="{{ route('rosters.edit', $roster->id) }}" title="تعديل البيانات أو إعادة الرفع" style="background: #f8fafc; color: #475569; padding: 0.45rem 0.65rem; border-radius: 8px; font-size: 0.82rem; text-decoration: none; border: 1px solid #cbd5e1;">
                                    ✏️
                                </a>

                                <form action="{{ route('rosters.destroy', $roster->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الجدول وجميع الشيفتاته؟');" style="display: inline;">
                                    @csrf
                                    <button type="submit" title="حذف الجدول" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; padding: 0.45rem 0.65rem; border-radius: 8px; font-size: 0.82rem; cursor: pointer;">
                                        🗑️
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9;">
            {{ $rosters->links() }}
        </div>
        @else
        <div style="text-align: center; padding: 4rem 1.5rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin-bottom: 0.5rem;">لا توجد جداول الشيفتات مسجلة حتى الآن</h3>
            <p style="color: #64748b; font-size: 0.92rem; max-width: 500px; margin: 0 auto 1.5rem; line-height: 1.6;">
                يمكنك إنشاء أول جدول الشيفتات الآن سواءً عن طريق رفع ملف الإكسيل الجاهز وسيقوم النظام بقراءته فوراً، أو البدء بجدول فارغ وتعديله تفاعلياً.
            </p>
            @can('rosters.manage')
            <a href="{{ route('rosters.create') }}" style="background: linear-gradient(135deg, #9c3b46 0%, #b84351 100%); color: #fff; padding: 0.75rem 1.5rem; border-radius: 10px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 14px rgba(156, 59, 70, 0.35);">
                ➕ إنشاء أول جدول الآن
            </a>
            @endcan
        </div>
        @endif
    </div>

</div>
@endsection
