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
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
        });

        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->unsignedBigInteger('section_id')->nullable()->change();
            $table->foreign('section_id')->references('id')->on('sections')->nullOnDelete();
            
            if (!Schema::hasColumn('student_enrollments', 'status')) {
                $table->string('status')->default('active')->after('section_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropForeign(['section_id']);
        });

        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->unsignedBigInteger('section_id')->nullable(false)->change();
            $table->foreign('section_id')->references('id')->on('sections')->cascadeOnDelete();
        });
    }
};
