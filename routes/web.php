<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AuthController,
    DocumentTypeController,
    DriverCarController,
    DriverDocumentController,
    HomePageController,
    RatingController,
    UserController,
    DriverController
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
            '/document-types' => DocumentTypeController::class
        ]);

        Route::get('/drivers/{driver}/documents', [DriverController::class, 'documents'])->name('drivers.documents');
    });
