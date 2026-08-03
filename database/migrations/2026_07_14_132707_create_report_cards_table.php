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
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            
            // Context & Period
            $table->string('report_period')->default('semester'); // 'semester' or 'annual'
            
            // Snapshots for Historical Integrity
            $table->string('student_name_snapshot');
            $table->string('section_name_snapshot');
            $table->string('academic_year_name_snapshot');
            
            // Grades & Status
            $table->decimal('gpa', 4, 2)->nullable();
            $table->decimal('total_percentage', 5, 2)->nullable();
            $table->integer('rank_in_section')->nullable();
            $table->string('status')->default('DRAFT'); // DRAFT, GENERATED, PUBLISHED, REVOKED
            $table->string('academic_status')->nullable(); // Pass, Fail, Incomplete
            
            // Locking & Audit
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Publishing & Verification
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('verification_uuid')->nullable()->unique();
            $table->string('verification_hash')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // A student should only have one report card per academic year/semester/period combo
            $table->unique(['student_id', 'academic_year_id', 'semester_id', 'report_period'], 'report_cards_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};
