<?php
$student = App\Models\Student::first();
if($student) {
    $user = App\Models\User::find($student->user_id);
    auth()->login($user);
    $controller = new App\Http\Controllers\Student\AcademicHistoryController();
    echo json_encode($controller->index(), JSON_PRETTY_PRINT);
} else {
    echo 'No student found';
}
