<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/verify/report/{uuid}', [App\Http\Controllers\VerificationController::class, 'verify'])->name('verify.report');


Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user && $user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }
    if ($user && $user->hasRole('teacher')) {
        return redirect()->route('teacher.dashboard');
    }
    if ($user && $user->hasRole('student')) {
        return redirect()->route('student.dashboard');
    }
    if ($user && $user->hasRole('parent')) {
        return redirect()->route('parent.dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Messaging Routes
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Messaging\MessageController::class, 'index'])->name('index');
        Route::get('/sent', [\App\Http\Controllers\Messaging\MessageController::class, 'sent'])->name('sent');
        Route::get('/create', [\App\Http\Controllers\Messaging\MessageController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Messaging\MessageController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Messaging\MessageController::class, 'show'])->name('show');
        Route::post('/{id}/reply', [\App\Http\Controllers\Messaging\MessageController::class, 'reply'])->name('reply');
    });
});

require __DIR__.'/auth.php';

// Panel Routes
require __DIR__.'/admin.php';
require __DIR__.'/teacher.php';
require __DIR__.'/student.php';
require __DIR__.'/parent.php';
