<?php

namespace App\Services;

use App\Models\ShiftGroup;
use App\Models\ShiftRoster;
use App\Models\ShiftRosterEntry;
use App\Models\User;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ShiftRosterExcelParser
{
    /**
     * خرائط مطابقة نوع الشيفت لمجموعة الشيفتات الرسمية
     */
    public static function mapShiftTypeToGroup(?string $shiftText): ?int
    {
        if (! $shiftText) {
            return null;
        }

        $clean = trim(mb_strtolower($shiftText));

        // أول / صباحي
        if (str_contains($clean, 'اول') || str_contains($clean, 'أول') || str_contains($clean, 'صباح')) {
            return 1;
        }
        // وسط
        if (str_contains($clean, 'وسط')) {
            return 2;
        }
        // تاني / ثاني / مسائي
        if (str_contains($clean, 'تان') || str_contains($clean, 'ثان') || str_contains($clean, 'مساء')) {
            return 3;
        }
        // سهر / ليلي
        if (str_contains($clean, 'سهر') || str_contains($clean, 'ليل')) {
            return 4;
        }

        return null;
    }

    /**
     * الهيكل الافتراضي لجدول التمريض المطابق لشيت الصورة المرفوعة
     */
    public static function getNursingDefaultGrid(): array
    {
        return [
            'main_table' => [
                'title' => 'جدول نوبتجيات التمريض الأسبوعي',
                'sections' => [
                    [
                        'name' => 'MRI',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => null, 'key' => 'mri_1'],
                            ['shift' => 'ثاني', 'sub' => null, 'key' => 'mri_2'],
                            ['shift' => 'سهر', 'sub' => null, 'key' => 'mri_night'],
                        ],
                    ],
                    [
                        'name' => 'ايكو',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => null, 'key' => 'echo_1'],
                        ],
                    ],
                    [
                        'name' => 'US -Doppler -4D',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => '4D', 'key' => 'us_4d'],
                            ['shift' => 'اول', 'sub' => 'سونار', 'key' => 'us_sonar'],
                            ['shift' => 'ثاني', 'sub' => null, 'key' => 'us_2'],
                        ],
                    ],
                    [
                        'name' => 'CT',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => null, 'key' => 'ct_1'],
                            ['shift' => 'ثاني', 'sub' => null, 'key' => 'ct_2'],
                        ],
                    ],
                    [
                        'name' => 'X-RAY',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => null, 'key' => 'xray_1'],
                            ['shift' => 'ثاني', 'sub' => null, 'key' => 'xray_2'],
                        ],
                    ],
                    [
                        'name' => 'MRI -X-RAY-DEXA',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => null, 'key' => 'mri_dexa_1'],
                            ['shift' => 'ثاني', 'sub' => null, 'key' => 'mri_dexa_2'],
                        ],
                    ],
                    [
                        'name' => 'حديث المدينه',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => null, 'key' => 'city_1'],
                            ['shift' => 'ثاني', 'sub' => null, 'key' => 'city_2'],
                        ],
                    ],
                ],
                'rows' => [
                    [
                        'day' => 'السبت',
                        'cells' => [
                            'mri_1' => 'هند', 'mri_2' => 'زينب', 'mri_night' => 'نورهان',
                            'echo_1' => 'هبه',
                            'us_4d' => 'ام ادهم', 'us_sonar' => 'يسرا', 'us_2' => 'ندي سلامه',
                            'ct_1' => 'صابرين', 'ct_2' => 'ملك',
                            'xray_1' => 'ياسمين', 'xray_2' => 'مها',
                            'mri_dexa_1' => 'مريم حبشي', 'mri_dexa_2' => 'ريم',
                            'city_1' => '', 'city_2' => 'دعاء',
                        ],
                    ],
                    [
                        'day' => 'الاحد',
                        'cells' => [
                            'mri_1' => 'ايمان', 'mri_2' => 'زينب', 'mri_night' => 'نورهان',
                            'echo_1' => 'هبه',
                            'us_4d' => 'ام ادهم', 'us_sonar' => 'مريم حبشي', 'us_2' => 'ملك',
                            'ct_1' => 'صابرين', 'ct_2' => 'ياسمين',
                            'xray_1' => 'رحمه', 'xray_2' => 'مها',
                            'mri_dexa_1' => 'ندي سلامه', 'mri_dexa_2' => 'ريم',
                            'city_1' => '', 'city_2' => 'دعاء',
                        ],
                    ],
                    [
                        'day' => 'الاثنين',
                        'cells' => [
                            'mri_1' => 'هند', 'mri_2' => 'زينب', 'mri_night' => 'نورهان',
                            'echo_1' => 'هبه',
                            'us_4d' => 'ام ادهم', 'us_sonar' => 'مريم حبشي', 'us_2' => 'ياسمين',
                            'ct_1' => 'يسرا', 'ct_2' => 'صابرين',
                            'xray_1' => 'رحمه', 'xray_2' => 'مها',
                            'mri_dexa_1' => 'ايمان', 'mri_dexa_2' => 'ندي سلامه',
                            'city_1' => '', 'city_2' => 'دعاء',
                        ],
                    ],
                    [
                        'day' => 'الثلاثاء',
                        'cells' => [
                            'mri_1' => 'هند', 'mri_2' => 'ندي سلامه', 'mri_night' => 'نورهان',
                            'echo_1' => 'هبه',
                            'us_4d' => 'ريم', 'us_sonar' => 'ام ادهم', 'us_2' => 'ملك',
                            'ct_1' => 'يسرا', 'ct_2' => 'صابرين',
                            'xray_1' => 'رحمه', 'xray_2' => 'مها',
                            'mri_dexa_1' => 'ايمان', 'mri_dexa_2' => 'ياسمين',
                            'city_1' => '', 'city_2' => 'دعاء',
                        ],
                    ],
                    [
                        'day' => 'الاربعاء',
                        'cells' => [
                            'mri_1' => 'هند', 'mri_2' => 'زينب', 'mri_night' => 'نورهان',
                            'echo_1' => 'هبه',
                            'us_4d' => 'ياسمين', 'us_sonar' => 'ام ادهم', 'us_2' => 'ملك',
                            'ct_1' => 'يسرا', 'ct_2' => 'صابرين',
                            'xray_1' => 'مها', 'xray_2' => 'ندي سلامه',
                            'mri_dexa_1' => 'رحمه', 'mri_dexa_2' => 'ريم',
                            'city_1' => 'دعاء', 'city_2' => 'ايمان',
                        ],
                    ],
                    [
                        'day' => 'الخميس',
                        'cells' => [
                            'mri_1' => 'هند', 'mri_2' => 'زينب', 'mri_night' => 'نورهان',
                            'echo_1' => 'هبه',
                            'us_4d' => 'مريم حبشي', 'us_sonar' => 'ام ادهم', 'us_2' => 'ريم',
                            'ct_1' => 'يسرا', 'ct_2' => 'ايمان',
                            'xray_1' => 'ياسمين', 'xray_2' => 'مها',
                            'mri_dexa_1' => 'ملك', 'mri_dexa_2' => 'ندي سلامه',
                            'city_1' => '', 'city_2' => 'دعاء',
                        ],
                    ],
                    [
                        'day' => 'الجمعه',
                        'cells' => [
                            'mri_1' => '', 'mri_2' => '', 'mri_night' => '',
                            'echo_1' => '',
                            'us_4d' => '', 'us_sonar' => '', 'us_2' => '',
                            'ct_1' => '', 'ct_2' => '',
                            'xray_1' => '', 'xray_2' => '',
                            'mri_dexa_1' => '', 'mri_dexa_2' => '',
                            'city_1' => 'رحمه', 'city_2' => 'ايمان',
                        ],
                    ],
                ],
            ],
            'friday_table' => [
                'title' => 'نوبتجيات أيام الجمعة',
                'sections' => [
                    [
                        'name' => 'MRI',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => null, 'key' => 'fri_mri_1'],
                            ['shift' => 'ثاني', 'sub' => null, 'key' => 'fri_mri_2'],
                            ['shift' => 'وسط', 'sub' => null, 'key' => 'fri_mri_mid'],
                        ],
                    ],
                    [
                        'name' => 'US',
                        'columns' => [
                            ['shift' => 'وسط', 'sub' => null, 'key' => 'fri_us_mid'],
                        ],
                    ],
                    [
                        'name' => 'CT',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => null, 'key' => 'fri_ct_1'],
                            ['shift' => 'ثاني', 'sub' => null, 'key' => 'fri_ct_2'],
                        ],
                    ],
                    [
                        'name' => 'panorama',
                        'columns' => [
                            ['shift' => 'وسط', 'sub' => null, 'key' => 'fri_pano_mid'],
                        ],
                    ],
                ],
                'rows' => [
                    [
                        'day' => 'الجمعه 9/4',
                        'cells' => [
                            'fri_mri_1' => 'ندي سلامه', 'fri_mri_2' => 'ندي سلامه', 'fri_mri_mid' => 'مها',
                            'fri_us_mid' => 'ياسمين',
                            'fri_ct_1' => 'ملك', 'fri_ct_2' => 'ريم',
                            'fri_pano_mid' => 'ريم',
                        ],
                    ],
                    [
                        'day' => 'الجمعه 9/11',
                        'cells' => [
                            'fri_mri_1' => 'مريم حبشي', 'fri_mri_2' => 'ندي سلامه', 'fri_mri_mid' => 'مها',
                            'fri_us_mid' => 'يسرا',
                            'fri_ct_1' => 'ملك', 'fri_ct_2' => 'ريم',
                            'fri_pano_mid' => 'ريم',
                        ],
                    ],
                    [
                        'day' => 'الجمعه 9/18',
                        'cells' => [
                            'fri_mri_1' => 'ياسمين', 'fri_mri_2' => 'دعاء', 'fri_mri_mid' => 'مها',
                            'fri_us_mid' => 'ملك',
                            'fri_ct_1' => 'ياسمين', 'fri_ct_2' => 'ندي سلامه',
                            'fri_pano_mid' => 'ندي سلامه',
                        ],
                    ],
                    [
                        'day' => 'الجمعه 9/25',
                        'cells' => [
                            'fri_mri_1' => 'زينب', 'fri_mri_2' => 'ايمان', 'fri_mri_mid' => 'مها',
                            'fri_us_mid' => 'ملك',
                            'fri_ct_1' => 'ملك', 'fri_ct_2' => 'ريم',
                            'fri_pano_mid' => 'ريم',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * الهيكل الافتراضي لجدول الاستقبال
     */
    public static function getReceptionDefaultGrid(): array
    {
        return [
            'main_table' => [
                'title' => 'جدول نوبتجيات الاستقبال الأسبوعي',
                'sections' => [
                    [
                        'name' => 'كاونتر 1 (الرئيسي)',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => null, 'key' => 'c1_1'],
                            ['shift' => 'ثاني', 'sub' => null, 'key' => 'c1_2'],
                            ['shift' => 'سهر', 'sub' => null, 'key' => 'c1_night'],
                        ],
                    ],
                    [
                        'name' => 'كاونتر 2',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => null, 'key' => 'c2_1'],
                            ['shift' => 'ثاني', 'sub' => null, 'key' => 'c2_2'],
                        ],
                    ],
                    [
                        'name' => 'كول سنتر',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => null, 'key' => 'cc_1'],
                            ['shift' => 'ثاني', 'sub' => null, 'key' => 'cc_2'],
                            ['shift' => 'سهر', 'sub' => null, 'key' => 'cc_night'],
                        ],
                    ],
                    [
                        'name' => 'خزينة / طوارئ',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => null, 'key' => 'cash_1'],
                            ['shift' => 'ثاني', 'sub' => null, 'key' => 'cash_2'],
                        ],
                    ],
                ],
                'rows' => [
                    ['day' => 'السبت', 'cells' => []],
                    ['day' => 'الاحد', 'cells' => []],
                    ['day' => 'الاثنين', 'cells' => []],
                    ['day' => 'الثلاثاء', 'cells' => []],
                    ['day' => 'الاربعاء', 'cells' => []],
                    ['day' => 'الخميس', 'cells' => []],
                    ['day' => 'الجمعه', 'cells' => []],
                ],
            ],
            'friday_table' => [
                'title' => 'نوبتجيات استقبال أيام الجمعة',
                'sections' => [
                    [
                        'name' => 'الاستقبال الرئيسي',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => null, 'key' => 'fri_rec_1'],
                            ['shift' => 'ثاني', 'sub' => null, 'key' => 'fri_rec_2'],
                        ],
                    ],
                    [
                        'name' => 'كول سنتر وطوارئ',
                        'columns' => [
                            ['shift' => 'وسط', 'sub' => null, 'key' => 'fri_rec_mid'],
                            ['shift' => 'سهر', 'sub' => null, 'key' => 'fri_rec_night'],
                        ],
                    ],
                ],
                'rows' => [
                    ['day' => 'الجمعة الأولى', 'cells' => []],
                    ['day' => 'الجمعة الثانية', 'cells' => []],
                    ['day' => 'الجمعة الثالثة', 'cells' => []],
                    ['day' => 'الجمعة الرابعة', 'cells' => []],
                ],
            ],
        ];
    }

    /**
     * الهيكل الافتراضي لجدول العمال
     */
    public static function getWorkersDefaultGrid(): array
    {
        return [
            'main_table' => [
                'title' => 'جدول نوبتجيات الخدمات المعاونة والعمال',
                'sections' => [
                    [
                        'name' => 'أدوار الفحص والأشعة',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => null, 'key' => 'w_rad_1'],
                            ['shift' => 'ثاني', 'sub' => null, 'key' => 'w_rad_2'],
                            ['shift' => 'سهر', 'sub' => null, 'key' => 'w_rad_night'],
                        ],
                    ],
                    [
                        'name' => 'الاستقبال والمداخل',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => null, 'key' => 'w_ent_1'],
                            ['shift' => 'ثاني', 'sub' => null, 'key' => 'w_ent_2'],
                        ],
                    ],
                    [
                        'name' => 'بوفيه واستراحات',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => null, 'key' => 'w_buf_1'],
                            ['shift' => 'ثاني', 'sub' => null, 'key' => 'w_buf_2'],
                        ],
                    ],
                ],
                'rows' => [
                    ['day' => 'السبت', 'cells' => []],
                    ['day' => 'الاحد', 'cells' => []],
                    ['day' => 'الاثنين', 'cells' => []],
                    ['day' => 'الثلاثاء', 'cells' => []],
                    ['day' => 'الاربعاء', 'cells' => []],
                    ['day' => 'الخميس', 'cells' => []],
                    ['day' => 'الجمعه', 'cells' => []],
                ],
            ],
            'friday_table' => [
                'title' => 'نوبتجيات العمال أيام الجمعة',
                'sections' => [
                    [
                        'name' => 'نظافة وخدمات شاملة',
                        'columns' => [
                            ['shift' => 'اول', 'sub' => null, 'key' => 'fri_w_1'],
                            ['shift' => 'ثاني', 'sub' => null, 'key' => 'fri_w_2'],
                            ['shift' => 'سهر', 'sub' => null, 'key' => 'fri_w_night'],
                        ],
                    ],
                ],
                'rows' => [
                    ['day' => 'الجمعة الأولى', 'cells' => []],
                    ['day' => 'الجمعة الثانية', 'cells' => []],
                    ['day' => 'الجمعة الثالثة', 'cells' => []],
                    ['day' => 'الجمعة الرابعة', 'cells' => []],
                ],
            ],
        ];
    }

    public static function getDefaultGridForDepartment(string $department): array
    {
        return match ($department) {
            'استقبال' => self::getReceptionDefaultGrid(),
            'عمال'    => self::getWorkersDefaultGrid(),
            default   => self::getNursingDefaultGrid(),
        };
    }

    /**
     * قراءة وفهم ملف إكسيل مرفوع وتحويله لهيكل Grid + Entries
     */
    public function parseUploadedExcel(string $filePath, string $department = 'تمريض'): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $sheetData = $sheet->toArray(null, true, true, true);

        // تنظيف الصفوف الفارغة بالكامل
        $cleanRows = [];
        foreach ($sheetData as $rIdx => $row) {
            $hasData = false;
            foreach ($row as $val) {
                if ($val !== null && trim((string) $val) !== '') {
                    $hasData = true;
                    break;
                }
            }
            if ($hasData) {
                $cleanRows[$rIdx] = $row;
            }
        }

        if (empty($cleanRows)) {
            throw new \Exception('ملف الإكسيل فارغ ولا يحتوي على بيانات.');
        }

        // إذا كان الشيت متبع هيكل النظام، نقوم باستخراجه بذكاء
        // نبحث عن أماكن كلمة "اليوم" أو أسماء الأيام
        $daysOfWeek = ['السبت', 'الاحد', 'الأحد', 'الاثنين', 'الإثنين', 'الثلاثاء', 'الاربعاء', 'الأربعاء', 'الخميس', 'الجمعه', 'الجمعة'];

        // نبحث عن صف رأس الجدول الرئيسي (Header Row)
        $headerRowIdx = null;
        $dayColKey = null;

        foreach ($cleanRows as $rIdx => $row) {
            foreach ($row as $colKey => $val) {
                $v = trim((string) $val);
                if ($v === 'اليوم' || str_contains($v, 'اليوم')) {
                    $headerRowIdx = $rIdx;
                    $dayColKey = $colKey;
                    break 2;
                }
            }
        }

        // إذا لم نجد كلمة "اليوم"، نبحث عن أول صف يظهر فيه يوم "السبت"
        if (! $headerRowIdx) {
            foreach ($cleanRows as $rIdx => $row) {
                foreach ($row as $colKey => $val) {
                    $v = trim((string) $val);
                    if (str_contains($v, 'السبت')) {
                        $headerRowIdx = max(1, $rIdx - 2);
                        $dayColKey = $colKey;
                        break 2;
                    }
                }
            }
        }

        // استخدام الهيكل الافتراضي كأساس، ثم ملء البيانات المستخرجة
        $grid = self::getDefaultGridForDepartment($department);

        // نقوم باستخراج أسماء الموظفين من خلايا الشيت
        // ونحاول مطابقتها مع الأيام والأعمدة
        $mainRows = &$grid['main_table']['rows'];
        $fridayRows = &$grid['friday_table']['rows'];

        // سنقوم بمسح أسماء الموظفين للبحث عن مطابقات الأيام
        foreach ($cleanRows as $rIdx => $row) {
            $rowDay = null;
            $matchedCol = null;

            // فحص هل هذا الصف يمثل يوماً من الأيام
            foreach ($row as $colKey => $val) {
                $v = trim((string) $val);
                foreach ($daysOfWeek as $dow) {
                    if ($v === $dow || (str_starts_with($v, $dow) && ! str_contains($v, '9/'))) {
                        $rowDay = $dow;
                        $matchedCol = $colKey;
                        break 2;
                    }
                }
            }

            // إذا وجدنا يوماً عادياً (سبت - خميس أو جمعه أسبوعية)
            if ($rowDay) {
                // نبحث عن الصف المقابل في الهيكل
                $normDay = match ($rowDay) {
                    'الأحد' => 'الاحد',
                    'الإثنين' => 'الاثنين',
                    'الأربعاء' => 'الاربعاء',
                    'الجمعة' => 'الجمعه',
                    default => $rowDay,
                };

                foreach ($mainRows as &$targetRow) {
                    if ($targetRow['day'] === $normDay) {
                        // نجمع القيم النصية غير الفارغة من هذا الصف
                        $extractedValues = [];
                        foreach ($row as $k => $cVal) {
                            if ($k === $matchedCol) {
                                continue;
                            }
                            $cValStr = trim((string) $cVal);
                            if ($cValStr !== '') {
                                $extractedValues[] = $cValStr;
                            }
                        }

                        // إذا كان لدينا قيم، نوزعها على خلايا هذا اليوم
                        if (! empty($extractedValues)) {
                            $cellKeys = array_keys($targetRow['cells']);
                            $valIdx = 0;
                            foreach ($cellKeys as $cKey) {
                                if (isset($extractedValues[$valIdx])) {
                                    $targetRow['cells'][$cKey] = $extractedValues[$valIdx];
                                    $valIdx++;
                                }
                            }
                        }
                    }
                }
            }

            // فحص هل هذا الصف يمثل يوم جمعة مخصص بتاريخ (مثل الجمعه 9/4)
            $isFridayDated = false;
            $fridayLabel = null;
            foreach ($row as $colKey => $val) {
                $v = trim((string) $val);
                if (str_contains($v, 'الجمعه') || str_contains($v, 'الجمعة')) {
                    if (preg_match('/\d+[\/\-]\d+/', $v)) {
                        $isFridayDated = true;
                        $fridayLabel = $v;
                        break;
                    }
                }
            }

            if ($isFridayDated && $fridayLabel) {
                // نوزع قيمه على جدول الجمعات
                $extractedFriValues = [];
                foreach ($row as $k => $cVal) {
                    $cValStr = trim((string) $cVal);
                    if ($cValStr !== '' && ! str_contains($cValStr, 'الجمعه') && ! str_contains($cValStr, 'الجمعة')) {
                        $extractedFriValues[] = $cValStr;
                    }
                }

                // إضافة صف جديد أو تحديث الموجود
                $foundFriday = false;
                foreach ($fridayRows as &$fRow) {
                    if ($fRow['day'] === $fridayLabel) {
                        $cellKeys = array_keys($fRow['cells']);
                        $valIdx = 0;
                        foreach ($cellKeys as $cKey) {
                            if (isset($extractedFriValues[$valIdx])) {
                                $fRow['cells'][$cKey] = $extractedFriValues[$valIdx];
                                $valIdx++;
                            }
                        }
                        $foundFriday = true;
                        break;
                    }
                }

                if (! $foundFriday && ! empty($extractedFriValues)) {
                    $newFriCells = [];
                    // بناء المفاتيح من أقسام جدول الجمعات
                    $allFriKeys = [];
                    foreach ($grid['friday_table']['sections'] as $sec) {
                        foreach ($sec['columns'] as $c) {
                            $allFriKeys[] = $c['key'];
                        }
                    }
                    foreach ($allFriKeys as $idx => $kName) {
                        $newFriCells[$kName] = $extractedFriValues[$idx] ?? '';
                    }
                    $fridayRows[] = [
                        'day' => $fridayLabel,
                        'cells' => $newFriCells,
                    ];
                }
            }
        }

        return $grid;
    }

    /**
     * بناء سجلات ShiftRosterEntry من هيكل الـ Grid
     */
    public function syncEntriesFromGrid(ShiftRoster $roster, array $grid): void
    {
        // حذف الإدخالات القديمة
        $roster->entries()->delete();

        // جلب أسماء الموظفين للربط مع جدول المستخدمين users
        $allUsers = User::select('id', 'name')->get();

        $entriesToInsert = [];
        $rIdx = 0;

        // 1. الجدول الرئيسي
        $colMap = [];
        $cIndex = 0;
        foreach ($grid['main_table']['sections'] as $sec) {
            foreach ($sec['columns'] as $col) {
                $colMap[$col['key']] = [
                    'section' => $sec['name'],
                    'sub'     => $col['sub'] ?? null,
                    'shift'   => $col['shift'],
                    'group_id' => self::mapShiftTypeToGroup($col['shift']),
                    'col_idx' => $cIndex++,
                ];
            }
        }

        foreach ($grid['main_table']['rows'] as $row) {
            $dayName = $row['day'];
            $rIdx++;

            foreach ($row['cells'] as $cellKey => $empName) {
                $empName = trim((string) $empName);
                if ($empName === '') {
                    continue;
                }

                $meta = $colMap[$cellKey] ?? [
                    'section' => 'عام',
                    'sub' => null,
                    'shift' => 'اول',
                    'group_id' => 1,
                    'col_idx' => 0,
                ];

                // محاولة مطابقة الاسم مع مستخدم مسجل
                $matchedUser = $this->findMatchingUser($empName, $allUsers);

                $entriesToInsert[] = [
                    'shift_roster_id' => $roster->id,
                    'user_id'         => $matchedUser?->id,
                    'employee_name'   => $empName,
                    'department'      => $roster->department,
                    'section_name'    => $meta['section'],
                    'sub_section'     => $meta['sub'],
                    'shift_type'      => $meta['shift'],
                    'shift_group_id'  => $meta['group_id'],
                    'day_name'        => $dayName,
                    'specific_date'   => null,
                    'is_friday'       => false,
                    'col_index'       => $meta['col_idx'],
                    'row_index'       => $rIdx,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
        }

        // 2. جدول الجمعات
        $friColMap = [];
        $fcIndex = 0;
        foreach ($grid['friday_table']['sections'] as $sec) {
            foreach ($sec['columns'] as $col) {
                $friColMap[$col['key']] = [
                    'section'  => $sec['name'],
                    'sub'      => $col['sub'] ?? null,
                    'shift'    => $col['shift'],
                    'group_id' => self::mapShiftTypeToGroup($col['shift']),
                    'col_idx'  => $fcIndex++,
                ];
            }
        }

        $fRowIdx = 0;
        foreach ($grid['friday_table']['rows'] as $row) {
            $dayName = $row['day'];
            $fRowIdx++;

            // فحص هل التاريخ موجود في الاسم (مثلاً الجمعه 9/4)
            $specificDate = null;
            if (preg_match('/(\d+)[\/\-](\d+)/', $dayName, $m)) {
                $year = $roster->start_date ? $roster->start_date->year : (int) date('Y');
                $month = (int) $m[1];
                $day = (int) $m[2];
                try {
                    $specificDate = Carbon::create($year, $month, $day)->toDateString();
                } catch (\Throwable $e) {
                    $specificDate = null;
                }
            }

            foreach ($row['cells'] as $cellKey => $empName) {
                $empName = trim((string) $empName);
                if ($empName === '') {
                    continue;
                }

                $meta = $friColMap[$cellKey] ?? [
                    'section' => 'عام',
                    'sub' => null,
                    'shift' => 'اول',
                    'group_id' => 1,
                    'col_idx' => 0,
                ];

                $matchedUser = $this->findMatchingUser($empName, $allUsers);

                $entriesToInsert[] = [
                    'shift_roster_id' => $roster->id,
                    'user_id'         => $matchedUser?->id,
                    'employee_name'   => $empName,
                    'department'      => $roster->department,
                    'section_name'    => $meta['section'],
                    'sub_section'     => $meta['sub'],
                    'shift_type'      => $meta['shift'],
                    'shift_group_id'  => $meta['group_id'],
                    'day_name'        => $dayName,
                    'specific_date'   => $specificDate,
                    'is_friday'       => true,
                    'col_index'       => $meta['col_idx'],
                    'row_index'       => $fRowIdx,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
        }

        if (! empty($entriesToInsert)) {
            ShiftRosterEntry::insert($entriesToInsert);
        }
    }

    /**
     * مطابقة ذكية للاسم مع مستخدم في النظام
     */
    protected function findMatchingUser(string $name, $users): ?User
    {
        $clean = mb_strtolower(trim($name));
        $clean = str_replace(['أ', 'إ', 'آ'], 'ا', $clean);
        $clean = str_replace('ة', 'ه', $clean);
        $clean = str_replace('ى', 'ي', $clean);

        foreach ($users as $u) {
            $uName = mb_strtolower(trim($u->name));
            $uNameNorm = str_replace(['أ', 'إ', 'آ'], 'ا', $uName);
            $uNameNorm = str_replace('ة', 'ه', $uNameNorm);
            $uNameNorm = str_replace('ى', 'ي', $uNameNorm);

            // تطابق كامل
            if ($clean === $uNameNorm) {
                return $u;
            }

            // الاسم الأول
            $uParts = explode(' ', $uNameNorm);
            $firstPart = $uParts[0] ?? '';
            if (mb_strlen($clean) >= 3 && ($clean === $firstPart || str_starts_with($uNameNorm, $clean . ' '))) {
                return $u;
            }
        }

        return null;
    }

    /**
     * تصدير الجدول إلى ملف إكسيل بتنسيق وألوان مطابقة 100% للشيت
     */
    public function exportExcel(ShiftRoster $roster): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);
        $sheet->setTitle(mb_substr($roster->title, 0, 30));

        $grid = $roster->grid_data ?? self::getDefaultGridForDepartment($roster->department);

        // ألوان مطابقة لصورة الشيت
        $burgundy = '9C3B46'; // رأس الأقسام الرئيسي
        $softRose = 'F3D3D6'; // رأس الشيفتات الفرعية
        $deepWine = '7C202A'; // شريط الجمعات
        $borderCol = 'D8A7AD'; // لون الحدود الزهرية الناعمة

        // صف البداية
        $currentRow = 2;

        // عنوان الشيت أعلى الصفحة
        $sheet->setCellValue('A' . $currentRow, $roster->title . ' - قسم ' . $roster->department . ' (' . $roster->formatted_period . ')');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('7C202A');
        $currentRow += 2;

        // ==========================================
        // 1. الجدول الرئيسي
        // ==========================================
        $mainSecs = $grid['main_table']['sections'];
        
        // حساب إجمالي الأعمدة
        $totalCols = 1; // لعمود اليوم
        foreach ($mainSecs as $s) {
            $totalCols += count($s['columns']);
        }

        // صف اليوم ورؤوس الأقسام (Row 1 of table)
        $r1 = $currentRow;
        $r2 = $currentRow + 1;
        $r3 = $currentRow + 2;

        // عمود "اليوم"
        $sheet->setCellValue('A' . $r1, 'اليوم');
        $sheet->mergeCells("A{$r1}:A{$r3}");
        $this->styleHeaderCell($sheet, "A{$r1}:A{$r3}", $softRose, '5A1A22', 12, true);

        $colIdx = 2; // Column B
        foreach ($mainSecs as $s) {
            $colCount = count($s['columns']);
            $startLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $endLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + $colCount - 1);

            // اسم القسم الرئيسي
            $sheet->setCellValue($startLetter . $r1, $s['name']);
            if ($colCount > 1) {
                $sheet->mergeCells("{$startLetter}{$r1}:{$endLetter}{$r1}");
            }
            $this->styleHeaderCell($sheet, "{$startLetter}{$r1}:{$endLetter}{$r1}", $softRose, '5A1A22', 11, true);

            // الأعمدة الفرعية
            foreach ($s['columns'] as $c) {
                $cLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                if (! empty($c['sub'])) {
                    // إذا كان هناك تصنيف فرعي (مثل سونار / 4D تحت أول)
                    $sheet->setCellValue($cLetter . $r2, $c['shift']);
                    $sheet->setCellValue($cLetter . $r3, $c['sub']);
                } else {
                    $sheet->setCellValue($cLetter . $r2, $c['shift']);
                    $sheet->mergeCells("{$cLetter}{$r2}:{$cLetter}{$r3}");
                }
                $this->styleHeaderCell($sheet, $cLetter . $r2, $softRose, '5A1A22', 11, true);
                if (! empty($c['sub'])) {
                    $this->styleHeaderCell($sheet, $cLetter . $r3, $softRose, '5A1A22', 10, true);
                }
                $colIdx++;
            }
        }

        $currentRow = $r3 + 1;

        // صفوف الأيام في الجدول الرئيسي
        foreach ($grid['main_table']['rows'] as $row) {
            $sheet->setCellValue('A' . $currentRow, $row['day']);
            $this->styleDataCell($sheet, 'A' . $currentRow, true);

            $cIdx = 2;
            foreach ($mainSecs as $s) {
                foreach ($s['columns'] as $c) {
                    $cLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx);
                    $val = $row['cells'][$c['key']] ?? '';
                    $sheet->setCellValue($cLetter . $currentRow, $val);
                    $this->styleDataCell($sheet, $cLetter . $currentRow);
                    $cIdx++;
                }
            }
            $currentRow++;
        }

        // مسافة بين الجدولين
        $currentRow += 2;

        // ==========================================
        // 2. جدول الجمعات (أسفل الجدول)
        // ==========================================
        if (! empty($grid['friday_table']['sections'])) {
            $friSecs = $grid['friday_table']['sections'];
            $friTotalCols = 1;
            foreach ($friSecs as $s) {
                $friTotalCols += count($s['columns']);
            }

            // شريط علوي بلون عنابي غامق
            $friR1 = $currentRow;
            $friR2 = $currentRow + 1;

            // عمود "اليوم" للجمعة
            $sheet->setCellValue('A' . $friR1, 'اليوم');
            $sheet->mergeCells("A{$friR1}:A{$friR2}");
            $this->styleHeaderCell($sheet, "A{$friR1}:A{$friR2}", $softRose, '5A1A22', 11, true);

            $fColIdx = 2;
            foreach ($friSecs as $s) {
                $colCount = count($s['columns']);
                $startLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($fColIdx);
                $endLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($fColIdx + $colCount - 1);

                $sheet->setCellValue($startLetter . $friR1, $s['name']);
                if ($colCount > 1) {
                    $sheet->mergeCells("{$startLetter}{$friR1}:{$endLetter}{$friR1}");
                }
                $this->styleHeaderCell($sheet, "{$startLetter}{$friR1}:{$endLetter}{$friR1}", $softRose, '5A1A22', 11, true);

                foreach ($s['columns'] as $c) {
                    $cLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($fColIdx);
                    $sheet->setCellValue($cLetter . $friR2, $c['shift']);
                    $this->styleHeaderCell($sheet, $cLetter . $friR2, $softRose, '5A1A22', 10, true);
                    $fColIdx++;
                }
            }

            $currentRow = $friR2 + 1;

            // صفوف الجمعات
            foreach ($grid['friday_table']['rows'] as $row) {
                $sheet->setCellValue('A' . $currentRow, $row['day']);
                $this->styleDataCell($sheet, 'A' . $currentRow, true);

                $fIdx = 2;
                foreach ($friSecs as $s) {
                    foreach ($s['columns'] as $c) {
                        $cLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($fIdx);
                        $val = $row['cells'][$c['key']] ?? '';
                        $sheet->setCellValue($cLetter . $currentRow, $val);
                        $this->styleDataCell($sheet, $cLetter . $currentRow);
                        $fIdx++;
                    }
                }
                $currentRow++;
            }
        }

        // ضبط عرض الأعمدة تلقائياً
        for ($col = 1; $col <= max($totalCols, 12); $col++) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($letter)->setWidth(14);
        }

        // حفظ في ملف مؤقت
        $tempDir = storage_path('app/temp_exports');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filename = 'roster_' . $roster->id . '_' . date('Ymd_His') . '.xlsx';
        $fullPath = $tempDir . '/' . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($fullPath);

        return $fullPath;
    }

    protected function styleHeaderCell($sheet, string $range, string $bgColor, string $textColor, int $fontSize = 11, bool $bold = true): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => $bold,
                'size' => $fontSize,
                'color' => ['rgb' => $textColor],
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $bgColor],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D8A7AD'],
                ],
            ],
        ]);
    }

    protected function styleDataCell($sheet, string $cell, bool $isHeader = false): void
    {
        $sheet->getStyle($cell)->applyFromArray([
            'font' => [
                'bold' => $isHeader,
                'size' => 11,
                'color' => ['rgb' => '1E293B'],
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D8A7AD'],
                ],
            ],
        ]);
    }
}
