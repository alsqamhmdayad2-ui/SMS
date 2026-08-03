<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('report_templates')->insert([
            'slug' => 'default-student-report',
            'name' => 'Default Student Report',
            'type' => 'student',
            'language' => 'en',
            'font_family' => 'sans-serif',
            'show_logo' => true,
            'show_signature' => true,
            'show_qr' => true,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_left' => 15,
            'margin_right' => 15,
            'is_default' => true,
            'status' => 'active',
            'version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        DB::table('report_templates')->insert([
            'slug' => 'default-section-report',
            'name' => 'Default Section Report',
            'type' => 'section',
            'language' => 'en',
            'font_family' => 'sans-serif',
            'show_logo' => true,
            'show_signature' => true,
            'show_qr' => false,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
            'is_default' => true,
            'status' => 'active',
            'version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
