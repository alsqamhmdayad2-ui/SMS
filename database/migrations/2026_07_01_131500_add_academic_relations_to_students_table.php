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
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('grade_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // class_id column already exists, just add the constraint
            $table->foreign('class_id')
                ->references('id')->on('classes')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('section_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('parents')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['grade_id']);
            $table->dropForeign(['class_id']);
            $table->dropForeign(['section_id']);
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['grade_id', 'section_id', 'parent_id']);
        });
    }
};
