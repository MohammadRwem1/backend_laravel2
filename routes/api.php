<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\API\OTPController;

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

    Route::put('/bookings/{id}', [BookingController::class, 'updateBooking']);

    Route::patch('/bookings/{id}/cancel', [BookingController::class, 'cancel']);

    Route::get('/my-bookings', [BookingController::class, 'myBookings']);

    Route::post('/bookings/{id}/review', [ReviewController::class, 'addReview']);

    Route::get('/owner/bookings/pending', [BookingController::class, 'ownerPending']);

    Route::post('/bookings/{id}/approve', [BookingController::class, 'approve']);

    Route::post('/bookings/{id}/reject', [BookingController::class, 'reject']);

    Route::get('/notifications', [NotificationController::class, 'index']);
});

Route::middleware('auth:sanctum')->post('/fcm-token', function (Request $request) {
    $request->validate([
        'fcm_token' => 'required|string'
    ]);

    $request->user()->update([
        'fcm_token' => $request->fcm_token
    ]);

    return response()->json(['message' => 'Token saved']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/conversations/{apartment_id}', [ConversationController::class, 'store']);
    Route::get('/conversations', [ConversationController::class, 'index']);

    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'index']);
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store']);
});

Route::post('/send-otp', [OTPController::class, 'sendOtp']);
Route::post('/verify-otp', [OTPController::class, 'verifyOtp']);



Route::middleware('auth:sanctum')->put( '/bookings/{id}/status',[BookingController::class, 'updateeBooking']
);


Route::middleware('auth:sanctum')->post('/save-fcm-token',[BookingController::class, 'saveUserToken']
);


