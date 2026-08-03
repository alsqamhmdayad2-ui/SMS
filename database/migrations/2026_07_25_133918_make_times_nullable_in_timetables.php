<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->time('start_time')->nullable()->change();
            $table->time('end_time')->nullable()->change();
            // Teacher is automatically fetched, but what if no teacher is assigned to the section?
            // Allow teacher_id to be nullable so the period can be scheduled even if missing a teacher.
            $table->unsignedBigInteger('teacher_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->time('start_time')->nullable(false)->change();
            $table->time('end_time')->nullable(false)->change();
            $table->unsignedBigInteger('teacher_id')->nullable(false)->change();
        });
    }
};
