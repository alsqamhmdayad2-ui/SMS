<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = array_map(fn($t) => array_values((array)$t)[0], DB::select('SHOW TABLES'));

$filtered = collect($tables)
    ->filter(fn ($table) => str_contains($table, 'enroll')
        || str_contains($table, 'academic')
        || str_contains($table, 'semester')
        || str_contains($table, 'exam')
        || str_contains($table, 'event')
        || str_contains($table, 'holiday')
        || str_contains($table, 'calendar'))
    ->values()
    ->toArray();

echo "Filtered Tables:\n";
print_r($filtered);
