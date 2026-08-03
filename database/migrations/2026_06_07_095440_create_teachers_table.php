<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name');
            $table->string('father_name');
            $table->string('grandfather_name')->nullable();
            $table->string('family_name');
            $table->string('national_id')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('specialization')->nullable();
            $table->decimal('salary', 8, 2)->nullable();
            $table->text('address')->nullable();
            $table->string('avatar')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
