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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            
            $table->enum('type', [
                'mcq', 
                'true_false', 
                'short_answer', 
                'essay', 
                'matching', 
                'fill_blank'
            ]);
            
            $table->text('question_text');
            $table->decimal('mark', 5, 2)->default(1.00);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
