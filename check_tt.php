<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$parent = App\Models\ParentModel::first();
echo "=== ParentModel fields ===\n";
echo "first_name: "      . $parent->first_name      . "\n";
echo "father_name: "     . $parent->father_name     . "\n";
echo "grandfather_name: ". $parent->grandfather_name. "\n";
echo "family_name: "     . $parent->family_name     . "\n";
echo "full_name: "       . $parent->full_name        . "\n";
echo "guardian_type: "   . $parent->guardian_type    . "\n";
echo "email: "           . $parent->email            . "\n";
echo "phone: "           . $parent->phone            . "\n";
echo "user->name: "      . ($parent->user->name ?? 'NULL') . "\n";
echo "\n=== DB Columns ===\n";
$cols = Schema::getColumnListing('parents');
echo implode(', ', $cols) . "\n";
