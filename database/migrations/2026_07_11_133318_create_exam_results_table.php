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
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('marks_obtained', 8, 2)->nullable();
            $table->decimal('total_marks', 8, 2); // Snapshot of exam's total_marks at the time
            $table->boolean('is_absent')->default(false);
            $table->boolean('is_excused')->default(false); // e.g., medical leave
            $table->text('remarks')->nullable();
            $table->timestamp('submitted_at')->nullable(); // When student submitted
            $table->timestamp('graded_at')->nullable();    // When teacher finished grading
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->unique(['exam_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
