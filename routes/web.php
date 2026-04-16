<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\LeaderboardController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AdController,
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

Route::domain(config('app.dashboard_domain'))->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [HomePageController::class, 'index'])->name('home');

        // Test route to check permissions
        Route::get('/test-permissions', function () {
            $user = auth()->user();
            return [
                'user' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'can_manage_users' => $user->can('إدارة المستخدمين'),
                'can_manage_drivers' => $user->can('إدارة السائقين'),
            ];
        });
    });

    // Public routes
    Route::get('/privacy-policy', [PrivacyPolicyController::class, 'index'])->name('privacy-policy');
    Route::get('/support', [SupportController::class, 'index'])->name('support');

    Route::controller(AuthController::class)->group(function () {
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login')->name('do.login');
        Route::post('/logout', 'logout')->middleware('auth:sanctum')->name('logout');
    });

    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')
        ->group(function () {
            Route::middleware(['can:إدارة المستخدمين'])->group(function () {
                Route::resource('/users', UserController::class);
                Route::get('/users/{user}/ride-history', [UserController::class, 'rideHistory'])->name('users.ride-history');
            });
            Route::middleware(['can:إدارة السائقين'])->group(function () {
                Route::resource('/drivers', DriverController::class);
                Route::get('/drivers/{driver}/documents', [DriverController::class, 'documents'])->name('drivers.documents');
                Route::get('/drivers/{driver}/cars', [DriverController::class, 'cars'])->name('drivers.cars');
                Route::get('/drivers/{driver}/ride-history', [DriverController::class, 'rideHistory'])->name('drivers.ride-history');
            });
            Route::middleware(['can:إدارة طلبات المحفظة'])->resource('/wallet-requests', WalletRequestController::class);
            Route::middleware(['can:إدارة أنواع المستندات'])->resource('/document-types', DocumentTypeController::class);
            Route::middleware(['can:إدارة فئات السيارات'])->resource('/car-categories', CarCategoryController::class);
            Route::middleware(['can:إدارة نماذج السيارات'])->resource('/car-models', CarModelController::class);
            Route::middleware(['can:إدارة أنواع السيارات'])->resource('/car-types', CarTypeController::class);
            Route::middleware(['can:إدارة الرحلات'])->resource('/rides', RideController::class);
            Route::middleware(['can:إدارة طرق الدفع'])->resource('/paymenent-methods', PaymenentMethodController::class);
            Route::middleware(['can:إدارة سياسات الإلغاء'])->resource('/cancellation-policies', CancellationPolicyController::class);
            Route::middleware(['can:إدارة أسباب الإلغاء'])->resource('/cancellation-reasons', CancellationReasonController::class);
            Route::middleware(['can:إدارة الرحلات الملغاه'])->resource('/cancelation-rides', CancelationRideController::class);
            Route::middleware(['can:إدارة حدود وقت طلب الرحلة'])->resource('/ride-request-time-limits', RideRequestTimeLimitController::class);
            Route::middleware(['can:إدارة حدود OTP'])->resource('/otp-limits', OtpLimitController::class);
            Route::middleware(['can:إدارة الإشعارات'])->resource('/notifications', NotificationController::class);
            Route::middleware(['can:إدارة المناطق'])->resource('/zones', ZoneController::class);

            Route::middleware(['can:إدارة الدورات'])->resource('/roles', RoleController::class);
            Route::post('/admins/{admin}/update-wallet-limit', [WalletRequestController::class, 'updateAdminLimit'])->name('admins.update-wallet-limit');
            Route::middleware(['can:إدارة المسؤولين'])->resource('/admins', AdminController::class);
            Route::middleware(['can:إدارة الإعلانات'])->resource('/ads', AdController::class);

            Route::resource('/ratings', RatingController::class); // No specific permission in seeder? Maybe manage users/drivers?
            Route::resource('/driver-documents', DriverDocumentController::class); // manage drivers?
            Route::resource('/driver-cars', DriverCarController::class); // manage drivers?

            Route::patch('/rides/{ride}/status', [RideController::class, 'updateStatus'])->name('rides.updateStatus');

            // Wallet Request additional routes
            Route::post('/wallet-requests/{walletRequest}/send-notification', [WalletRequestController::class, 'sendAcceptanceNotification'])->name('wallet-requests.send-notification');
            Route::post('/wallet-requests/{walletRequest}/add-message', [WalletRequestController::class, 'addMessage'])->name('wallet-requests.add-message');
            Route::post('/drivers/{driver}/add-to-wallet', [WalletRequestController::class, 'addToWallet'])->name('drivers.add-to-wallet');
            Route::post('/drivers/{driver}/subtract-from-wallet', [WalletRequestController::class, 'subtractFromWallet'])->name('drivers.subtract-from-wallet');
            Route::get('/drivers/{driver}/wallet-history', [WalletRequestController::class, 'walletHistory'])->name('drivers.wallet-history');

            // Additional ride routes
            Route::get('/rides/{ride}/track', [RideController::class, 'track'])->name('rides.track');

            // Settings routes
            Route::middleware(['can:إدارة الإعدادات'])->group(function () {
                Route::get('/settings', [AppSettingController::class, 'index'])->name('settings.index');
                Route::put('/settings', [AppSettingController::class, 'update'])->name('settings.update');
            });

            // Profit Statistics routes
            Route::middleware(['can:إدارة إحصائيات الربح'])->prefix('profit-statistics')->name('profit-statistics.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\ProfitStatisticsWebController::class, 'index'])->name('index');
                Route::get('/history', [\App\Http\Controllers\Admin\ProfitStatisticsWebController::class, 'history'])->name('history');
            });

            // Referral Management routes
            Route::middleware(['can:إدارة الإحالات'])->prefix('referrals')->name('referrals.')->group(function () {
                Route::get('/', [AdminReferralController::class, 'index'])->name('index');
                Route::get('/list', [AdminReferralController::class, 'list'])->name('list');
                Route::get('/settings', [AdminReferralController::class, 'settings'])->name('settings');
                Route::put('/settings', [AdminReferralController::class, 'updateSettings'])->name('settings.update');
            });

            // Coupon Management routes
            Route::middleware(['can:إدارة الكوبونات'])->get('/coupons', function () {
                return view('admin.coupons.index', ['currentPage' => 'coupons']);
            })->name('coupons.index');

            // Support Chat Management routes
            Route::middleware(['can:إدارة الدردشة الدعمية'])->prefix('support-chat')->name('support-chat.')->group(function () {
                Route::get('/', [AdminSupportChatController::class, 'index'])->name('index');
                Route::get('/test', function () {
                    return 'Support Chat is working! <a href="' . route('support-chat.index') . '">Go to Support Chat</a>';
                })->name('test');
                Route::get('/test-firebase', [AdminSupportChatController::class, 'testFirebaseConnection'])->name('test-firebase');
                Route::get('/realtime', function () {
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

            Route::get('/drivers/{driver}/location', [DriverController::class, 'getLocation'])->name('drivers.location');

            // Leaderboard & Bonus Management
            Route::prefix('leaderboard')->name('leaderboard.')->group(function () {
                Route::get('/', [LeaderboardController::class, 'index'])->name('index');
                Route::post('/grant', [LeaderboardController::class, 'grantBonus'])->name('grant');
                Route::post('/tiers', [LeaderboardController::class, 'storeTier'])->name('tier.store');
                Route::put('/tiers/{tier}', [LeaderboardController::class, 'updateTier'])->name('tier.update');
                Route::patch('/tiers/{tier}/toggle', [LeaderboardController::class, 'toggleTier'])->name('tier.toggle');
                Route::delete('/tiers/{tier}', [LeaderboardController::class, 'destroyTier'])->name('tier.destroy');
            });
        });
});
