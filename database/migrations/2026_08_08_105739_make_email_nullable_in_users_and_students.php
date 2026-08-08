<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make email nullable in users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        // Make email nullable in students table
        Schema::table('students', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
