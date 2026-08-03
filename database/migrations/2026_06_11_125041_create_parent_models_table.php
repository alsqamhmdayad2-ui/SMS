<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('guardian_type', ['Father', 'Mother', 'Guardian'])->default('Father');
            $table->string('full_name');
            $table->string('relationship')->nullable();
            $table->string('national_id')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone_1');
            $table->string('phone_2')->nullable();
            $table->string('occupation')->nullable();
            $table->string('workplace')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};
