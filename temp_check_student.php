<?php
$student = App\Models\Student::first();
echo "Student ID: {$student->id}\n";
echo "National ID: {$student->national_id}\n";
echo "User ID: {$student->user_id}\n";
if ($student->user_id) {
    $user = App\Models\User::find($student->user_id);
    echo "User Email: " . ($user ? $user->email : 'null') . "\n";
    echo "User Password Hash: " . ($user ? $user->password : 'null') . "\n";
} else {
    echo "NO USER ID\n";
}
