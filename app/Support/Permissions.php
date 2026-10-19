<?php

namespace App\Support;

/**
 * سجل الصلاحيات المركزي للنظام.
 * كل صلاحية هنا ممكن المطور يمنحها أو يسحبها من أي فئة من صفحة "إدارة الفئات والصلاحيات".
 */
class Permissions
{
    public const DEVELOPER_ROLE = 'developer';
    public const DEFAULT_ROLE   = 'user';

    /**
     * المجموعات والصلاحيات (key => label)
     */
    public static function groups(): array
    {
        return [
            'overtime' => [
                'label' => 'الإضافي',
                'icon'  => '⏱️',
                'permissions' => [
                    'overtime.view_all' => 'عرض إضافي كل الموظفين + الإجماليات',
                    'overtime.approve'  => 'قبول / رفض طلبات الإضافي',
                    'overtime.export'   => 'تصدير الإضافي (Excel)',
                ],
            ],
            'leave' => [
                'label' => 'الإجازات',
                'icon'  => '🏝️',
                'permissions' => [
                    'leave.view_all' => 'عرض إجازات كل الموظفين',
                    'leave.approve'  => 'قبول / رفض الإجازات',
                    'leave.export'   => 'تصدير الإجازات (Excel)',
                ],
            ],
            'permission' => [
                'label' => 'الأذونات',
                'icon'  => '📋',
                'permissions' => [
                    'permission.view_all' => 'عرض أذونات كل الموظفين',
                    'permission.approve'  => 'قبول / رفض الأذونات',
                    'permission.export'   => 'تصدير الأذونات (Excel)',
                ],
            ],
            'checkinout' => [
                'label' => 'الحضور والانصراف',
                'icon'  => '⏳',
                'permissions' => [
                    'checkinout.self_register' => 'تسجيل حضور / انصراف من الموقع',
                    'checkinout.view_all'      => 'عرض سجل حضور كل الموظفين',
                    'checkinout.approve'       => 'قبول / رفض حركات الحضور',
                    'checkinout.export'        => 'تصدير الحضور (Excel)',
                ],
            ],
            'penalties' => [
                'label' => 'الجزاءات',
                'icon'  => '⚠️',
                'permissions' => [
                    'penalties.view_all' => 'عرض جزاءات كل الموظفين',
                    'penalties.create'   => 'إضافة جزاء',
                    'penalties.approve'  => 'اعتماد / رفض الجزاءات',
                    'penalties.export'   => 'تصدير الجزاءات (Excel)',
                ],
            ],
            'settlements' => [
                'label' => 'التسويات',
                'icon'  => '⚙️',
                'permissions' => [
                    'settlements.view_all'      => 'عرض تسويات كل الموظفين',
                    'settlements.approve'       => 'قبول / رفض التسويات',
                    'settlements.export'        => 'تصدير التسويات (Excel)',
                    'settlements.bypass_window' => 'تقديم تسوية/جزاء خارج الفترة (1 → 10)',
                ],
            ],
            'incentives' => [
                'label' => 'الحوافز',
                'icon'  => '🌟',
                'permissions' => [
                    'incentives.view'   => 'عرض الحوافز',
                    'incentives.create' => 'إضافة حافز',
                    'incentives.export' => 'تصدير الحوافز (Excel)',
                ],
            ],
            'admin_notes' => [
                'label' => 'ملاحظات الإدارة',
                'icon'  => '🗒️',
                'permissions' => [
                    'admin_notes.view'   => 'عرض ملاحظات الإدارة',
                    'admin_notes.create' => 'إضافة ملاحظة',
                    'admin_notes.export' => 'تصدير الملاحظات (Excel)',
                ],
            ],
            'employees' => [
                'label' => 'الموظفين',
                'icon'  => '👥',
                'permissions' => [
                    'employees.view_profiles'  => 'عرض ملفات الموظفين',
                    'employees.edit_profiles'  => 'تعديل ملفات الموظفين',
                    'employees.reset_password' => 'إعادة ضبط كلمة السر',
                    'employees.create'         => 'إضافة موظفين',
                    'employees.manual_entry'   => 'الإضافة اليدوية (إجازات، أذونات، إضافي للموظفين)',
                ],
            ],
            'reports' => [
                'label' => 'التقارير والسجلات',
                'icon'  => '📊',
                'permissions' => [
                    'reports.full_report' => 'التقرير الشامل + تصديره',
                    'reports.audit_log'   => 'سجل العمليات (Audit Log)',
                    'system.view_ip'      => 'عرض الـ IP ووقت التسجيل الدقيق',
                ],
            ],
            'rosters' => [
                'label' => 'جدول النوبتجيات والشيفتات',
                'icon'  => '📅',
                'permissions' => [
                    'rosters.view'   => 'عرض جدول النوبتجيات وتوزيع الشيفتات',
                    'rosters.manage' => 'إنشاء وتعديل ورفع جداول النوبتجيات (Excel)',
                ],
            ],
            'instructions' => [
                'label' => 'التعليمات والتنبيهات',
                'icon'  => '📢',
                'permissions' => [
                    'instructions.view'   => 'مشاهدة صفحة التعليمات',
                    'instructions.manage' => 'إضافة/حذف وتثبيت التعليمات',
                ],
            ],
        ];
    }

    /** كل مفاتيح الصلاحيات في مصفوفة مسطحة */
    public static function all(): array
    {
        $keys = [];
        foreach (static::groups() as $group) {
            $keys = array_merge($keys, array_keys($group['permissions']));
        }
        return $keys;
    }

    public static function exists(string $key): bool
    {
        return in_array($key, static::all(), true);
    }

    public static function label(string $key): string
    {
        foreach (static::groups() as $group) {
            if (isset($group['permissions'][$key])) {
                return $group['permissions'][$key];
            }
        }
        return $key;
    }

    /** تنظيف أي مصفوفة صلاحيات قادمة من فورم */
    public static function sanitize(?array $keys): array
    {
        return array_values(array_intersect(static::all(), $keys ?? []));
    }

    /**
     * الصلاحيات الافتراضية للفئات الأساسية — مطابقة لسلوك النظام قبل نظام الفئات الديناميكي.
     */
    public static function defaults(): array
    {
        $admin = [
            'overtime.view_all', 'overtime.approve',
            'leave.view_all', 'leave.approve',
            'permission.view_all', 'permission.approve',
            'checkinout.view_all',
            'penalties.view_all', 'penalties.create',
            'rosters.view', 'rosters.manage',
        ];

        return [
            'user'         => [],
            'user_checkin' => ['checkinout.self_register'],
            'admin'        => $admin,
            'super_admin'  => static::all(),
        ];
    }
}
