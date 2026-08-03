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
            $table->foreignId('calculated_by')->nullable()->constrained('users')->nullOnDelete()->after('calculated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_subject_grades', function (Blueprint $table) {
            $table->dropForeign(['calculated_by']);
            $table->dropColumn('calculated_by');
        });
    }
};
