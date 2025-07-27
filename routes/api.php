<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Driver\AuthController as DriverAuthController;
use App\Http\Controllers\Api\Driver\DriverActivtyController;
use App\Http\Controllers\Api\Driver\DriverLocationController;
use App\Http\Controllers\Api\Driver\DriverProfileController;
use App\Http\Controllers\Api\Driver\PointController as DriverPointController;
use App\Http\Controllers\Api\Driver\RaitingController as DriverRaitingController;
use App\Http\Controllers\Api\Driver\RideActionsController;
use App\Http\Controllers\Api\Driver\RideSettingCOntroller;
use App\Http\Controllers\Api\Driver\WalletRequestController;
use App\Http\Controllers\Api\Paytabs\PaymentController;
use App\Http\Controllers\Api\User\LoggedUserController;
use App\Http\Controllers\Api\User\PointController;
use App\Http\Controllers\Api\User\ProfileController;
use App\Http\Controllers\Api\User\RaitingController;
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

//store car
Route::post('/driver/store-car', [DriverAuthController::class, 'storeDriverCar']);

//get model type ids
Route::get('/driver/get-model-type-ids', [DriverAuthController::class, 'getModelTypeIds']);

//login
Route::post('/driver/login', [DriverAuthController::class, 'login']);


//======= DRIVER ========
Route::middleware(['auth:sanctum', 'role:driver'])->prefix('driver')->group(function () {

//driver status
    Route::get('/driver-activity',[DriverActivtyController::class,'getDriverActivity']);

//driver location
    Route::post('/driver/update-location', [DriverPointController::class, 'updatePointDriverLocation']);

//driver status
    Route::get('/driver-status', [DriverActivtyController::class, 'getDriverStatus']);

//Profile
    Route::get('/get-profile', [DriverProfileController::class, 'getProfileData']);
    Route::put('/update-profile', [DriverProfileController::class, 'updateDriverProfile']);

//Ride Acrions
    Route::post('/ride/accept', [RideActionsController::class, 'acceptRide']);
    Route::post('/ride/cancel', [RideActionsController::class, 'cancelRide']);
    Route::post('/ride/arrived', [RideActionsController::class, 'arrived']);
    Route::post('/ride/start', [RideActionsController::class, 'startRide']);
    Route::post('/ride/complete', [RideActionsController::class, 'completeRide']);
    Route::post('/ride/finish', [RideActionsController::class, 'finishRide']);

//Raiting
    Route::post('/ride/rating', [DriverRaitingController::class,'raiting']);

//DriverLocationUpdate
    Route::post('/ride/update-location', [DriverLocationController::class, 'updateDriverLocation']);
    // Route::post('/ride/end', [DriverLocationController::class, 'endRide']);

//Wallet Request
    Route::post('/wallet/request', [WalletRequestController::class, 'requestWallet']);
    Route::get('/wallet/requests', [WalletRequestController::class, 'getWalletRequests']);

//Ride Setting
    Route::post('/ride-setting', [RideSettingCOntroller::class, 'addRideSetting']);

});


//======= USER ========
Route::middleware(['auth:sanctum', 'role:user'])->prefix('user')->group(function () {

    Route::post('/point/pickup-drop', [PointController::class, 'storePointPickupandDrop']);

//Ride Estimate
    Route::post('/ride-estimate', [RideEstimateController::class, 'estimateForAllCategories']);

//LoggedUser
    Route::get('/logged-user', [LoggedUserController::class, 'getLoggedUser']);

//Payments
    Route::post('/paytabs/card/save',   [PaymentController::class, 'storeTokenizedCard']);
    Route::post('/paytabs/card/charge', [PaymentController::class, 'chargeSavedCard']);

//Profile
    Route::get('/get-profile', [ProfileController::class, 'getProfileData']);
    Route::put('/update-profile', [ProfileController::class, 'updateUserProfile']);

//Ride
    Route::post('/ride/create', [RideEstimateController::class,'createRide']);

//Raiting
    Route::post('/ride/rating', [RaitingController::class,'raiting']);
});

