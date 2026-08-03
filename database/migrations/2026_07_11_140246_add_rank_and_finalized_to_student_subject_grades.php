<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_subject_grades', function (Blueprint $table) {
            $table->unsignedInteger('rank_in_section')->nullable()->after('is_passing');
            $table->boolean('is_finalized')->default(false)->after('rank_in_section');
        });
    }

    public function down(): void
    {
        Schema::table('student_subject_grades', function (Blueprint $table) {
            $table->dropColumn(['rank_in_section', 'is_finalized']);
        });
    }
};
