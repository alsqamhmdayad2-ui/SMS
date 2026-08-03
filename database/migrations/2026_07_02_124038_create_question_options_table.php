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
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            
            // For MCQ and True/False
            $table->text('option_text')->nullable();
            $table->boolean('is_correct')->default(false);
            
            // For Matching (e.g. Left: Apple, Right: Fruit)
            $table->text('left_item')->nullable();
            $table->text('right_item')->nullable();
            
            // Optional ordering
            $table->integer('order')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
