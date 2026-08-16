<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * نظام الدرجات الجديد بالمكونات السبعة لكل طالب/مادة/فصل
     * activity + attendance + homework + monthly1 + midterm + monthly2 + final = 100
     */
    public function up(): void
    {
        Schema::create('student_semester_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();

            // المكونات السبعة (مجموعها من 100)
            $table->decimal('activity',    5, 2)->nullable()->comment('نشاط - max 10');
            $table->decimal('attendance',  5, 2)->nullable()->comment('حضور - max 10');
            $table->decimal('homework',    5, 2)->nullable()->comment('واجبات - max 10');
            $table->decimal('monthly1',    5, 2)->nullable()->comment('شهري 1 - max 10');
            $table->decimal('midterm',     5, 2)->nullable()->comment('نصفي - max 20');
            $table->decimal('monthly2',    5, 2)->nullable()->comment('شهري 2 - max 10');
            $table->decimal('final_exam',  5, 2)->nullable()->comment('نهائي - max 30');

            // المجموع المحسوب (من 100)
            $table->decimal('total',       5, 2)->nullable()->comment('المجموع من 100');

            // الاعتماد
            $table->boolean('is_locked')->default(false)->comment('مقفل بعد اعتماد الفصل');

            // التدقيق
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('entered_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'section_id', 'semester_id', 'academic_year_id'], 'unique_student_mark');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_semester_marks');
    }
};
