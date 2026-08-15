<?php

use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\ExamController;
use App\Http\Controllers\Student\ResultController;
use App\Http\Controllers\Student\ReportController;
use App\Http\Controllers\Student\NotificationController;
use App\Http\Controllers\Student\SettingsController;
use App\Http\Controllers\Student\TimetableController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::get('/exams', [ExamController::class, 'index'])->name('exams');
        Route::get('/exams/{exam}/take', [ExamController::class, 'take'])->name('exams.take');
        Route::post('/exams/{exam}/submit', [ExamController::class, 'submit'])->name('exams.submit');
        Route::post('/exams/{exam}/auto-save', [ExamController::class, 'autoSave'])->name('exams.auto-save');
        Route::get('/exams/{exam}/review',     [ExamController::class, 'review'])->name('exams.review');
        Route::get('/results', [ResultController::class, 'index'])->name('results');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable');
        Route::get('/attendance', [App\Http\Controllers\Student\AttendanceController::class, 'index'])->name('attendance');
        Route::get('/documents', [App\Http\Controllers\Student\DocumentController::class, 'index'])->name('documents.index');
        Route::get('/documents/{document}/download', [App\Http\Controllers\Student\DocumentController::class, 'download'])->name('documents.download');
    });
