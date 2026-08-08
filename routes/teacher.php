<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/teacher', '/teacher/dashboard')->name('teacher.root');

Route::prefix('teacher')->name('teacher.')->middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('dashboard', [App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');

    // My Students
    Route::get('students', [App\Http\Controllers\Teacher\TeacherController::class, 'students'])->name('students');

    // Schedule
    Route::get('schedule', [App\Http\Controllers\Teacher\TeacherController::class, 'schedule'])->name('schedule');

    // Grades
    Route::get('grades', [App\Http\Controllers\Teacher\TeacherController::class, 'grades'])->name('grades');

    // Profile
    Route::get('profile', [App\Http\Controllers\Teacher\TeacherController::class, 'profile'])->name('profile');
    Route::post('profile', [App\Http\Controllers\Teacher\TeacherController::class, 'updateProfile'])->name('profile.update');

    // Attendance Portal
    Route::get('attendance/today', [App\Http\Controllers\Teacher\AttendanceController::class, 'today'])->name('attendance.today');
    Route::get('attendance/take/{timetable}', [App\Http\Controllers\Teacher\AttendanceController::class, 'take'])->name('attendance.take');
    Route::post('attendance/{session}/save', [App\Http\Controllers\Teacher\AttendanceController::class, 'save'])->name('attendance.save');
    Route::post('attendance/{session}/lock', [App\Http\Controllers\Teacher\AttendanceController::class, 'lock'])->name('attendance.lock');

    // Exams & Marks Entry
    Route::prefix('exams')->name('exams.')->group(function () {
        Route::get('/',                [App\Http\Controllers\Teacher\ExamController::class, 'index'])   ->name('index');
        Route::get('/create',          [App\Http\Controllers\Teacher\ExamController::class, 'create'])  ->name('create');
        Route::post('/',               [App\Http\Controllers\Teacher\ExamController::class, 'store'])   ->name('store');
        Route::get('/{exam}',          [App\Http\Controllers\Teacher\ExamController::class, 'show'])    ->name('show');
        Route::get('/{exam}/edit',     [App\Http\Controllers\Teacher\ExamController::class, 'edit'])    ->name('edit');
        Route::put('/{exam}',          [App\Http\Controllers\Teacher\ExamController::class, 'update'])  ->name('update');
        Route::delete('/{exam}',       [App\Http\Controllers\Teacher\ExamController::class, 'destroy']) ->name('destroy');
        Route::post('/marks/save',     [App\Http\Controllers\Teacher\ExamController::class, 'saveMark'])->name('marks.save');
        Route::post('/marks/save-all', [App\Http\Controllers\Teacher\ExamController::class, 'saveAll']) ->name('marks.save-all');
    });
});
