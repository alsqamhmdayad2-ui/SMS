<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\AttendanceSession;
use App\Services\AttendanceService;
use App\Enums\AttendanceSessionStatus;

Schema::dropIfExists('attendance_records');
Schema::dropIfExists('attendance_sessions');
Schema::dropIfExists('students');

Schema::create('students', function (Blueprint $table) {
    $table->id();
    $table->integer('section_id');
    $table->string('name');
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('attendance_sessions', function (Blueprint $table) {
    $table->id();
    $table->unsignedInteger('academic_year_id');
    $table->unsignedInteger('semester_id');
    $table->unsignedInteger('section_id');
    $table->date('date');
    $table->integer('period_number');
    $table->unsignedInteger('subject_id');
    $table->unsignedInteger('teacher_id');
    $table->unsignedInteger('timetable_id')->nullable();
    $table->string('status')->default('open');
    $table->unsignedInteger('created_by');
    $table->timestamp('locked_at')->nullable();
    $table->unsignedInteger('locked_by')->nullable();
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('attendance_records', function (Blueprint $table) {
    $table->id();
    $table->unsignedInteger('attendance_session_id');
    $table->unsignedInteger('student_id');
    $table->string('status')->default('present');
    $table->text('remarks')->nullable();
    $table->unsignedInteger('marked_by')->nullable();
    $table->timestamp('marked_at')->nullable();
    $table->unsignedInteger('updated_by')->nullable();
    $table->timestamps();
});

$existing = AttendanceSession::create([
    'academic_year_id' => 1,
    'semester_id' => 1,
    'section_id' => 10,
    'date' => '2026-07-28',
    'period_number' => 1,
    'subject_id' => 1,
    'teacher_id' => 1,
    'timetable_id' => 100,
    'status' => AttendanceSessionStatus::Open->value,
    'created_by' => 1,
]);

echo 'created=' . $existing->id . PHP_EOL;
$all = AttendanceSession::all()->toArray();
var_export($all);
echo PHP_EOL;

$query = AttendanceSession::where('academic_year_id', 1)
    ->where('semester_id', 1)
    ->where('section_id', 10)
    ->where('date', '2026-07-28')
    ->orderBy('created_at', 'asc')
    ->first();

echo 'query=' . ($query?->id ?? 'null') . PHP_EOL;

$service = new AttendanceService();
$res = $service->firstOrCreateSession([
    'academic_year_id' => 1,
    'semester_id' => 1,
    'section_id' => 10,
    'date' => '2026-07-28',
    'period_number' => 2,
    'subject_id' => 2,
    'teacher_id' => 2,
    'timetable_id' => 101,
], 2);

echo 'service=' . $res->id . PHP_EOL;
