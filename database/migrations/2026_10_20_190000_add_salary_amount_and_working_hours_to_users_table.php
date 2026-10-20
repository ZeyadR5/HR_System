<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'salary_amount')) {
                $table->decimal('salary_amount', 10, 2)->nullable()->after('hourly_rate');
            }
            if (!Schema::hasColumn('users', 'working_hours')) {
                $table->decimal('working_hours', 8, 2)->nullable()->after('salary_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'working_hours')) {
                $table->dropColumn('working_hours');
            }
            if (Schema::hasColumn('users', 'salary_amount')) {
                $table->dropColumn('salary_amount');
            }
        });
    }
};
