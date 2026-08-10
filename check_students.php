<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check an existing student that works vs our new students
echo "=== فحص المستخدمين الطلاب ===\n";

$newStudents = App\Models\Student::where('section_id', 1)->get();
foreach ($newStudents as $s) {
    $user = App\Models\User::find($s->user_id);
    $hasRole = $user ? $user->hasRole('student') : false;
    echo "طالب: {$s->first_name} {$s->family_name} | user_id: {$s->user_id} | hasRole('student'): " . ($hasRole ? 'YES' : 'NO') . "\n";
    if ($user) {
        $roles = $user->getRoleNames()->toArray();
        echo "  Roles: " . implode(', ', $roles ?: ['none']) . "\n";
    }
}

// Find a working student from another section
echo "\n=== طالب من الصف الثاني للمقارنة ===\n";
$workingStudent = App\Models\Student::where('section_id', 3)->first();
if ($workingStudent) {
    $user = App\Models\User::find($workingStudent->user_id);
    echo "طالب: {$workingStudent->first_name} | user_id: {$workingStudent->user_id}\n";
    if ($user) {
        $roles = $user->getRoleNames()->toArray();
        echo "Roles: " . implode(', ', $roles ?: ['none']) . "\n";
    }
}

// Check why admin students page might be empty
echo "\n=== فحص صفحة الطلاب الإدارية ===\n";
$total = App\Models\Student::count();
$withUser = App\Models\Student::whereNotNull('user_id')->count();
$withoutUser = App\Models\Student::whereNull('user_id')->count();
echo "إجمالي الطلاب: $total\n";
echo "مع user_id: $withUser\n";
echo "بدون user_id: $withoutUser\n";
