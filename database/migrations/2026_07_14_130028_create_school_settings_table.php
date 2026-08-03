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
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_code')->nullable();
            $table->string('school_name');
            $table->string('school_short_name')->nullable();
            $table->string('school_name_en')->nullable();
            $table->string('logo')->nullable();
            $table->string('academic_logo')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('principal_name')->nullable();
            $table->string('principal_signature')->nullable();
            $table->text('report_footer')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('currency')->default('USD');
            $table->string('academic_system')->nullable();
            $table->string('grading_system')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
