<?php
$c = file_get_contents('resources/views/panels/admin/students/edit.blade.php');
$c = str_replace('إضافة طالب جديد', 'تعديل بيانات الطالب', $c);
$c = str_replace('تسجيل طالب جديد', 'تعديل بيانات الطالب', $c);
$c = str_replace('حفظ بيانات الطالب', 'حفظ التعديلات', $c);
$c = str_replace('route(\'admin.students.store\')', 'route(\'admin.students.update\', $student->id)', $c);
$c = str_replace('method="POST"', "method=\"POST\"\n            @method('PUT')", $c);
$c = preg_replace('/value="\{\{ old\(\'(.*?)\'\) \}\}"/', 'value="{{ old(\'$1\', $student->$1) }}"', $c);
$c = preg_replace('/value="\{\{ old\(\'(.*?)\'\) \}\}"/', 'value="{{ old(\'$1\', $student->$1) }}"', $c);

// specific fixes for parents data that might be mapped differently or nested
$c = preg_replace('/\{\{ old\(\'parent_full_name\'\) \}\}/', '{{ old(\'parent_full_name\', $student->parent?->full_name) }}', $c);
$c = preg_replace('/\{\{ old\(\'parent_national_id\'\) \}\}/', '{{ old(\'parent_national_id\', $student->parent?->national_id) }}', $c);
$c = preg_replace('/\{\{ old\(\'parent_phone_1\'\) \}\}/', '{{ old(\'parent_phone_1\', $student->parent?->phone_1) }}', $c);
$c = preg_replace('/\{\{ old\(\'parent_phone_2\'\) \}\}/', '{{ old(\'parent_phone_2\', $student->parent?->phone_2) }}', $c);
$c = preg_replace('/\{\{ old\(\'parent_occupation\'\) \}\}/', '{{ old(\'parent_occupation\', $student->parent?->occupation) }}', $c);
$c = preg_replace('/\{\{ old\(\'parent_workplace\'\) \}\}/', '{{ old(\'parent_workplace\', $student->parent?->workplace) }}', $c);

// Select values
$c = str_replace("old('gender') == 'male'", "old('gender', \$student->gender) == 'male'", $c);
$c = str_replace("old('gender') == 'female'", "old('gender', \$student->gender) == 'female'", $c);
$c = str_replace("old('religion') == 'مسلم'", "old('religion', \$student->religion) == 'مسلم'", $c);
$c = str_replace("old('religion') == 'مسيحي'", "old('religion', \$student->religion) == 'مسيحي'", $c);
$c = str_replace("old('guardian_type') == 'Father'", "old('guardian_type', \$student->parent?->guardian_type) == 'Father'", $c);
$c = str_replace("old('guardian_type') == 'Mother'", "old('guardian_type', \$student->parent?->guardian_type) == 'Mother'", $c);

file_put_contents('resources/views/panels/admin/students/edit.blade.php', $c);
echo "Done\n";
