<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AppSettingController,
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
    SupportChatController,
    WalletRequestController,
    ZoneController
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
            '/zones' => ZoneController::class,
        ]);

        // Additional ride routes
        Route::get('/rides/{ride}/track', [RideController::class, 'track'])->name('rides.track');

        // Settings routes
        Route::get('/settings', [AppSettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [AppSettingController::class, 'update'])->name('settings.update');

        // Profit Statistics routes
        Route::prefix('profit-statistics')->name('profit-statistics.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ProfitStatisticsWebController::class, 'index'])->name('index');
            Route::get('/history', [\App\Http\Controllers\Admin\ProfitStatisticsWebController::class, 'history'])->name('history');
        });

        Route::post('/otp-limits/{otpLimit}/reset-drivers', [OtpLimitController::class, 'resetDrivers'])->name('otp-limits.reset-drivers');
        Route::post('/otp-limits/{otpLimit}/reset-users', [OtpLimitController::class, 'resetUsers'])->name('otp-limits.reset-users');


        Route::get('/drivers/{driver}/documents', [DriverController::class, 'documents'])->name('drivers.documents');
        Route::get('/drivers/{driver}/cars', [DriverController::class, 'cars'])->name('drivers.cars');
        Route::get('/drivers/{driver}/ride-history', [DriverController::class, 'rideHistory'])->name('drivers.ride-history');
        
        Route::get('/users/{user}/ride-history', [UserController::class, 'rideHistory'])->name('users.ride-history');

        // Support Chat Routes
        Route::prefix('support-chat')->name('admin.support-chat.')->group(function () {
            Route::get('/', [SupportChatController::class, 'index'])->name('index');
            Route::get('/conversations/users', [SupportChatController::class, 'getUserConversations'])->name('conversations.users');
            Route::get('/conversations/drivers', [SupportChatController::class, 'getDriverConversations'])->name('conversations.drivers');
            Route::get('/conversation/{conversationId}', [SupportChatController::class, 'getConversation'])->name('conversation.show');
            Route::post('/message', [SupportChatController::class, 'sendMessage'])->name('message.send');
            Route::patch('/conversation/{conversationId}/read', [SupportChatController::class, 'markAsRead'])->name('conversation.read');
            Route::get('/stats', [SupportChatController::class, 'getStats'])->name('stats');
        });
    });
