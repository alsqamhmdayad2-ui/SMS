<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_publications', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            
            // subject_id is nullable (null means entire section/semester is published)
            $table->foreignId('subject_id')->nullable()->constrained()->cascadeOnDelete();
            
            // Type of publication: 'subject', 'section', 'semester'
            $table->string('published_type', 20)->default('subject');
            
            // Statuses: draft, submitted, approved, published, archived
            $table->string('status', 20)->default('draft');
            
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Unique constraint to prevent duplicate publications
            $table->unique([
                'academic_year_id',
                'semester_id',
                'grade_id',
                'section_id',
                'subject_id'
            ], 'unique_publication_combo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_publications');
    }
};
