<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Driver\AuthController as DriverAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//======= USER AUTH ========
//phone otp
Route::post('/send-otp', [AuthController::class, 'postOtp']);
Route::post('/check-otp', [AuthController::class, 'checkOtp']);

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



//======= DRIVER AUTH ========
//phone otp
Route::post('/driver/send-otp', [DriverAuthController::class, 'postOtp']);
Route::post('/driver/check-otp', [DriverAuthController::class, 'CheckOtp']);

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
