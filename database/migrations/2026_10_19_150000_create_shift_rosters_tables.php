<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_rosters', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('department')->default('تمريض'); // تمريض، استقبال، عمال، إلخ
            $table->date('start_date');
            $table->date('end_date');
            $table->longText('grid_data')->nullable(); // JSON representation of full table for 1:1 rendering
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('shift_roster_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_roster_id')->constrained('shift_rosters')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_name');
            $table->string('department');
            $table->string('section_name'); // MRI, CT, X-RAY, US...
            $table->string('sub_section')->nullable(); // سونار، 4D...
            $table->string('shift_type'); // اول، وسط، ثاني، سهر
            $table->foreignId('shift_group_id')->nullable()->constrained('shift_groups')->nullOnDelete();
            $table->string('day_name'); // السبت، الاحد... او الجمعه 9/4
            $table->date('specific_date')->nullable();
            $table->boolean('is_friday')->default(false);
            $table->integer('col_index')->default(0);
            $table->integer('row_index')->default(0);
            $table->timestamps();

            $table->index(['shift_roster_id', 'employee_name']);
            $table->index(['user_id', 'specific_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_roster_entries');
        Schema::dropIfExists('shift_rosters');
    }
};
