<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'basic_incentive')) {
                $table->decimal('basic_incentive', 10, 2)->default(0)->nullable()->after('hourly_rate');
            }
            if (!Schema::hasColumn('users', 'hire_date')) {
                $table->date('hire_date')->nullable()->after('basic_incentive');
            }
            if (!Schema::hasColumn('users', 'evaluation')) {
                $table->string('evaluation', 10)->nullable()->after('hire_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('users', 'basic_incentive')) $cols[] = 'basic_incentive';
            if (Schema::hasColumn('users', 'hire_date')) $cols[] = 'hire_date';
            if (Schema::hasColumn('users', 'evaluation')) $cols[] = 'evaluation';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
