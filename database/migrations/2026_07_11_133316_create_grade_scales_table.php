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
        Schema::create('grade_scales', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('percentage_from', 5, 2);
            $table->decimal('percentage_to', 5, 2);
            $table->string('letter_grade');
            $table->decimal('gpa_point', 3, 2);
            $table->boolean('is_passing')->default(true);
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_scales');
    }
};
