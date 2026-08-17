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
        Schema::create('exam_student_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_result_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_option_id')->nullable()->constrained()->nullOnDelete(); // for mcq
            $table->text('text_answer')->nullable(); // for text answers
            $table->decimal('marks_awarded', 5, 2)->default(0);
            $table->boolean('is_correct')->default(false);
            $table->boolean('is_graded')->default(true); // false for essay until teacher grades it
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_student_answers');
    }
};
