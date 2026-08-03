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
        Schema::table('student_subject_grades', function (Blueprint $table) {
            $table->dropColumn('letter_grade');
            $table->foreignId('grade_scale_id')->nullable()->after('total_percentage')->constrained('grade_scales')->nullOnDelete();
            $table->string('grade_scale_name_snapshot')->nullable()->after('grade_scale_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_subject_grades', function (Blueprint $table) {
            $table->string('letter_grade')->nullable()->after('total_percentage');
            $table->dropForeign(['grade_scale_id']);
            $table->dropColumn('grade_scale_id');
            $table->dropColumn('grade_scale_name_snapshot');
        });
    }
};
