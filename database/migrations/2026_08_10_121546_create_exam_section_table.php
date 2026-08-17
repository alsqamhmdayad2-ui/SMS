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
        Schema::create('exam_section', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Migrate old data
        \Illuminate\Support\Facades\DB::statement('
            INSERT INTO exam_section (exam_id, section_id, created_at, updated_at)
            SELECT id, section_id, created_at, updated_at FROM exams WHERE section_id IS NOT NULL
        ');

        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
        });

        // Revert data
        \Illuminate\Support\Facades\DB::statement('
            UPDATE exams 
            JOIN exam_section ON exams.id = exam_section.exam_id 
            SET exams.section_id = exam_section.section_id
        ');

        Schema::dropIfExists('exam_section');
    }
};
