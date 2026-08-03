<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add academic_year_id column
        Schema::table('subject_section_teacher', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable()->after('id')->constrained('academic_years')->cascadeOnDelete();
        });

        // Set default value for existing rows to the active academic year (or the latest one)
        $activeYear = DB::table('academic_years')->where('status', true)->first() 
                      ?? DB::table('academic_years')->latest()->first();

        if ($activeYear) {
            DB::table('subject_section_teacher')->update(['academic_year_id' => $activeYear->id]);
        }

        // Now that data is populated, make it non-nullable
        Schema::table('subject_section_teacher', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_year_id')->nullable(false)->change();
            
            // Drop foreign key first to avoid 1553 error
            $table->dropForeign(['subject_id']);
            
            // Drop old unique constraint
            $table->dropUnique('subject_section_teacher_subject_id_section_id_unique');
            
            // Add foreign key back
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            
            // Add new unique constraint including academic_year_id
            $table->unique(['academic_year_id', 'subject_id', 'section_id'], 'sst_year_subject_section_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_section_teacher', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropUnique('sst_year_subject_section_unique');
            
            $table->unique(['subject_id', 'section_id'], 'subject_section_teacher_subject_id_section_id_unique');
            
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
    }
};
