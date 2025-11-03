<?php

namespace App\Console\Commands;

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
    protected $description = 'Automatically cancel and remove pending rides that have been searching for a driver for more than 15 minutes.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = now()->format('Y-m-d H:i:s');
        Log::info("🚀 Auto cancel command started at {$startTime}");

        // ✅ 1. Get all rides still pending after 15 minutes
        $rides = Ride::where('status', 'pending')
            ->where('updated_at', '<', Carbon::now()->subMinutes(5))
            ->get();

        if ($rides->isEmpty()) {
            Log::info('✅ No pending rides found to cancel.');
            return Command::SUCCESS;
        }

        foreach ($rides as $ride) {
            try {
                Log::info("⏰ Ride ID {$ride->id} pending for more than 15 mins — cancelling...");

                // ✅ 2. Update DB status to "cancelled"
                $ride->update(['status' => 'cancelled']);

                // ✅ 3. Remove ride from Firebase
                $this->deleteFirebaseRide($ride);

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
                ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
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
