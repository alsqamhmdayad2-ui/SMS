<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $classes = SchoolClass::all();

        foreach ($classes as $schoolClass) {
            // Create two sections for each class
            Section::firstOrCreate(
                ['name' => 'الشعبة أ', 'class_id' => $schoolClass->id],
                ['status' => 1]
            );

            Section::firstOrCreate(
                ['name' => 'الشعبة ب', 'class_id' => $schoolClass->id],
                ['status' => 1]
            );
        }
    }
}
