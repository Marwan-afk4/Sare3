<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Driver\AuthController as DriverAuthController;
use App\Http\Controllers\Api\Driver\DriverActivtyController;
use App\Http\Controllers\Api\Driver\PointController as DriverPointController;
use App\Http\Controllers\Api\User\LoggedUserController;
use App\Http\Controllers\Api\User\PointController;
use App\Http\Controllers\Api\User\RideEstimateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//======= USER AUTH ========
//phone otp
// Route::post('/send-otp', [AuthController::class, 'postOtp']);
// Route::post('/check-otp', [AuthController::class, 'checkOtp']);
Route::post('/phone-otp', [AuthController::class, 'phoneVerified']);

//email otp
Route::post('/send-email-otp', [AuthController::class, 'sendEmailVerificationCode']);
Route::post('/verify-email-otp', [AuthController::class, 'verifyEmailCode']);

//post name
Route::post('/post-name', [AuthController::class, 'postName']);

//email verfication fisrt
Route::post('/email-verfication', [AuthController::class, 'emailVerficationFirst']);
Route::post('/verify-email', [AuthController::class, 'verifyEmailFirst']);

//google auth
Route::post('/google-auth', [AuthController::class, 'googleAuth']);

//login
Route::post('/login', [AuthController::class, 'login']);



//======= DRIVER AUTH ========
//phone otp
// Route::post('/driver/send-otp', [DriverAuthController::class, 'postOtp']);
// Route::post('/driver/check-otp', [DriverAuthController::class, 'CheckOtp']);
Route::post('/driver/phone-otp', [AuthController::class,'phoneVerified']);

//email otp
Route::post('/driver/send-email-otp', [DriverAuthController::class, 'sendEmailVerificationCode']);
Route::post('/driver/verify-email-otp', [DriverAuthController::class, 'verifyEmailCode']);

//email verfication fisrt
Route::post('/driver/email-verfication', [DriverAuthController::class, 'emailVerficationFirst']);
Route::post('/driver/verify-email', [DriverAuthController::class, 'verifyEmailFirst']);

//post name
Route::post('/driver/post-name', [DriverAuthController::class, 'postName']);

//google auth
Route::post('/driver/google-auth', [DriverAuthController::class, 'googleAuth']);

//required docs
Route::get('/driver/required-docs', [DriverAuthController::class, 'requiredDocs']);
Route::post('/driver/store-docs', [DriverAuthController::class, 'storeDriverDocs']);

//login
Route::post('/driver/login', [DriverAuthController::class, 'login']);


//======= DRIVER ========
Route::middleware(['auth:sanctum', 'role:driver'])->prefix('driver')->group(function () {

//driver status
    Route::get('/driver-activity',[DriverActivtyController::class,'getDriverActivity']);

//driver location
    Route::post('/driver/update-location', [DriverPointController::class, 'updatePointDriverLocation']);
});


//======= USER ========
Route::middleware(['auth:sanctum', 'role:user'])->prefix('user')->group(function () {

    Route::post('/point/pickup-drop', [PointController::class, 'storePointPickupandDrop']);

//Ride Estimate
    Route::post('/ride-estimate', [RideEstimateController::class, 'estimateForAllCategories']);
    Route::post('/ride-estimate/store', [RideEstimateController::class,'storeEstimate']);

//LoggedUser
    Route::get('/logged-user', [LoggedUserController::class, 'getLoggedUser']);
});

