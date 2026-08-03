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
});
