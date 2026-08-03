use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Section;

try {
    // 1. Create Grade
    $grade = Grade::create(['name' => 'Grade 10', 'description' => 'Tenth Grade']);
    echo 'Grade created: ' . $grade->name . PHP_EOL;

    // 2. Create School Class
    $classA = SchoolClass::create(['grade_id' => $grade->id, 'name' => 'Class A']);
    echo 'Class created: ' . $classA->name . PHP_EOL;

    // 3. Create Section
    $section = Section::create(['class_id' => $classA->id, 'name' => 'Section 1']);
    echo 'Section created: ' . $section->name . PHP_EOL;

    // 4. Test Validation (Duplicate Section in same Class)
    try {
        Section::create(['class_id' => $classA->id, 'name' => 'Section 1']);
        echo 'Failed: Duplicate section allowed (validation is usually in FormRequest, but DB should ideally prevent or we just test eloquent). Wait, DB has no unique constraint, so this succeeds in tinker.' . PHP_EOL;
    } catch (\Exception $e) {
        echo 'Passed: Duplicate prevented by DB.' . PHP_EOL;
    }

    // 5. Test Relation
    $t1 = Section::with('schoolClass.grade')->find($section->id);
    echo 'Relation Section->Class->Grade: ' . $t1->schoolClass->grade->name . PHP_EOL;

    $t2 = SchoolClass::with('sections')->find($classA->id);
    echo 'Relation Class->Sections count: ' . $t2->sections->count() . PHP_EOL;

    // 6. Edit Section
    $section->update(['name' => 'Section 1 Updated']);
    echo 'Section updated: ' . $section->name . PHP_EOL;

    // 7. Soft Delete Section
    $section->delete();
    echo 'Section deleted. Trashed: ' . $section->trashed() . PHP_EOL;

    // 8. Restore Section
    $section->restore();
    echo 'Section restored. Trashed: ' . $section->trashed() . PHP_EOL;

    // 9. Delete Rules: Try deleting School Class with restrictOnDelete
    try {
        $classA->delete();
        echo 'School Class deleted.' . PHP_EOL;
    } catch (\Exception $e) {
        echo 'Restrict on Delete caught: ' . $e->getMessage() . PHP_EOL;
    }
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
