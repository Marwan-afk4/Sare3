<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AppSettingController,
    AuthController,
    CancelationRideController,
    CancellationPolicyController,
    CancellationReasonController,
    CarCategoryController,
    CarModelController,
    CarTypeController,
    DocumentTypeController,
    DriverCarController,
    DriverDocumentController,
    HomePageController,
    PrivacyPolicyController,
    RatingController,
    SupportController,
    UserController,
    DriverController,
    NotificationController,
    OtpLimitController,
    PaymenentMethodController,
    RideController,
    RideRequestTimeLimitController,
    WalletRequestController,
    ZoneController,
};
use App\Http\Controllers\Admin\ReferralController as AdminReferralController;
use App\Http\Controllers\Admin\SupportChatController as AdminSupportChatController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [HomePageController::class, 'index'])->name('home');
});

// Public routes
Route::get('/privacy-policy', [\App\Http\Controllers\PrivacyPolicyController::class, 'index'])->name('privacy-policy');
Route::get('/support', [\App\Http\Controllers\SupportController::class, 'index'])->name('support');

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
            '/cancellation-reasons' => CancellationReasonController::class,
        ]);

        Route::patch('/rides/{ride}/status', [RideController::class, 'updateStatus'])->name('rides.updateStatus');

        // Wallet Request additional routes
        Route::post('/wallet-requests/{walletRequest}/send-notification', [WalletRequestController::class, 'sendAcceptanceNotification'])->name('wallet-requests.send-notification');
        Route::post('/wallet-requests/{walletRequest}/add-message', [WalletRequestController::class, 'addMessage'])->name('wallet-requests.add-message');

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

        // Referral Management routes
        Route::prefix('referrals')->name('referrals.')->group(function () {
            Route::get('/', [AdminReferralController::class, 'index'])->name('index');
            Route::get('/list', [AdminReferralController::class, 'list'])->name('list');
            Route::get('/settings', [AdminReferralController::class, 'settings'])->name('settings');
            Route::put('/settings', [AdminReferralController::class, 'updateSettings'])->name('settings.update');
        });

        // Coupon Management routes
        Route::get('/coupons', function () {
            return view('admin.coupons.index', ['currentPage' => 'coupons']);
        })->name('coupons.index');

        // Support Chat Management routes
        Route::prefix('support-chat')->name('support-chat.')->group(function () {
            Route::get('/', [AdminSupportChatController::class, 'index'])->name('index');
            Route::get('/test', function() {
                return 'Support Chat is working! <a href="' . route('support-chat.index') . '">Go to Support Chat</a>';
            })->name('test');
            Route::get('/test-firebase', [AdminSupportChatController::class, 'testFirebaseConnection'])->name('test-firebase');
            Route::get('/realtime', function() {
                $stats = [
                    'total_requests' => 0,
                    'pending_requests' => 0,
                    'in_progress_requests' => 0,
                    'resolved_requests' => 0,
                    'closed_requests' => 0,
                    'today_requests' => 0,
                    'this_week_requests' => 0,
                    'this_month_requests' => 0,
                    'total_unread_messages' => 0,
                    'user_conversations' => 0,
                    'driver_conversations' => 0
                ];
                return view('admin.support-chat.realtime', compact('stats'));
            })->name('realtime');
            Route::get('/active-requests', [AdminSupportChatController::class, 'getActiveSupportRequests'])->name('active-requests');
            Route::get('/chat/{target_id}/{target_type}', [AdminSupportChatController::class, 'getChatMessages'])->name('chat');
            Route::post('/reply', [AdminSupportChatController::class, 'sendReply'])->name('reply');
            Route::patch('/requests/{support_request}/status', [AdminSupportChatController::class, 'updateSupportRequestStatus'])->name('update-status');
            Route::get('/statistics', [AdminSupportChatController::class, 'getStatistics'])->name('statistics');
        });

        Route::post('/otp-limits/{otpLimit}/reset-drivers', [OtpLimitController::class, 'resetDrivers'])->name('otp-limits.reset-drivers');
        Route::post('/otp-limits/{otpLimit}/reset-users', [OtpLimitController::class, 'resetUsers'])->name('otp-limits.reset-users');


        Route::get('/drivers/{driver}/documents', [DriverController::class, 'documents'])->name('drivers.documents');
        Route::get('/drivers/{driver}/cars', [DriverController::class, 'cars'])->name('drivers.cars');
        Route::get('/drivers/{driver}/ride-history', [DriverController::class, 'rideHistory'])->name('drivers.ride-history');

        Route::get('/users/{user}/ride-history', [UserController::class, 'rideHistory'])->name('users.ride-history');


    });
