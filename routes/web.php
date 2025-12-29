use App\Http\Controllers\Admin\AdminController;

Route::middleware('admin.basic')->prefix('admin')->group(function () {
    Route::get('/users', [AdminController::class, 'pendingUsers']);
    Route::post('/users/{id}/approve', [AdminController::class, 'approve']);
    Route::post('/users/{id}/reject', [AdminController::class, 'reject']);
});
