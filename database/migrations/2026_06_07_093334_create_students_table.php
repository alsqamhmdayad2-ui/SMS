<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('national_id')->nullable();
            $table->string('student_number')->unique();
            $table->string('first_name');
            $table->string('father_name');
            $table->string('grandfather_name');
            $table->string('family_name');
            $table->string('english_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('nationality')->default('Palestinian');
            $table->string('religion')->default('Muslim');
            $table->string('blood_type')->nullable();
            $table->string('health_status')->nullable();
            $table->string('avatar')->nullable();
            $table->enum('status', ['active', 'transferred', 'graduated', 'withdrawn', 'dismissed', 'postponed'])->default('active');
            
            // Address details
            $table->string('governorate')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('street')->nullable();
            $table->string('nearest_landmark')->nullable();
            
            $table->foreignId('class_id')->nullable(); // Original relationship
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
