<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shift_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('from_time');
            $table->time('to_time');
            $table->string('color', 20)->default('#2563eb');
            $table->string('icon', 20)->default('⏰');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('shift_group_id')->nullable()->after('role')->constrained('shift_groups')->nullOnDelete();
        });

        // Seed the 4 requested shift groups (NO users attached)
        DB::table('shift_groups')->insert([
            [
                'name'        => 'جروب الصباحي',
                'from_time'   => '09:00:00',
                'to_time'     => '16:00:00',
                'color'       => '#2563eb',
                'icon'        => '☀️',
                'description' => 'من 9:00 ص إلى 4:00 م',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'جروب الوسط',
                'from_time'   => '12:00:00',
                'to_time'     => '19:00:00',
                'color'       => '#0d9488',
                'icon'        => '🌤️',
                'description' => 'من 12:00 م إلى 7:00 م',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'جروب المسائي',
                'from_time'   => '16:00:00',
                'to_time'     => '23:00:00',
                'color'       => '#ea580c',
                'icon'        => '🌆',
                'description' => 'من 4:00 م إلى 11:00 م',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'جروب السهر',
                'from_time'   => '23:00:00',
                'to_time'     => '08:00:00',
                'color'       => '#7c3aed',
                'icon'        => '🌙',
                'description' => 'من 11:00 م إلى 8:00 ص (سهر)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['shift_group_id']);
            $table->dropColumn('shift_group_id');
        });

        Schema::dropIfExists('shift_groups');
    }
};
