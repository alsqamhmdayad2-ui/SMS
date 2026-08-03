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
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();

            // Academic context
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            
            // These are now optional, representing the context of who took the daily attendance
            $table->integer('period_number')->nullable();
            $table->foreignId('subject_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('timetable_id')->nullable()->constrained()->nullOnDelete();

            $table->string('status')->default('open'); // open, locked

            // Audit
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Enforce ONE attendance session per section per day
            $table->unique(
                ['academic_year_id', 'semester_id', 'section_id', 'date'],
                'att_sessions_daily_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
