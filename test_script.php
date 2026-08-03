<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;

echo "========================================" . PHP_EOL;
echo "  Phase 7.4.1 - Academic Audit" . PHP_EOL;
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
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

// ======= GRADE CRUD =======
echo PHP_EOL . "--- Grade CRUD ---" . PHP_EOL;
$grade = Grade::create(['name' => 'Grade 10', 'description' => 'Tenth Grade', 'status' => 1]);
test('Grade created', $grade->exists);

$grade->update(['name' => 'Grade 10 Updated']);
test('Grade updated', $grade->fresh()->name === 'Grade 10 Updated');
$grade->update(['name' => 'Grade 10']);

$grade->delete();
test('Grade soft deleted', $grade->trashed());

$grade->restore();
test('Grade restored', !$grade->trashed());

// ======= SCHOOL CLASS CRUD =======
echo PHP_EOL . "--- School Class CRUD ---" . PHP_EOL;
$classA = SchoolClass::create(['grade_id' => $grade->id, 'name' => 'Class A', 'status' => 1]);
test('School Class created', $classA->exists);

$classA->update(['name' => 'Class A Updated']);
test('School Class updated', $classA->fresh()->name === 'Class A Updated');
$classA->update(['name' => 'Class A']);

test('Class->Grade relation', $classA->grade->name === 'Grade 10');
test('Grade->Classes relation', $grade->classes->count() === 1);

// ======= SECTION CRUD =======
echo PHP_EOL . "--- Section CRUD ---" . PHP_EOL;
$section1 = Section::create(['class_id' => $classA->id, 'name' => 'Section 1', 'status' => 1]);
test('Section created', $section1->exists);

$section1->update(['name' => 'Section 1 Updated']);
test('Section updated', $section1->fresh()->name === 'Section 1 Updated');
$section1->update(['name' => 'Section 1']);

test('Section->SchoolClass relation', $section1->schoolClass->name === 'Class A');
test('Section->SchoolClass->Grade chain', $section1->schoolClass->grade->name === 'Grade 10');
test('SchoolClass->Sections relation', $classA->fresh()->sections->count() === 1);

$section1->delete();
test('Section soft deleted', $section1->trashed());
$section1->restore();
test('Section restored', !$section1->trashed());

// Duplicate section in same class
$section2 = Section::create(['class_id' => $classA->id, 'name' => 'Section 1', 'status' => 1]);
test('Duplicate section name in same class (DB allows - Validation prevents)', $section2->exists);
$section2->forceDelete();

// Section in different class
$classB = SchoolClass::create(['grade_id' => $grade->id, 'name' => 'Class B', 'status' => 1]);
$sectionInClassB = Section::create(['class_id' => $classB->id, 'name' => 'Section 1', 'status' => 1]);
test('Same section name in different class', $sectionInClassB->exists);

// ======= SUBJECT CRUD =======
echo PHP_EOL . "--- Subject CRUD ---" . PHP_EOL;
$subject1 = Subject::create([
    'name' => 'Mathematics',
    'code' => 'MATH101',
    'description' => 'Basic Mathematics',
    'status' => 1
]);
test('Subject created', $subject1->exists);

$subject1->update(['name' => 'Advanced Mathematics']);
test('Subject updated', $subject1->fresh()->name === 'Advanced Mathematics');
$subject1->update(['name' => 'Mathematics']);

// Test unique code constraint
try {
    Subject::create([
        'name' => 'Duplicate Math',
        'code' => 'MATH101',
        'description' => 'Duplicate',
        'status' => 1
    ]);
    test('Unique code constraint', false);
} catch (\Exception $e) {
    test('Unique code constraint prevents duplicate code', true);
}

$subject1->delete();
test('Subject soft deleted', $subject1->trashed());
$subject1->restore();
test('Subject restored', !$subject1->trashed());

// More subjects
$subject2 = Subject::create(['name' => 'English', 'code' => 'ENG201', 'status' => 1]);
$subject3 = Subject::create(['name' => 'Science', 'code' => 'SCI301', 'description' => 'General Science', 'status' => 1]);
test('Multiple subjects created', Subject::count() === 3);

// ======= DELETE RULES =======
echo PHP_EOL . "--- Delete Rules ---" . PHP_EOL;

// Try deleting School Class that has sections (cascadeOnDelete)
$sectionCount = Section::where('class_id', $classB->id)->count();
$classB->forceDelete();
$remainingSections = Section::where('class_id', $classB->id)->count();
test('CascadeOnDelete removes sections when class deleted', $remainingSections === 0);

// ======= SEARCH =======
echo PHP_EOL . "--- Subject Search ---" . PHP_EOL;
$service = new \App\Services\SubjectService();
$searchByName = $service->getAllSubjects('Math');
test('Search by name', $searchByName->count() >= 1);

$searchByCode = $service->getAllSubjects('ENG');
test('Search by code', $searchByCode->count() >= 1);

$searchNoResult = $service->getAllSubjects('NONEXISTENT999');
test('Search no result', $searchNoResult->count() === 0);

// ======= SUMMARY =======
echo PHP_EOL . "========================================" . PHP_EOL;
echo "  Results: {$pass} passed, {$fail} failed" . PHP_EOL;
echo "========================================" . PHP_EOL;
