<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cleanup: Remove orphaned grade_id and class_id columns from subjects table.
 * These were added in add_grade_and_class_to_subjects_table but are no longer used.
 * Subject-class relationships are managed via class_subject_teacher pivot table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropForeign(['grade_id']);
            $table->dropColumn(['class_id', 'grade_id']);
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('grade_id')->nullable()->after('id')->constrained('grades')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->after('grade_id')->constrained('classes')->nullOnDelete();
        });
    }
};
