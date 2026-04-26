<?php

use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Api\Admin\AdminLeaderboardController;
use App\Http\Controllers\Api\Admin\AdminSettingsController;
use App\Http\Controllers\Api\Admin\AdminSupportChatController;
use App\Http\Controllers\Api\Admin\DriverController as AdminDriverController;
use App\Http\Controllers\Api\Admin\ProfitStatisticsController;
use App\Http\Controllers\Api\Admin\ReferralController as AdminReferralController;
use App\Http\Controllers\Api\AppSettingsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\DeleteAccountController;
use App\Http\Controllers\Api\Driver\AuthController as DriverAuthController;
use App\Http\Controllers\Api\Driver\CancelationReasonController as DriverCancelationReasonController;
use App\Http\Controllers\Api\Driver\DriverActivtyController;
use App\Http\Controllers\Api\Driver\DriverLeaderboardController;
use App\Http\Controllers\Api\Driver\DriverLocationController;
use App\Http\Controllers\Api\Driver\DriverNotificationController;
use App\Http\Controllers\Api\Driver\DriverProfileController;
use App\Http\Controllers\Api\Driver\PointController as DriverPointController;
use App\Http\Controllers\Api\Driver\RaitingController as DriverRaitingController;
use App\Http\Controllers\Api\Driver\ReferralController as DriverReferralController;
use App\Http\Controllers\Api\Driver\RideActionsController;
use App\Http\Controllers\Api\Driver\RideRequestLimitController;
use App\Http\Controllers\Api\Driver\RideSettingCOntroller;
use App\Http\Controllers\Api\Driver\TransactionController;
use App\Http\Controllers\Api\Driver\WalletRequestController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Paytabs\PaymentController;
use App\Http\Controllers\Api\User\AdsController;
use App\Http\Controllers\Api\User\CancelationRide;
use App\Http\Controllers\Api\User\LoggedUserController;
use App\Http\Controllers\Api\User\PaymentMethodController;
use App\Http\Controllers\Api\User\PointController;
use App\Http\Controllers\Api\User\ProfileController;
use App\Http\Controllers\Api\User\RaitingController;
use App\Http\Controllers\Api\User\RideEstimateController;
use App\Http\Controllers\Api\RideTrackingController;
use App\Http\Controllers\Api\User\CancelationReasonController;
use App\Http\Controllers\Api\User\ReferralController;
use App\Http\Controllers\Api\User\RideActionsController as UserRideActionsController;
use App\Http\Controllers\Api\User\TransactionController as UserTransactionController;
use App\Http\Controllers\Api\User\UserNotificatonController;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
Route::domain(config('app.api_domain'))->group(function () {
    //======= USER AUTH ========
    //wp phone otp
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('/check-user-otp-limit', [ProfileController::class, 'checkUserOtpLimit']);

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

    //login (password step) + verify login OTP (token step)
    Route::post('/login', [AuthController::class, 'login']);

    //Ads
    Route::get('/ads', [AdsController::class, 'getAds']);

    //======= DRIVER AUTH ========
    //wp phone otp
    Route::post('/driver/send-otp', [DriverAuthController::class, 'sendOtp']);
    Route::post('/driver/verify-otp', [DriverAuthController::class, 'verifyOtp']);
    Route::post('/driver/resend-otp', [DriverAuthController::class, 'resendOtp']);

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
    Route::middleware(['auth:sanctum'])->prefix('driver')->group(function () {

    //Zones
        Route::get('/zones', [RideEstimateController::class, 'zones']);

    //driver status
        //Route::get('/driver-activity',[DriverActivtyController::class,'getDriverActivity']);

    //driver status
        Route::get('/driver-status', [DriverActivtyController::class, 'getDriverStatus']);
        Route::post('/update-availability', [DriverActivtyController::class, 'updateAvailability']);

    //wallet status
        Route::get('/wallet-status', [DriverProfileController::class, 'checkWalletStatus']);

    //Profile
        Route::get('/get-profile', [DriverProfileController::class, 'getProfileData']);
        Route::put('/update-profile', [DriverProfileController::class, 'updateDriverProfile']);
        Route::get('/driver-rides', [DriverProfileController::class, 'getDriverCompletedRides']);

    //Ride Acrions
        Route::post('/ride/accept', [RideActionsController::class, 'acceptRide']);
        Route::post('/ride/cancel', [RideActionsController::class, 'cancelRide']);
        Route::post('/ride/arrived', [RideActionsController::class, 'arrived']);
        Route::post('/ride/verify-code', [RideActionsController::class, 'verifyRideCode']);
        Route::get('/ride/verification-status', [RideActionsController::class, 'getVerificationStatus']);
        Route::post('/ride/start', [RideActionsController::class, 'startRide']);
        Route::post('/ride/complete', [RideActionsController::class, 'completeRide']);
        Route::post('/ride/finish', [RideActionsController::class, 'finishRide']);

    //Raiting
        Route::post('/ride/rating', [DriverRaitingController::class,'raiting']);

    //DriverLocationUpdate
        Route::post('/update-location', [DriverLocationController::class, 'updateGeneralLocation']);
        Route::post('/ride/update-location', [DriverLocationController::class, 'updateDriverLocation']);
        // Route::post('/ride/end', [DriverLocationController::class, 'endRide']);

    //Wallet Request
        Route::post('/wallet/request', [WalletRequestController::class, 'requestWallet']);
        Route::get('/wallet/requests', [WalletRequestController::class, 'getWalletRequests']);
        Route::get('/wallet/request-messages/{id}', [WalletRequestController::class, 'getWalletRequestMessages']);
        Route::post('/wallet/send-request-message/{id}', [WalletRequestController::class, 'sendWalletRequestMessage']);

    //Ride Setting
        Route::post('/ride-setting', [RideSettingCOntroller::class, 'addRideSetting']);
        Route::get('/get-ride-setting', [RideSettingCOntroller::class, 'getRideSetting']);

    //Cancel Ride
        Route::post('/cancel-ride', [CancelationRide::class, 'cancel']);

    //Request Ride Limit
        Route::get('/ride-request-time-limits', [RideRequestLimitController::class, 'getRideRequestLimit']);

    //Logout
        Route::delete('/logout', [DriverAuthController::class, 'logout']);

    //Notification
        Route::post('/push-notification', [NotificationController::class, 'broadcastNotification']);
        Route::get('/notifications', [DriverNotificationController::class, 'getDriverNotificaions']);

    //is in ride
        Route::get('/driver-in-ride', [DriverProfileController::class, 'isInRide']);

    //FCM Token
        Route::post('/fcm-token', [NotificationController::class, 'fcmTOken']);

    //check driver otp limit
        Route::post('/check-driver-otp-limit', [ProfileController::class, 'checkUserOtpLimit']);

    //Referrals
        Route::post('/referrals/generate', [DriverReferralController::class, 'generateLink']);
        Route::get('/referrals/stats', [DriverReferralController::class, 'getStats']);
        Route::get('/referrals/discount-status', [DriverReferralController::class, 'getDiscountStatus']);
        Route::post('/referrals/validate', [DriverReferralController::class, 'validateCode']);
        Route::post('/referrals/apply', [DriverReferralController::class, 'applyCode']);

    //AddZone
        Route::post('/add-zone', [DriverProfileController::class, 'addZoneId']);

    //Cancelation Reasons
        Route::get('/cancelation-reasons', [DriverCancelationReasonController::class, 'getDriverCancelationReason']);

    //transfare to user wallet
        Route::post('/transfare-to-user-wallet', [TransactionController::class, 'transfareToUserWallet']);

    //Driver car presence
        Route::get('/has-car', [DriverProfileController::class, 'hasCarData']);

    //Delete Account
        Route::delete('/delete-account', [DeleteAccountController::class, 'deleteAccount']);

    //Leaderboard
        Route::get('/leaderboard', [DriverLeaderboardController::class, 'leaderboard']);
        Route::get('/leaderboard/my-stats', [DriverLeaderboardController::class, 'myStats']);

    });


    //======= USER ========
    Route::middleware(['auth:sanctum'])->prefix('user')->group(function () {

        Route::post('/point/pickup-drop', [PointController::class, 'storePointPickupandDrop']);

    //Ride Estimate
        Route::post('/ride-estimate', [RideEstimateController::class, 'estimateForAllCategories']);
        Route::get('/zones', [RideEstimateController::class, 'zones']);

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
        Route::get('/ride/verification-code', [UserRideActionsController::class, 'getVerificationCode']);
        Route::get('/ride/status', [UserRideActionsController::class, 'getRideStatus']);

    //Raiting
        Route::post('/ride/rating', [RaitingController::class,'raiting']);

    //Payment Methods
        Route::get('/payment-methods', [PaymentMethodController::class, 'getPaymentMethods']);

    //Cancel Ride
        Route::post('/cancel-ride', [CancelationRide::class, 'cancel']);

    //Logout
        Route::delete('/logout', [AuthController::class, 'logout']);

    //Notification
        Route::post('/push-notification', [NotificationController::class, 'broadcastNotification']);
        Route::get('/notifications', [UserNotificatonController::class, 'getNotificaions']);

    //user in Ride
        Route::get('/user-in-ride', [ProfileController::class, 'isInRide']);

    //FCM Token
        Route::post('/fcm-token', [NotificationController::class, 'fcmTOken']);

    //check user otp limit
        Route::post('/check-user-otp-limit', [ProfileController::class, 'checkUserOtpLimit']);

    //Referrals
        Route::post('/referrals/generate', [ReferralController::class, 'generateLink']);
        Route::get('/referrals/stats', [ReferralController::class, 'getStats']);
        Route::get('/referrals/discount-status', [ReferralController::class, 'getDiscountStatus']);
        Route::post('/referrals/validate', [ReferralController::class, 'validateCode']);
        Route::post('/referrals/apply', [ReferralController::class, 'applyCode']);

    //Coupons
        Route::get('/coupons/available', [CouponController::class, 'getUserCoupons']);
        Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);
        Route::post('/coupons/apply', [CouponController::class, 'applyCoupon']);
        Route::post('/coupons/remove', [CouponController::class, 'removeCoupon']);

    //Cancelation Reasons
        Route::get('/cancelation-reasons', [CancelationReasonController::class, 'getUserCancelationReason']);

    //User Transactions
        Route::get('/transactions', [UserTransactionController::class, 'getTransactions']);

    //AddZone
        Route::post('/add-zone', [ProfileController::class, 'addZoneId']);

    //Delete Account
        Route::delete('/delete-account', [DeleteAccountController::class, 'deleteAccount']);

    });

    //======= RIDE TRACKING (Public/Admin) ========

    Route::prefix('rides')->group(function () {
        Route::get('/{rideId}/driver-location', [RideTrackingController::class, 'getDriverLocation']);
        Route::get('/{rideId}/route-points', [RideTrackingController::class, 'getRoutePoints']);
        Route::get('/{rideId}/tracking-data', [RideTrackingController::class, 'getRideTrackingData']);
    });

    //======= APP SETTINGS (Public) ========
    Route::get('/settings', [AppSettingsController::class, 'getSettings']);
    Route::get('/settings/ride-verification-enabled', [AppSettingsController::class, 'isRideVerificationEnabled']);

    //======= ADMIN SETTINGS ========
    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
        // Driver Management
        // Route::get('/drivers', [AdminDriverController::class, 'index']);
        // Route::get('/drivers/{id}', [AdminDriverController::class, 'show']);

        // Profit Settings
        Route::get('/profit-percentage', [AdminSettingsController::class, 'getProfitPercentage']);
        Route::post('/profit-percentage', [AdminSettingsController::class, 'setProfitPercentage']);

        // Wallet Settings
        Route::get('/minimum-driver-wallet-balance', [AdminSettingsController::class, 'getMinimumDriverWalletBalance']);
        Route::post('/minimum-driver-wallet-balance', [AdminSettingsController::class, 'setMinimumDriverWalletBalance']);

        Route::get('/settings', [AdminSettingsController::class, 'getAllSettings']);

        // Profit Statistics
        Route::get('/profit-statistics', [ProfitStatisticsController::class, 'getProfitStatistics']);
        Route::get('/profit-statistics/daily', [ProfitStatisticsController::class, 'getDailyProfitBreakdown']);
        Route::get('/profit-statistics/top-drivers', [ProfitStatisticsController::class, 'getTopEarningDrivers']);
        Route::get('/profit-history', [ProfitStatisticsController::class, 'getProfitHistory']);

        // Referral Management
        Route::get('/referrals/settings', [AdminReferralController::class, 'getSettings']);
        Route::post('/referrals/settings', [AdminReferralController::class, 'updateSettings']);
        Route::get('/referrals/statistics', [AdminReferralController::class, 'getStatistics']);
        Route::get('/referrals/list', [AdminReferralController::class, 'getReferralList']);

        // Coupon Management
        Route::get('/coupons', [AdminCouponController::class, 'index']);
        Route::post('/coupons', [AdminCouponController::class, 'store']);
        Route::get('/coupons/{coupon}', [AdminCouponController::class, 'show']);
        Route::put('/coupons/{coupon}', [AdminCouponController::class, 'update']);
        Route::delete('/coupons/{coupon}', [AdminCouponController::class, 'destroy']);
        Route::patch('/coupons/{coupon}/toggle-status', [AdminCouponController::class, 'toggleStatus']);
        Route::get('/coupons-statistics', [AdminCouponController::class, 'statistics']);

        // Support Chat Management
        Route::get('/support/active-requests', [AdminSupportChatController::class, 'getActiveSupportRequests']);
        Route::get('/support/chat/{target_id}/{target_type}', [AdminSupportChatController::class, 'getChatMessages']);
        Route::post('/support/reply', [AdminSupportChatController::class, 'sendReply']);
        Route::patch('/support/requests/{support_request}/status', [AdminSupportChatController::class, 'updateSupportRequestStatus']);
        Route::get('/support/statistics', [AdminSupportChatController::class, 'getSupportStatistics']);

        // Zone Notifications
        Route::post('/notifications/zone', [NotificationController::class, 'broadcastNotificationToZone']);

        // Leaderboard & Bonus Management
        Route::get('/leaderboard', [AdminLeaderboardController::class, 'leaderboard']);

        // Bonus Tiers CRUD
        Route::get('/bonus/tiers', [AdminLeaderboardController::class, 'indexTiers']);
        Route::post('/bonus/tiers', [AdminLeaderboardController::class, 'storeTier']);
        Route::get('/bonus/tiers/{tier}', [AdminLeaderboardController::class, 'showTier']);
        Route::put('/bonus/tiers/{tier}', [AdminLeaderboardController::class, 'updateTier']);
        Route::delete('/bonus/tiers/{tier}', [AdminLeaderboardController::class, 'destroyTier']);
        Route::patch('/bonus/tiers/{tier}/toggle', [AdminLeaderboardController::class, 'toggleTier']);

        // Manual Bonus Grant & History
        Route::post('/bonus/grant', [AdminLeaderboardController::class, 'grantBonus']);
        Route::get('/bonus/grants', [AdminLeaderboardController::class, 'grantHistory']);
        Route::get('/bonus/driver/{driver}', [AdminLeaderboardController::class, 'driverBonusStats']);

    });

    //======= DEBUG ENDPOINT (Temporary) ========
    Route::get('/debug/ride/{rideId}', function($rideId) {
        $ride = \App\Models\Ride::find($rideId);

        if (!$ride) {
            return response()->json(['error' => 'Ride not found'], 404);
        }

        return response()->json([
            'ride_id' => $ride->id,
            'status' => $ride->status,
            'driver_id' => $ride->driver_id,
            'verification_code' => $ride->verification_code,
            'verification_code_verified' => $ride->verification_code_verified,
            'verification_enabled' => AppSetting::isRideVerificationEnabled()
        ]);
    });

    // Debug Support Chat (No Auth Required)
    Route::get('/debug/support-chats', function() {
        try {
            $controller = new \App\Http\Controllers\Api\Admin\AdminSupportChatController(
                app(\App\Services\FirebaseChatService::class)
            );

            return $controller->getActiveSupportRequests();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    });

    // Debug Admin Auth
    Route::middleware(['auth:sanctum', 'role:admin'])->get('/debug/admin-auth', function() {
        return response()->json([
            'success' => true,
            'user_id' => Auth::id(),
            'user_email' => Auth::user()->email,
            'user_roles' => Auth::user()->getRoleNames(),
            'message' => 'Admin authentication working'
        ]);
    });
});
