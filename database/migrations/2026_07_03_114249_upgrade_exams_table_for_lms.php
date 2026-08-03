<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // Change status from boolean to string enum (draft, published, closed, archived)
            $table->string('status_enum')->default('draft')->after('end_time');
            $table->integer('duration_minutes')->nullable()->after('end_time');
            $table->text('instructions')->nullable()->after('status_enum');
        });

        // Migrate old boolean status to new enum
        DB::table('exams')->where('status', true)->update(['status_enum' => 'published']);
        DB::table('exams')->where('status', false)->update(['status_enum' => 'draft']);

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->renameColumn('status_enum', 'status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['duration_minutes', 'instructions']);
        });

        // Revert status back to boolean
        Schema::table('exams', function (Blueprint $table) {
            $table->string('status_bool')->default('1')->after('end_time');
        });

        DB::table('exams')->where('status', 'published')->update(['status_bool' => '1']);
        DB::table('exams')->whereNot('status', 'published')->update(['status_bool' => '0']);

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->renameColumn('status_bool', 'status');
        });
    }
};
