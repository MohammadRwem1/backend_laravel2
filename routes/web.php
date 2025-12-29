<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;

Route::middleware('admin.basic')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/users/pending', [AdminController::class, 'pendingUsers'])->name('users.pending');
        Route::post('/users/{id}/approve', [AdminController::class, 'approve'])->name('users.approve');
        Route::post('/users/{id}/reject', [AdminController::class, 'reject'])->name('users.reject');

    });
