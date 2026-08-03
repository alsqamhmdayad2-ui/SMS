<?php
$c = file_get_contents('resources/views/panels/admin/students/edit.blade.php');

// Fix nationality
$c = preg_replace('/value="\{\{ old\(\'nationality\'.*?\}\}"/', 'value="{{ old(\'nationality\', $student->nationality ?? \'فلسطيني\') }}"', $c);

// Fix gender selects
$c = str_replace("old('gender') == 'Male'", "old('gender', \$student->gender) == 'Male'", $c);
$c = str_replace("old('gender') == 'Female'", "old('gender', \$student->gender) == 'Female'", $c);

// Fix blood type
$c = str_replace("old('blood_type') == \$bt", "old('blood_type', \$student->blood_type) == \$bt", $c);

// Fix religion
$c = str_replace("old('religion') == 'Muslim'", "old('religion', \$student->religion) == 'Muslim'", $c);
$c = str_replace("old('religion') == 'Christian'", "old('religion', \$student->religion) == 'Christian'", $c);

// Add reqClassId etc to the top if not exist
if (strpos($c, '$reqClassId =') === false) {
    $phpBlock = "
@php
    \$reqStageId = old('stage_id', \$student->grade_id ?? '');
    \$reqClassId = old('grade_id', \$student->class_id ?? '');
    \$reqSectionId = old('section_id', \$student->section_id ?? '');
@endphp
";
    $c = str_replace("@section('content')", "@section('content')\n" . $phpBlock, $c);
}

// Update script req variables
$c = str_replace("const oldClassId = '{{ \$reqClassId ?? '' }}';", "const oldStageId = '{{ \$reqStageId ?? '' }}';\n    const oldClassId = '{{ \$reqClassId ?? '' }}';", $c);

$c = str_replace("const stageId = stageSelect.value;", "const stageId = stageSelect.value || oldStageId;", $c);
$c = preg_replace("/stageSelect.value \= '';/", "stageSelect.value = oldStageId;", $c);

// Set selected on stage
$c = preg_replace('/<option value="\{\{ \$stage->id \}\}" \{\{ old\(\'stage_id\'\) == \$stage->id \? \'selected\' : \'\' \}\}>/', '<option value="{{ $stage->id }}" {{ (old(\'stage_id\', $student->grade_id ?? \'\') == $stage->id) ? \'selected\' : \'\' }}>', $c);


file_put_contents('resources/views/panels/admin/students/edit.blade.php', $c);
echo "Fixed";
