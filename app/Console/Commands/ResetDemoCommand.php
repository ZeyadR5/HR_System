<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ResetDemoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:reset {--force : تجاوز رسائل التأكيد والتشغيل الإجباري}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إعادة ضبط البيئة التجريبية (Sandbox) وتعبئتها بالبيانات الافتراضية الذكية';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $currentDb = Config::get('database.connections.mysql.database');
        $isDemoMode = env('DEMO_MODE', false) || Config::get('app.demo_mode', false) || str_contains($currentDb, 'demo');

        $this->info("==================================================");
        $this->info("       🚀 HR System - Demo Sandbox Reset          ");
        $this->info("==================================================");
        $this->line("قاعدة البيانات المستهدفة: <fg=yellow>{$currentDb}</>");

        // التحقق الأمني: إذا لم تكن قاعدة البيانات صراحة demo ولم يتم تمرير --force
        if (!$isDemoMode && !$this->option('force')) {
            $this->warn("⚠️ تنبيه أمني عالي الأهمية:");
            $this->warn("قاعدة البيانات الحالية ({$currentDb}) ليست معلمة كـ DEMO.");
            if (!$this->confirm('هل أنت متأكد تماماً من رغبتك في إعادة ضبط هذه الداتا بالكامل واستبدالها بالبيانات الوهمية؟', false)) {
                $this->error('تم إلغاء العملية لحماية بياناتك.');
                return 1;
            }
        }

        $this->comment('1. جاري إعادة بناء الجداول وتطبيق كافة الـ Migrations...');
        $exitCode = Artisan::call('migrate:fresh', [
            '--force'  => true,
        ], $this->output);

        if ($exitCode !== 0) {
            $this->error('فشل تطبيق الـ Migrations.');
            return $exitCode;
        }

        $this->comment('2. جاري زرع البيانات النموذجية الشاملة (DemoDatabaseSeeder)...');
        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\DemoDatabaseSeeder',
            '--force' => true,
        ], $this->output);

        if ($exitCode !== 0) {
            $this->error('فشل زرع البيانات.');
            return $exitCode;
        }

        $this->comment('3. جاري تنظيف الكاش المؤقت...');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        $this->newLine();
        $this->info('✨ تم إعادة ضبط البيئة التجريبية بنجاح 100%!');
        $this->newLine();
        $this->table(
            ['الدور', 'البريد الإلكتروني', 'كلمة المرور', 'الصلاحيات'],
            [
                ['مدير عام (Super Admin)', 'admin@demo.com', 'demo123456', 'كاملة (Full Access)'],
                ['مدير موارد بشرية (HR)', 'hr@demo.com', 'demo123456', 'إدارة الموظفين والملفات والتقارير'],
                ['مشرف عمليات (Supervisor)', 'supervisor@demo.com', 'demo123456', 'شفتات وإدخال يدوي سريع'],
                ['موظف عادي (Employee)', 'ahmed.fathy@demo.com', 'demo123456', 'تسجيل حضور وانصراف شخصي'],
            ]
        );

        return 0;
    }
}
