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
        Schema::create('attendance_overrides', function (Blueprint $table) {
            $table->id();

            // The record being overridden
            $table->foreignId('attendance_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();

            // Before / After snapshot
            $table->string('old_status');
            $table->string('new_status');

            // Who did it and why
            $table->foreignId('overridden_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('overridden_at');
            $table->string('reason');               // Required — no silent edits

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_overrides');
    }
};
