<?php
$user = App\Models\User::where('email', 'admin@school.com')->first();
echo "Admin National ID: " . ($user->national_id ?? 'null') . "\n";
$user = App\Models\User::where('role', 'student')->first();
echo "Student User National ID: " . ($user->national_id ?? 'null') . "\n";
