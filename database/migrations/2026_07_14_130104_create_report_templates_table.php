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
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('type'); // Student, Section, etc.
            $table->string('language')->default('en');
            $table->string('font_family')->default('sans-serif');
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_signature')->default(true);
            $table->boolean('show_qr')->default(false);
            $table->integer('margin_top')->default(10);
            $table->integer('margin_bottom')->default(10);
            $table->integer('margin_left')->default(10);
            $table->integer('margin_right')->default(10);
            $table->text('header')->nullable();
            $table->text('footer')->nullable();
            $table->string('watermark')->nullable();
            $table->string('orientation')->default('portrait');
            $table->string('paper_size')->default('a4');
            $table->boolean('is_default')->default(false);
            $table->string('status')->default('active');
            $table->integer('version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};
