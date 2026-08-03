<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add weekly periods count to subjects (based on Palestinian curriculum)
        Schema::table('subjects', function (Blueprint $table) {
            $table->unsignedTinyInteger('weekly_periods')->default(0)->after('code')
                  ->comment('عدد الحصص الأسبوعية حسب المنهاج');
        });

        // Add teacher weekly load (نصاب المعلم)
        Schema::table('teachers', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_weekly_periods')->default(24)->after('salary')
                  ->comment('النصاب الأسبوعي للمعلم (عدد الحصص)');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('weekly_periods');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('max_weekly_periods');
        });
    }
};
