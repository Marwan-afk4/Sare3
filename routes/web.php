<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AuthController,
    CancelationRideController,
    CancellationPolicyController,
    CarCategoryController,
    CarModelController,
    CarTypeController,
    DocumentTypeController,
    DriverCarController,
    DriverDocumentController,
    HomePageController,
    RatingController,
    UserController,
    DriverController,
    NotificationController,
    OtpLimitController,
    PaymenentMethodController,
    RideController,
    RideRequestTimeLimitController,
    WalletRequestController
};

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [HomePageController::class, 'index'])->name('home');
});

Route::controller(AuthController::class)->group(function () {
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login')->name('do.login');
        Route::post('/logout', 'logout')->middleware('auth:sanctum')->name('logout');
    });

Route::middleware(['auth:sanctum','role:admin'])->prefix('admin')
    ->group(function () {
        Route::resources([
            '/users' => UserController::class,
            '/ratings' => RatingController::class,
            '/driver-documents' => DriverDocumentController::class,
            '/driver-cars' => DriverCarController::class,
            '/drivers' => DriverController::class,
            '/document-types' => DocumentTypeController::class,
            '/car-categories' => CarCategoryController::class,
            '/rides' => RideController::class,
            '/car-types' => CarTypeController::class,
            '/car-models' => CarModelController::class,
            '/wallet-requests' => WalletRequestController::class,
            '/paymenent-methods' => PaymenentMethodController::class,
            '/cancellation-policies' => CancellationPolicyController::class,
            '/cancelation-rides' => CancelationRideController::class,
            '/ride-request-time-limits' => RideRequestTimeLimitController::class,
            '/otp-limits' => OtpLimitController::class,
            '/notifications' => NotificationController::class,
        ]);

        Route::post('/otp-limits/{otpLimit}/reset-drivers', [OtpLimitController::class, 'resetDrivers'])->name('otp-limits.reset-drivers');
        Route::post('/otp-limits/{otpLimit}/reset-users', [OtpLimitController::class, 'resetUsers'])->name('otp-limits.reset-users');


        Route::get('/drivers/{driver}/documents', [DriverController::class, 'documents'])->name('drivers.documents');

        Route::get('/drivers/{driver}/cars', [DriverController::class, 'cars'])->name('drivers.cars');
    });
