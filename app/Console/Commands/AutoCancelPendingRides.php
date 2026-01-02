<?php

namespace App\Console\Commands;

use App\Helpers\FcmHelper;
use Illuminate\Console\Command;
use App\Models\Ride;
use Carbon\Carbon;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use Exception;

class AutoCancelPendingRides extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan auto:cancel-pending-rides
     */
    protected $signature = 'auto:cancel-pending-rides';

    /**
     * The console command description.
     */
    protected $description = 'Automatically cancel and remove pending rides that have been searching for a driver for more than 5 minutes.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = now()->format('Y-m-d H:i:s');
        Log::info("🚀 Auto cancel command started at {$startTime}");

        // ✅ 1. Get all rides still pending after 5 minutes
        $rides = Ride::where('status', 'pending')
            ->where('created_at', '<=', Carbon::now()->subMinutes(5))
            ->get();

        if ($rides->isEmpty()) {
            Log::info('✅ No pending rides found to cancel.');
            return Command::SUCCESS;
        }

        foreach ($rides as $ride) {
            try {
                Log::info("⏰ Ride ID {$ride->id} pending for more than 5 mins — cancelling...");

                // ✅ 2. Update DB status to "cancelled"
                $ride->update(['status' => 'cancelled']);

                // ✅ 5. Remove ride from Firebase
                $this->deleteFirebaseRide($ride);

                // ✅ 4. Send notification to the user
                $user = $ride->user; // assuming Ride has user() relationship
                if ($user && $user->fcm_token) {
                    $data = [
                        'title'    => 'Ride Cancelled',
                        'body'     => 'Your ride has been cancelled because no drivers were available.',
                        'msg_type' => 'ride_cancelled',
                        'ride_id'  => (string) $ride->id,
                    ];

                    $response = FcmHelper::sendPushNotification(
                        $user->fcm_token,
                        $data['title'],
                        $data['body'],
                        $data
                    );

                    Log::info("📩 Notification sent to user {$user->id} for cancelled ride {$ride->id}", ['response' => $response]);
                } else {
                    Log::warning("⚠️ No FCM token found for user of ride {$ride->id}");
                }

                Log::info("❌ Ride ID {$ride->id} cancelled and deleted from Firebase.");
            } catch (Exception $e) {
                Log::error("🔥 Error cancelling ride {$ride->id}: " . $e->getMessage());
            }
        }

        Log::info("✅ Auto cancel command finished at " . now()->format('Y-m-d H:i:s'));
        return Command::SUCCESS;
    }

    /**
     * 🧩 Delete ride completely from Firebase Realtime Database
     */
    private function deleteFirebaseRide($ride)
    {
        try {
            $firebase = (new Factory)
                ->withServiceAccount(storage_path('firebase/sarea-adce5-firebase-adminsdk-fbsvc-892a07f554.json'))
                ->withDatabaseUri('https://sarea-adce5-default-rtdb.firebaseio.com')
                ->createDatabase();

            $firebaseRideId = 'ride_' . $ride->id;

            // 🔥 Remove the ride node completely
            $firebase->getReference("rides/$firebaseRideId")->remove();

            Log::info("🧹 Firebase ride {$firebaseRideId} deleted successfully.");
        } catch (Exception $e) {
            Log::error("❌ Failed to delete Firebase ride {$ride->id}: " . $e->getMessage());
        }
    }
}
