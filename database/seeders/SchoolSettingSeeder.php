<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('school_settings')->insert([
            'school_code' => 'SCH-001',
            'school_name' => 'International Excellence School',
            'school_short_name' => 'IES',
            'school_name_en' => 'International Excellence School',
            'address' => '123 Education Street, Learning City',
            'phone' => '+1234567890',
            'email' => 'info@iesschool.edu',
            'website' => 'www.iesschool.edu',
            'principal_name' => 'Dr. Ahmed Mohammed',
            'report_footer' => 'This is an official document issued by the school administration.',
            'country' => 'Global',
            'city' => 'Learning City',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
