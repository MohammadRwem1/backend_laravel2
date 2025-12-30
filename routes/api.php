<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/apartments/{id}',[ApartmentController::class,'show']);
    Route::get('/apartments',[ApartmentController::class,'index']);
    
    Route::middleware(['checkUser'])->group(function () {
        Route::post('/apartments',[ApartmentController::class,'store']);
        Route::put('/apartments/{apartment}',[ApartmentController::class,'update']);
        Route::delete('/apartments/{apartment}',[ApartmentController::class,'destroy']);
    });    
});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/apartments/{apartment_id}/bookings', [BookingController::class, 'store']);

    Route::get('/my-bookings', [BookingController::class, 'myBookings']);

    Route::post('/bookings/{id}/review', [BookingController::class, 'addReview']);

    Route::get('/owner/bookings/pending', [BookingController::class, 'ownerPending']);

    Route::post('/bookings/{id}/approve', [BookingController::class, 'approve']);

    Route::post('/bookings/{id}/reject', [BookingController::class, 'reject']);
});