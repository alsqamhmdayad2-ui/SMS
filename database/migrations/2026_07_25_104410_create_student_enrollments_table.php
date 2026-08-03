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
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained('grades')->cascadeOnDelete(); // Stage
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete(); // Grade
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete(); // Section
            
            $table->date('registration_date');
            $table->enum('registration_type', ['New', 'Transferred', 'Re-enrolled'])->default('New');
            $table->string('previous_school')->nullable();
            $table->string('transfer_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
