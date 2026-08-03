<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$teacher = \App\Models\Teacher::find(4);
if ($teacher) {
    echo json_encode($teacher->timetables()->get());
}
