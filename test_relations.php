<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Student;
use App\Models\ParentModel;
use App\Models\Teacher;

echo "========================================" . PHP_EOL;
echo "  Phase 7.5.4 - Testing Academic Relations" . PHP_EOL;
echo "========================================" . PHP_EOL . PHP_EOL;

$pass = 0;
$fail = 0;

function test($label, $condition) {
    global $pass, $fail;
    if ($condition) {
        echo "  ✅ PASS: {$label}" . PHP_EOL;
        $pass++;
    } else {
        echo "  ❌ FAIL: {$label}" . PHP_EOL;
        $fail++;
    }
}

// Clean up
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
Grade::withTrashed()->forceDelete();
SchoolClass::withTrashed()->forceDelete();
Section::withTrashed()->forceDelete();
Subject::withTrashed()->forceDelete();
Student::withTrashed()->forceDelete();
ParentModel::withTrashed()->forceDelete();
Teacher::withTrashed()->forceDelete();
DB::table('subject_teacher')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

// Create basic records
$grade = Grade::create(['name' => 'Grade 10', 'description' => 'Tenth Grade', 'status' => 1]);
$classA = SchoolClass::create(['grade_id' => $grade->id, 'name' => 'Class A', 'status' => 1]);
$section1 = Section::create(['class_id' => $classA->id, 'name' => 'Section 1', 'status' => 1]);
$parent = ParentModel::create([
    'father_name' => 'John Doe Sr.',
    'email' => 'john.sr@example.com',
    'phone' => '1234567890'
]);

$subject1 = Subject::create(['name' => 'Math', 'code' => 'MTH101', 'status' => 1]);
$subject2 = Subject::create(['name' => 'Science', 'code' => 'SCI101', 'status' => 1]);

$teacher = Teacher::create([
    'name' => 'Jane Smith',
    'email' => 'jane.smith@example.com',
    'phone' => '0987654321'
]);

// ======= Student Relations =======
echo PHP_EOL . "--- Student Relations ---" . PHP_EOL;
$student = Student::create([
    'name' => 'John Doe Jr.',
    'email' => 'john.jr@example.com',
    'parent_id' => $parent->id,
    'grade_id' => $grade->id,
    'class_id' => $classA->id,
    'section_id' => $section1->id
]);

test('Student created', $student->exists);

// Reload to test relations
$student->load(['parent', 'grade', 'schoolClass', 'section']);

test('Student -> Parent relation', $student->parent->father_name === 'John Doe Sr.');
test('Student -> Grade relation', $student->grade->name === 'Grade 10');
test('Student -> Class relation', $student->schoolClass->name === 'Class A');
test('Student -> Section relation', $student->section->name === 'Section 1');

// Test inverse relation from Parent
$parent->load('students');
test('Parent -> Students relation', $parent->students->count() === 1 && $parent->students->first()->name === 'John Doe Jr.');

// ======= Teacher-Subject Relations =======
echo PHP_EOL . "--- Teacher ↔ Subjects ---" . PHP_EOL;

// Attach subjects
$teacher->subjects()->attach([$subject1->id, $subject2->id]);

// Test Teacher -> Subjects
$teacher->load('subjects');
test('Teacher -> Subjects relation count', $teacher->subjects->count() === 2);
test('Teacher -> Subjects relation data', $teacher->subjects->pluck('name')->contains('Math') && $teacher->subjects->pluck('name')->contains('Science'));

// Test inverse relation from Subject
$subject1->load('teachers');
test('Subject -> Teachers relation', $subject1->teachers->count() === 1 && $subject1->teachers->first()->name === 'Jane Smith');

// ======= SUMMARY =======
echo PHP_EOL . "========================================" . PHP_EOL;
echo "  Results: {$pass} passed, {$fail} failed" . PHP_EOL;
echo "========================================" . PHP_EOL;
