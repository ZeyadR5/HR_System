<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'profile_picture')) {
                $table->string('profile_picture')->nullable()->after('working_hours');
            }
            if (!Schema::hasColumn('users', 'national_id_card')) {
                $table->string('national_id_card')->nullable()->after('profile_picture');
            }
            if (!Schema::hasColumn('users', 'qualification_certificate')) {
                $table->string('qualification_certificate')->nullable()->after('national_id_card');
            }
            if (!Schema::hasColumn('users', 'other_document')) {
                $table->string('other_document')->nullable()->after('qualification_certificate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = array_filter([
                'profile_picture',
                'national_id_card',
                'qualification_certificate',
                'other_document',
            ], fn($col) => Schema::hasColumn('users', $col));

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
