<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('user_id');
            $table->string('grandfather_name')->nullable()->after('father_name');
            $table->string('family_name')->nullable()->after('grandfather_name');
        });
    }

    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'grandfather_name',
                'family_name',
            ]);
        });
    }
};
