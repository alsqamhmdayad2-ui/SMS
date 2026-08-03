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
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('grade_id')->nullable()->after('id')->constrained('grades')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->after('grade_id')->constrained('classes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropForeign(['grade_id']);
            $table->dropColumn(['class_id', 'grade_id']);
        });
    }
};
