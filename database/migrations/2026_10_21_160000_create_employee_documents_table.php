<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $types = ['national_id_card', 'qualification_certificate', 'other_document'];

    public function up(): void
    {
        if (!Schema::hasTable('employee_documents')) {
            Schema::create('employee_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('type', 50)->index();
                $table->string('path');
                $table->string('original_name')->nullable();
                $table->timestamps();
            });
        }

        // نقل الملفات القديمة (ملف واحد لكل نوع) إلى الجدول الجديد
        foreach ($this->types as $type) {
            if (!Schema::hasColumn('users', $type)) {
                continue;
            }
            $rows = DB::table('users')->whereNotNull($type)->where($type, '!=', '')->get(['id', $type]);
            foreach ($rows as $row) {
                DB::table('employee_documents')->insert([
                    'user_id'       => $row->id,
                    'type'          => $type,
                    'path'          => $row->{$type},
                    'original_name' => basename($row->{$type}),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
            DB::table('users')->whereNotNull($type)->update([$type => null]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
