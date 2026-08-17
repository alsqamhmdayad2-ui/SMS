<?php

use App\Http\Controllers\ParentPanel\DashboardController;
use App\Http\Controllers\ParentPanel\ChildrenController;
use App\Http\Controllers\ParentPanel\AttendanceController;
use App\Http\Controllers\ParentPanel\ResultController;
use App\Http\Controllers\ParentPanel\ProfileController;
use App\Http\Controllers\ParentPanel\TimetableController;
use App\Http\Controllers\ParentPanel\DocumentController;
use App\Http\Controllers\ParentPanel\AcademicHistoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:parent'])
    ->prefix('parent')
    ->name('parent.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/children', [ChildrenController::class, 'index'])->name('children');
        Route::get('/children/{student}', [ChildrenController::class, 'show'])->name('child.profile');
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
        Route::get('/results', [ResultController::class, 'index'])->name('results');
        Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable');
        Route::get('/documents', [DocumentController::class, 'index'])->name('documents');
        Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::get('/academic-history', [AcademicHistoryController::class, 'index'])->name('academic-history');
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    });
