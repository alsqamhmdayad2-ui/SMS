<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        // Step 1: Clear old per-class duplicated subjects and reset
        DB::table('class_subject_teacher')->delete();
        Subject::withTrashed()->forceDelete();

        // Step 2: Define UNIQUE subjects (no duplication)
        $uniqueSubjects = [
            ['name' => 'اللغة العربية',              'code' => 'ARB'],
            ['name' => 'الرياضيات',                   'code' => 'MATH'],
            ['name' => 'اللغة الإنجليزية',            'code' => 'ENG'],
            ['name' => 'التربية الإسلامية',           'code' => 'ISL'],
            ['name' => 'العلوم والحياة',              'code' => 'SCI'],
            ['name' => 'العلوم العامة',               'code' => 'GSCI'],
            ['name' => 'الدراسات الاجتماعية',        'code' => 'SOC'],
            ['name' => 'التربية الوطنية والحياتية',  'code' => 'NAT'],
            ['name' => 'التكنولوجيا',                 'code' => 'TECH'],
            ['name' => 'التربية الفنية',              'code' => 'ART'],
            ['name' => 'التربية الرياضية',            'code' => 'PE'],
        ];

        foreach ($uniqueSubjects as $sub) {
            Subject::create(['name' => $sub['name'], 'code' => $sub['code'], 'status' => 1]);
        }

        // Step 3: Define which subjects belong to each class
        $curriculum = [
            'الصف الأول'   => ['ARB', 'MATH', 'ISL', 'NAT', 'ENG', 'ART', 'PE'],
            'الصف الثاني'  => ['ARB', 'MATH', 'ENG', 'ISL', 'NAT', 'ART', 'PE'],
            'الصف الثالث'  => ['ARB', 'MATH', 'ENG', 'ISL', 'SCI', 'NAT', 'ART', 'PE'],
            'الصف الرابع'  => ['ARB', 'MATH', 'ENG', 'SCI', 'ISL', 'SOC', 'NAT', 'ART', 'PE'],
            'الصف الخامس'  => ['ARB', 'MATH', 'ENG', 'SCI', 'ISL', 'SOC', 'TECH', 'ART', 'PE'],
            'الصف السادس'  => ['ARB', 'MATH', 'ENG', 'SCI', 'ISL', 'SOC', 'TECH', 'ART', 'PE'],
            'الصف السابع'  => ['ARB', 'ENG', 'MATH', 'GSCI', 'ISL', 'SOC', 'TECH', 'PE', 'ART'],
            'الصف الثامن'  => ['ARB', 'ENG', 'MATH', 'GSCI', 'ISL', 'SOC', 'TECH', 'PE', 'ART'],
            'الصف التاسع'  => ['ARB', 'ENG', 'MATH', 'GSCI', 'ISL', 'SOC', 'TECH', 'PE', 'ART'],
        ];

        // Cache subjects by code
        $subjectsByCode = Subject::all()->keyBy('code');

        // Step 4: Link subjects to classes AND register sections
        $classes = SchoolClass::with('sections')->get();
        foreach ($classes as $class) {
            if (isset($curriculum[$class->name])) {
                foreach ($curriculum[$class->name] as $code) {
                    if (isset($subjectsByCode[$code])) {
                        $subjectId = $subjectsByCode[$code]->id;

                        // Link subject to class
                        DB::table('class_subject_teacher')->insertOrIgnore([
                            'class_id'   => $class->id,
                            'subject_id' => $subjectId,
                            'teacher_id' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Register all sections of this class for section-level teacher assignment
                        foreach ($class->sections as $section) {
                            DB::table('subject_section_teacher')->insertOrIgnore([
                                'subject_id' => $subjectId,
                                'section_id' => $section->id,
                                'teacher_id' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }
        }
    }
}
