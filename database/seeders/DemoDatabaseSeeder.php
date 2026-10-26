<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\CheckInOut;
use App\Models\Incentive;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Penalty;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Settlement;
use App\Models\ShiftGroup;
use App\Models\User;
use App\Support\Permissions;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DemoDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. مسح الجداول القديمة بأمان
        Schema::disableForeignKeyConstraints();
        
        $tables = [
            'audit_logs',
            'check_in_outs',
            'employee_documents',
            'incentives',
            'instructions',
            'leaves',
            'notes',
            'overtimes',
            'penalties',
            'permissions',
            'settlements',
            'shift_roster_entries',
            'shift_rosters',
            'shift_groups',
            'users',
            'roles',
        ];

        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl)) {
                DB::table($tbl)->truncate();
            }
        }

        Schema::enableForeignKeyConstraints();

        Role::flushCache();

        // 2. إنشاء الفئات والصلاحيات الأساسية
        $superAdminRole = Role::create([
            'name'         => 'super_admin',
            'display_name' => 'المدير العام (Super Admin)',
            'description'  => 'صلاحيات كاملة وغير محدودة للنظام',
            'color'        => '#dc2626',
            'is_system'    => true,
            'permissions'  => Permissions::all(),
        ]);

        $hrRole = Role::create([
            'name'         => 'hr_manager',
            'display_name' => 'مدير الموارد البشرية (HR Manager)',
            'description'  => 'إدارة الموظفين والملفات والتقارير والاعتمادات',
            'color'        => '#2563eb',
            'is_system'    => false,
            'permissions'  => [
                'employees.view', 'employees.create', 'employees.edit', 'employees.archive', 'employees.documents_manage', 'employees.avatar_manage',
                'overtime.view_all', 'overtime.view_totals', 'overtime.approve', 'overtime.export',
                'leave.view_all', 'leave.view_totals', 'leave.approve', 'leave.export',
                'permission.view_all', 'permission.view_totals', 'permission.approve', 'permission.export',
                'checkinout.view_all', 'checkinout.approve', 'checkinout.export',
                'penalties.view_all', 'penalties.create', 'penalties.approve',
                'salary.view', 'financial.penalties', 'financial.incentives', 'financial.settlements', 'financial.notes',
                'reports.view', 'reports.export',
                'dashboard.view', 'dashboard.charts',
            ],
        ]);

        $supervisorRole = Role::create([
            'name'         => 'supervisor',
            'display_name' => 'مشرف عمليات (Supervisor)',
            'description'  => 'متابعة الشفتات والإضافة اليدوية السريعة',
            'color'        => '#f59e0b',
            'is_system'    => false,
            'permissions'  => [
                'employees.view', 'employees.manual_entry',
                'overtime.view_all', 'overtime.approve',
                'leave.view_all', 'leave.approve',
                'permission.view_all', 'permission.approve',
                'checkinout.view_all',
                'dashboard.view',
            ],
        ]);

        $userRole = Role::create([
            'name'         => 'user',
            'display_name' => 'موظف (Employee)',
            'description'  => 'تسجيل الحضور والطلبات الشخصية',
            'color'        => '#10b981',
            'is_system'    => true,
            'permissions'  => [
                'checkinout.self_register',
            ],
        ]);

        // 3. مجموعات الشفتات (Shift Groups)
        $shiftMorning = ShiftGroup::create([
            'name'        => 'الشفت الصباحي الإداري',
            'from_time'   => '09:00',
            'to_time'     => '17:00',
            'color'       => '#3b82f6',
            'icon'        => '☀️',
            'description' => 'شفت الدوام المكتبي العام من التاسعة صباحاً حتى الخامسة مساءً',
        ]);

        $shiftEvening = ShiftGroup::create([
            'name'        => 'شفت الدعم والعمليات المسائي',
            'from_time'   => '16:00',
            'to_time'     => '00:00',
            'color'       => '#8b5cf6',
            'icon'        => '🌙',
            'description' => 'شفت التشغيل المسائي وخدمة العملاء',
        ]);

        $shiftFlexible = ShiftGroup::create([
            'name'        => 'الشفت التقني المرن',
            'from_time'   => '10:00',
            'to_time'     => '18:00',
            'color'       => '#10b981',
            'icon'        => '💻',
            'description' => 'شفت فريق التطوير وتكنولوجيا المعلومات',
        ]);

        // 4. الحسابات الإدارية الرئيسية للديمو
        $demoAdmin = User::create([
            'name'            => 'المدير العام التجريبي',
            'email'           => 'admin@demo.com',
            'password'        => Hash::make('demo123456'),
            'role'            => 'super_admin',
            'job_title'       => 'General Manager',
            'salary_amount'   => 35000,
            'working_hours'   => 40,
            'hourly_rate'     => round(35000 / (40 * 4.33), 2),
            'hire_date'       => '2023-01-01',
            'evaluation'      => 'A',
            'is_insured'      => true,
            'shift_group_id'  => $shiftMorning->id,
        ]);

        $demoHR = User::create([
            'name'            => 'سارة عبد الرحمن (HR)',
            'email'           => 'hr@demo.com',
            'password'        => Hash::make('demo123456'),
            'role'            => 'hr_manager',
            'job_title'       => 'HR Specialist',
            'salary_amount'   => 18000,
            'working_hours'   => 40,
            'hourly_rate'     => round(18000 / (40 * 4.33), 2),
            'hire_date'       => '2023-06-15',
            'evaluation'      => 'A',
            'is_insured'      => true,
            'shift_group_id'  => $shiftMorning->id,
        ]);

        $demoSupervisor = User::create([
            'name'            => 'م. كريم عادل (مشرف)',
            'email'           => 'supervisor@demo.com',
            'password'        => Hash::make('demo123456'),
            'role'            => 'supervisor',
            'job_title'       => 'Team Leader',
            'salary_amount'   => 16000,
            'working_hours'   => 40,
            'hourly_rate'     => round(16000 / (40 * 4.33), 2),
            'hire_date'       => '2024-02-01',
            'evaluation'      => 'B',
            'is_insured'      => true,
            'shift_group_id'  => $shiftEvening->id,
        ]);

        // 5. قائمة موظفين وهميين متنوعين
        $mockEmployeesData = [
            ['name' => 'أحمد فتحي محمود', 'email' => 'ahmed.fathy@demo.com', 'title' => 'Software Engineer', 'salary' => 22000, 'eval' => 'A', 'shift' => $shiftFlexible->id, 'insured' => true],
            ['name' => 'نور الهدى إبراهيم', 'email' => 'nour.hoda@demo.com', 'title' => 'Accountant', 'salary' => 14000, 'eval' => 'A', 'shift' => $shiftMorning->id, 'insured' => true],
            ['name' => 'محمود مصطفى حسن', 'email' => 'mahmoud.mostafa@demo.com', 'title' => 'Customer Care', 'salary' => 9500, 'eval' => 'B', 'shift' => $shiftEvening->id, 'insured' => false],
            ['name' => 'ياسمين طارق الشريف', 'email' => 'yasmin.tarek@demo.com', 'title' => 'Graphic Designer', 'salary' => 15000, 'eval' => 'A', 'shift' => $shiftFlexible->id, 'insured' => true],
            ['name' => 'عمر خالد الصاوي', 'email' => 'omar.khaled@demo.com', 'title' => 'Sales Executive', 'salary' => 12000, 'eval' => 'B', 'shift' => $shiftMorning->id, 'insured' => true],
            ['name' => 'رنا هاني القاضي', 'email' => 'rana.hany@demo.com', 'title' => 'Receptionist', 'salary' => 8500, 'eval' => 'C', 'shift' => $shiftMorning->id, 'insured' => false],
            ['name' => 'حسام الدين شريف', 'email' => 'hossam.shereef@demo.com', 'title' => 'IT Support', 'salary' => 13500, 'eval' => 'B', 'shift' => $shiftFlexible->id, 'insured' => true],
            ['name' => 'مريم عصام الجيار', 'email' => 'mariam.essam@demo.com', 'title' => 'Data Analyst', 'salary' => 17000, 'eval' => 'A', 'shift' => $shiftFlexible->id, 'insured' => true],
            ['name' => 'طارق نبيل رضوان', 'email' => 'tarek.nabil@demo.com', 'title' => 'Supply Chain', 'salary' => 13000, 'eval' => 'B', 'shift' => $shiftMorning->id, 'insured' => true],
            ['name' => 'إنجي سعيد عبد العزيز', 'email' => 'engy.saeed@demo.com', 'title' => 'Marketing', 'salary' => 14500, 'eval' => 'A', 'shift' => $shiftMorning->id, 'insured' => false],
            ['name' => 'مصطفى كمال البحيري', 'email' => 'mostafa.kamal@demo.com', 'title' => 'Customer Care', 'salary' => 9000, 'eval' => 'B', 'shift' => $shiftEvening->id, 'insured' => false],
            ['name' => 'دينا صلاح الدين', 'email' => 'dina.salah@demo.com', 'title' => 'Content Writer', 'salary' => 11000, 'eval' => 'B', 'shift' => $shiftFlexible->id, 'insured' => true],
        ];

        $createdEmployees = [];
        foreach ($mockEmployeesData as $i => $data) {
            $hours = 40;
            $rate = round($data['salary'] / ($hours * 4.33), 2);
            $hireDate = Carbon::now()->subMonths(rand(3, 24))->subDays(rand(1, 28))->toDateString();

            $emp = User::create([
                'name'            => $data['name'],
                'email'           => $data['email'],
                'password'        => Hash::make('demo123456'),
                'role'            => 'user',
                'job_title'       => $data['title'],
                'salary_amount'   => $data['salary'],
                'working_hours'   => $hours,
                'hourly_rate'     => $rate,
                'basic_incentive' => rand(0, 3) === 1 ? rand(500, 1500) : 0,
                'hire_date'       => $hireDate,
                'evaluation'      => $data['eval'],
                'is_insured'      => $data['insured'],
                'shift_group_id'  => $data['shift'],
            ]);

            $createdEmployees[] = $emp;
        }

        // 6. زرع حركات واقعية خلال الشهر الحالي (إجازات، أذونات، إضافي، جزاءات)
        $now = Carbon::now();
        $daysArabic = [
            0 => 'الأحد', 1 => 'الاثنين', 2 => 'الثلاثاء', 3 => 'الأربعاء',
            4 => 'الخميس', 5 => 'الجمعة', 6 => 'السبت'
        ];

        // 6.1 إجازات
        $leaveReasons = [
            'إجازة اعتيادية سنوية للسفر',
            'ظروف عائلية خاصة وطارئة',
            'إجازة راحة مرضية بموجب تقرير طبي',
            'تجديد بطاقة الرقم القومي وأوراق حكومية',
        ];

        foreach (array_slice($createdEmployees, 0, 8) as $idx => $emp) {
            $leaveDate = $now->copy()->subDays(rand(1, 20));
            $daysCount = ($idx % 3 === 0) ? 2 : 1;
            $status = ($idx === 7) ? 'pending' : (($idx === 6) ? 'refused' : 'accepted');

            Leave::create([
                'user_id'     => $emp->id,
                'name'        => $emp->name,
                'date'        => $leaveDate->toDateString(),
                'day'         => $daysArabic[$leaveDate->dayOfWeek],
                'reason'      => $leaveReasons[$idx % count($leaveReasons)],
                'substitute'  => 'أحد الزملاء بالقسم',
                'days_count'  => $daysCount,
                'status'      => $status,
                'actioned_by' => $status !== 'pending' ? 'المدير العام التجريبي' : null,
                'created_ip'  => '127.0.0.1',
            ]);
        }

        // 6.2 أذونات
        $permTypes = ['إذن تأخير', 'إذن انصراف باكر', 'إذن نسيان بصمة حضور', 'إذن نسيان بصمة انصراف'];
        $permReasons = [
            'ازدحام مروري شديد على المحور',
            'موعد طبي عاجل مع استشاري',
            'عطل مفاجئ بجهاز البصمة أثناء الدخول',
            'إنهاء معاملة بنكية رسمية خاصة بمرتبات',
        ];

        foreach (array_slice($createdEmployees, 0, 10) as $idx => $emp) {
            $permDate = $now->copy()->subDays(rand(1, 18));
            $type = $permTypes[$idx % count($permTypes)];
            $isTimeReq = in_array($type, ['إذن تأخير', 'إذن انصراف باكر']);

            Permission::create([
                'user_id'         => $emp->id,
                'name'            => $emp->name,
                'date'            => $permDate->toDateString(),
                'day'             => $daysArabic[$permDate->dayOfWeek],
                'reason'          => $permReasons[$idx % count($permReasons)],
                'permission_type' => $type,
                'from'            => $isTimeReq ? '09:00' : '09:00',
                'to'              => $isTimeReq ? ($type === 'إذن تأخير' ? '10:30' : '15:30') : '09:15',
                'status'          => ($idx === 9) ? 'pending' : 'accepted',
                'actioned_by'     => 'المدير العام التجريبي',
                'created_ip'      => '127.0.0.1',
            ]);
        }

        // 6.3 وقت إضافي
        $otReasons = [
            'إنهاء تقارير الإقفال المالي والحسابات',
            'تحديث السيرفرات وإطلاق التحديثات البرمجية',
            'تغطية عجز الوردية وتلبية اتصالات العملاء',
            'جرد المخازن السنوي ومطابقة العهدة',
        ];

        foreach (array_slice($createdEmployees, 0, 9) as $idx => $emp) {
            $otDate = $now->copy()->subDays(rand(2, 22));
            $hours = ($idx % 2 === 0) ? 2.5 : 3.0;

            Overtime::create([
                'user_id'     => $emp->id,
                'name'        => $emp->name,
                'date'        => $otDate->toDateString(),
                'day'         => $daysArabic[$otDate->dayOfWeek],
                'reason'      => $otReasons[$idx % count($otReasons)],
                'from'        => '17:00',
                'to'          => ($hours == 2.5) ? '19:30' : '20:00',
                'total_hours' => $hours,
                'status'      => ($idx === 8) ? 'pending' : 'accepted',
                'actioned_by' => 'المدير العام التجريبي',
                'created_ip'  => '127.0.0.1',
            ]);
        }

        // 6.4 جزاءات وخصومات
        $penaltyReasons = [
            'التأخر المتكرر عن بداية موعد الشيفت بدون إذن مسبق',
            'عدم ارتداء الزي الرسمي المعتمد لموظفي الاستقبال',
            'مخالفة تعليمات الأمن والسلامة في غرفة الخوادم',
        ];

        foreach (array_slice($createdEmployees, 2, 4) as $idx => $emp) {
            $penDate = $now->copy()->subDays(rand(3, 15));
            Penalty::create([
                'user_id'     => $emp->id,
                'name'        => $emp->name,
                'reason'      => $penaltyReasons[$idx % count($penaltyReasons)],
                'amount'      => ($idx % 2 === 0) ? 250 : 500,
                'notes'       => 'تم التنبيه والإنذار الشفهي أولاً',
                'status'      => 'accepted',
                'actioned_by' => 'سارة عبد الرحمن (HR)',
                'created_at'  => $penDate,
                'updated_at'  => $penDate,
            ]);
        }

        // 6.5 مكافآت وحوافز
        $incentiveReasons = [
            'مكافأة تميز في خدمة العملاء وتحقيق أعلى تقييم رضا (+1000 ج.م)',
            'حافز إنجاز مبكر لمشروع تكنولوجيا المعلومات (+1500 ج.م)',
            'مكافأة جهود استثنائية في تنظيم ملفات الموظفين (+800 ج.م)',
        ];

        foreach (array_slice($createdEmployees, 0, 3) as $idx => $emp) {
            $incDate = $now->copy()->subDays(rand(4, 12));
            Incentive::create([
                'user_id'     => $emp->id,
                'name'        => $emp->name,
                'evaluation'  => $incentiveReasons[$idx],
                'date'        => $incDate->toDateString(),
            ]);
        }

        // 6.6 تسويات وملاحظات إدارية
        foreach (array_slice($createdEmployees, 4, 2) as $idx => $emp) {
            $setlDate = $now->copy()->subDays(rand(2, 8));
            Settlement::create([
                'user_id'     => $emp->id,
                'name'        => $emp->name,
                'note'        => 'صرف بدل وجبات وانتقالات مهام خارجية بموافقة الإدارة',
                'date'        => $setlDate->toDateString(),
                'day'         => $daysArabic[$setlDate->dayOfWeek],
                'status'      => 'accepted',
                'accept_note' => 'معتمد للصرف مع راتب الشهر الحالي',
                'actioned_by' => 'سارة عبد الرحمن (HR)',
            ]);
        }

        // 6.7 حركات حضور وانصراف (CheckInOut)
        foreach (array_slice($createdEmployees, 0, 7) as $idx => $emp) {
            $chkDate = $now->copy()->subDays(rand(1, 5));
            CheckInOut::create([
                'user_id'     => $emp->id,
                'name'        => $emp->name,
                'date'        => $chkDate->toDateString(),
                'day'         => $daysArabic[$chkDate->dayOfWeek],
                'type'        => 'حضور',
                'status'      => 'accepted',
                'actioned_by' => 'المدير العام التجريبي',
            ]);

            CheckInOut::create([
                'user_id'     => $emp->id,
                'name'        => $emp->name,
                'date'        => $chkDate->toDateString(),
                'day'         => $daysArabic[$chkDate->dayOfWeek],
                'type'        => 'انصراف',
                'status'      => 'accepted',
                'actioned_by' => 'المدير العام التجريبي',
            ]);
        }

        // 7. سجل رقابي حي (Audit Log)
        AuditLog::log('المدير العام التجريبي', 'System Seeded', 'Demo Environment', 'HR Sandbox', 'تهيئة قاعدة البيانات التجريبية ببيانات الموظفين والشفتات والأرصدة.');
        AuditLog::log('سارة عبد الرحمن (HR)', 'Updated Profile', 'Employee Profile', $createdEmployees[0]->name, 'تحديث بيانات الراتب الأساسي وتقييم الأداء إلى A.');
        AuditLog::log('المدير العام التجريبي', 'Approved', 'Leave Request', $createdEmployees[1]->name, 'اعتماد طلب إجازة سنوية.');
    }
}
