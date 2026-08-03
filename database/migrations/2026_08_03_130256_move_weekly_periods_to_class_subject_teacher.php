<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add weekly_periods to class_subject_teacher
        Schema::table('class_subject_teacher', function (Blueprint $table) {
            $table->unsignedTinyInteger('weekly_periods')->default(0)->after('teacher_id')
                  ->comment('عدد الحصص الأسبوعية لهذا الصف والمادة');
        });

        // Drop weekly_periods from subjects
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('weekly_periods');
        });
    }

    public function down(): void
    {
        Schema::table('class_subject_teacher', function (Blueprint $table) {
            $table->dropColumn('weekly_periods');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->unsignedTinyInteger('weekly_periods')->default(0)->after('code');
        });
    }
};
