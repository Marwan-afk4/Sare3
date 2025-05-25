<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//phone otp
Route::post('/send-otp', [AuthController::class, 'postOtp']);
Route::post('/check-otp', [AuthController::class, 'checkOtp']);

//email otp
Route::post('/send-email-otp', [AuthController::class, 'sendEmailVerificationCode']);
Route::post('/verify-email-otp', [AuthController::class, 'verifyEmailCode']);

//post name
Route::post('/post-name', [AuthController::class, 'postName']);

