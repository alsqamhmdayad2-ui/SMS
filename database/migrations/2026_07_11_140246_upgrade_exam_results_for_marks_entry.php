<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            // Replace boolean flags with a flexible attendance_status string
            $table->string('attendance_status', 20)->default('present')->after('is_excused');
            // Store the calculated percentage physically
            $table->decimal('percentage', 5, 2)->nullable()->after('total_marks');
        });

        // Migrate existing data
        \Illuminate\Support\Facades\DB::statement("
            UPDATE exam_results 
            SET attendance_status = CASE 
                WHEN is_absent = 1 THEN 'absent'
                WHEN is_excused = 1 THEN 'excused'
                ELSE 'present'
            END
        ");

        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropColumn(['is_absent', 'is_excused']);
        });
    }

    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->boolean('is_absent')->default(false)->after('total_marks');
            $table->boolean('is_excused')->default(false)->after('is_absent');
        });

        \Illuminate\Support\Facades\DB::statement("
            UPDATE exam_results 
            SET is_absent = CASE WHEN attendance_status = 'absent' THEN 1 ELSE 0 END,
                is_excused = CASE WHEN attendance_status = 'excused' THEN 1 ELSE 0 END
        ");

        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropColumn(['attendance_status', 'percentage']);
        });
    }
};
