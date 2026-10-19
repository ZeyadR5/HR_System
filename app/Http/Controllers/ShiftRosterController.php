<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ShiftRoster;
use App\Models\ShiftRosterEntry;
use App\Models\User;
use App\Services\ShiftRosterExcelParser;
use App\Support\PayrollPeriod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftRosterController extends Controller
{
    public function __construct(protected ShiftRosterExcelParser $parser)
    {
    }

    /**
     * عرض قائمة جداول النوبتجيات
     */
    public function index(Request $request)
    {
        $query = ShiftRoster::with('creator')->withCount('entries')->orderByDesc('start_date')->orderByDesc('id');

        if ($request->filled('department') && $request->department !== 'all') {
            $query->where('department', $request->department);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                  ->orWhere('notes', 'like', "%{$q}%");
            });
        }

        $rosters = $query->paginate(15)->withQueryString();

        // إحصائيات سريعة
        $counts = [
            'total'     => ShiftRoster::count(),
            'nursing'   => ShiftRoster::where('department', 'تمريض')->count(),
            'reception' => ShiftRoster::where('department', 'استقبال')->count(),
            'workers'   => ShiftRoster::where('department', 'عمال')->count(),
        ];

        return view('rosters.index', compact('rosters', 'counts'));
    }

    /**
     * شاشة إنشاء جدول نوبتجيات جديد (عبر رفع إكسيل أو إنشاء يدوي)
     */
    public function create(Request $request)
    {
        $currentPeriod = PayrollPeriod::current();
        $defaultDept = $request->get('department', 'تمريض');

        return view('rosters.create', [
            'defaultDept'   => $defaultDept,
            'defaultStart'  => $currentPeriod->start->toDateString(),
            'defaultEnd'    => $currentPeriod->end->toDateString(),
            'defaultTitle'  => 'جدول نوبتجيات ' . $defaultDept . ' - ' . $currentPeriod->label,
        ]);
    }

    /**
     * حفظ الجدول الجديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'department'  => 'required|string|max:100',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'excel_file'  => 'nullable|file|mimes:xlsx,xls|max:10240',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $gridData = null;

        // إذا تم رفع ملف إكسيل
        if ($request->hasFile('excel_file')) {
            try {
                $file = $request->file('excel_file');
                $gridData = $this->parser->parseUploadedExcel($file->getRealPath(), $request->department);
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'حدث خطأ أثناء قراءة ملف الإكسيل: ' . $e->getMessage());
            }
        } else {
            // استخدام الهيكل الافتراضي للقسم
            $gridData = ShiftRosterExcelParser::getDefaultGridForDepartment($request->department);
        }

        $roster = ShiftRoster::create([
            'title'       => $request->title,
            'department'  => $request->department,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'grid_data'   => $gridData,
            'notes'       => $request->notes,
            'created_by'  => Auth::id(),
        ]);

        // مزامنة الإدخالات لربط الأسماء بالشيفتات
        if ($gridData) {
            $this->parser->syncEntriesFromGrid($roster, $gridData);
        }

        AuditLog::log(
            Auth::user()?->name ?? 'System',
            'Created',
            'ShiftRoster',
            $roster->title,
            "أنشأ جدول نوبتجيات لقسم {$roster->department}"
        );

        return redirect()->route('rosters.show', $roster->id)->with('success', 'تم إنشاء جدول النوبتجيات بنجاح!');
    }

    /**
     * عرض شيت النوبتجيات بتصميم مطابق تماماً للملف الورقي
     */
    public function show(ShiftRoster $roster)
    {
        $grid = $roster->grid_data ?? ShiftRosterExcelParser::getDefaultGridForDepartment($roster->department);

        // قائمة بجميع الموظفين للاختيار السريع عند التعديل
        $allUsers = User::select('id', 'name', 'job_title')->orderBy('name')->get();

        // إحصائيات الورديات والموظفين في هذا الجدول
        $entriesCount = $roster->entries()->count();
        $uniqueEmployees = $roster->entries()->distinct('employee_name')->pluck('employee_name');

        return view('rosters.show', compact('roster', 'grid', 'allUsers', 'entriesCount', 'uniqueEmployees'));
    }

    /**
     * تعديل بيانات الجدول
     */
    public function edit(ShiftRoster $roster)
    {
        return view('rosters.edit', compact('roster'));
    }

    /**
     * تحديث بيانات الجدول أو إعادة رفع شيت جديد
     */
    public function update(Request $request, ShiftRoster $roster)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'department'  => 'required|string|max:100',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'excel_file'  => 'nullable|file|mimes:xlsx,xls|max:10240',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $roster->title = $request->title;
        $roster->department = $request->department;
        $roster->start_date = $request->start_date;
        $roster->end_date = $request->end_date;
        $roster->notes = $request->notes;

        // إذا تم رفع ملف إكسيل جديد لتحديث البيانات
        if ($request->hasFile('excel_file')) {
            try {
                $file = $request->file('excel_file');
                $gridData = $this->parser->parseUploadedExcel($file->getRealPath(), $request->department);
                $roster->grid_data = $gridData;
                $this->parser->syncEntriesFromGrid($roster, $gridData);
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'خطأ في معالجة الإكسيل: ' . $e->getMessage());
            }
        }

        $roster->save();

        AuditLog::log(
            Auth::user()?->name ?? 'System',
            'Updated',
            'ShiftRoster',
            $roster->title,
            "عدّل جدول نوبتجيات {$roster->department}"
        );

        return redirect()->route('rosters.show', $roster->id)->with('success', 'تم حفظ التعديلات بنجاح.');
    }

    /**
     * تعديل خلية واحدة تفاعلياً مباشرة من الصفحة (AJAX)
     */
    public function updateCell(Request $request, ShiftRoster $roster)
    {
        $request->validate([
            'table_type' => 'required|in:main_table,friday_table',
            'day'        => 'required|string',
            'cell_key'   => 'required|string',
            'name'       => 'nullable|string|max:100',
        ]);

        $grid = $roster->grid_data ?? ShiftRosterExcelParser::getDefaultGridForDepartment($roster->department);

        $tableType = $request->table_type;
        $dayName = $request->day;
        $cellKey = $request->cell_key;
        $newName = trim((string) $request->name);

        $updated = false;
        if (isset($grid[$tableType]['rows'])) {
            foreach ($grid[$tableType]['rows'] as &$row) {
                if ($row['day'] === $dayName) {
                    $row['cells'][$cellKey] = $newName;
                    $updated = true;
                    break;
                }
            }
        }

        if (! $updated) {
            return response()->json(['success' => false, 'message' => 'لم يتم العثور على الصف المحدد.'], 404);
        }

        $roster->grid_data = $grid;
        $roster->save();

        // مزامنة الإدخالات في الخلفية
        $this->parser->syncEntriesFromGrid($roster, $grid);

        return response()->json([
            'success'       => true,
            'message'       => 'تم تحديث الاسم بنجاح!',
            'employee_name' => $newName,
        ]);
    }

    /**
     * تصدير الجدول إلى إكسيل بنفس التصميم تماماً
     */
    public function export(ShiftRoster $roster)
    {
        try {
            $filePath = $this->parser->exportExcel($roster);
            $filename = 'جدول_نوبتجيات_' . $roster->department . '_' . $roster->start_date->format('Y-m-d') . '.xlsx';

            return response()->download($filePath, $filename)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return back()->with('error', 'حدث خطأ أثناء تصدير ملف الإكسيل: ' . $e->getMessage());
        }
    }

    /**
     * تحميل نموذج إكسيل فارغ معبأ برؤوس الأعمدة للقسم
     */
    public function downloadTemplate(Request $request)
    {
        $dept = $request->get('department', 'تمريض');

        $fakeRoster = new ShiftRoster([
            'title'      => 'نموذج جدول نوبتجيات ' . $dept,
            'department' => $dept,
            'start_date' => now()->startOfMonth(),
            'end_date'   => now()->endOfMonth(),
            'grid_data'  => ShiftRosterExcelParser::getDefaultGridForDepartment($dept),
        ]);

        $filePath = $this->parser->exportExcel($fakeRoster);
        $filename = 'نموذج_نوبتجيات_' . $dept . '.xlsx';

        return response()->download($filePath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * حذف الجدول
     */
    public function destroy(ShiftRoster $roster)
    {
        $title = $roster->title;
        $roster->delete();

        AuditLog::log(
            Auth::user()?->name ?? 'System',
            'Deleted',
            'ShiftRoster',
            $title,
            'حذف جدول النوبتجيات بالكامل'
        );

        return redirect()->route('rosters.index')->with('success', 'تم حذف جدول النوبتجيات بنجاح.');
    }
}
