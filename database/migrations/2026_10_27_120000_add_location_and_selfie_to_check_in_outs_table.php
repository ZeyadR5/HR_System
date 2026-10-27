<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('check_in_outs', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('actioned_by');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->unsignedInteger('distance_m')->nullable()->after('longitude');
            $table->string('selfie_path')->nullable()->after('distance_m');
            $table->string('source', 20)->default('web')->after('selfie_path'); // web | app
        });
    }

    public function down(): void
    {
        Schema::table('check_in_outs', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'distance_m', 'selfie_path', 'source']);
        });
    }
};
