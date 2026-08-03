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
        Schema::table('questions', function (Blueprint $table) {
            // Make exam_id nullable (bank questions won't have an exam_id)
            $table->foreignId('exam_id')->nullable()->change();

            // Question Bank fields
            $table->foreignId('subject_id')->nullable()->after('exam_id')->constrained()->nullOnDelete();
            $table->string('question_code')->nullable()->unique()->after('id');
            $table->foreignId('created_by')->nullable()->after('difficulty')->constrained('users')->nullOnDelete();
            $table->boolean('is_public')->default(true)->after('created_by');
            $table->string('bloom_level')->nullable()->after('difficulty');
            $table->integer('estimated_time')->nullable()->comment('in seconds')->after('bloom_level');
            $table->integer('display_order')->default(0)->after('estimated_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'subject_id',
                'question_code',
                'created_by',
                'is_public',
                'bloom_level',
                'estimated_time',
                'display_order',
            ]);
        });
    }
};
