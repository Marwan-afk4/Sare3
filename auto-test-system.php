<?php

// Automatic System Test - No Manual Intervention Required
// This script creates a ride and exits - the system should auto-reject it

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Ride;
use App\Jobs\AutoRejectRideJob;

echo "🚀 Starting Automatic Auto-Reject Test\n";
echo "Time: " . now()->format('Y-m-d H:i:s') . "\n\n";

// Create test ride
$user = App\Models\User::first();
$driver = App\Models\User::skip(1)->first() ?? $user;

$ride = new Ride();
$ride->user_id = $user->id;
$ride->driver_id = $driver->id;
$ride->status = 'pending';
$ride->pickup_lat = 30.0444;
$ride->pickup_lng = 31.2357;
$ride->dropoff_lat = 30.0644;
$ride->dropoff_lng = 31.2557;
$ride->zone_id = 1;
$ride->car_category_id = 1;
$ride->pickup_address = 'Auto Test Pickup';
$ride->dropoff_address = 'Auto Test Dropoff';
$ride->save();

// Schedule auto-reject (this simulates what happens in your app)
$timeoutSeconds = config('ride.auto_reject_timeout_seconds', 15);
AutoRejectRideJob::dispatch(
    $ride->id,
    $ride->driver_id,
    $ride->updated_at->format('Y-m-d H:i:s')
)->delay(now()->addSeconds($timeoutSeconds));

echo "✅ Test ride created:\n";
echo "   - Ride ID: {$ride->id}\n";
echo "   - Driver ID: {$ride->driver_id}\n";
echo "   - Status: {$ride->status->value}\n";
echo "   - Created at: " . $ride->created_at->format('H:i:s') . "\n";
echo "   - Will auto-reject at: " . now()->addSeconds($timeoutSeconds)->format('H:i:s') . "\n";

echo "\n🔄 Auto-reject job scheduled for {$timeoutSeconds} seconds\n";
echo "📝 The system will now automatically:\n";
echo "   1. Wait {$timeoutSeconds} seconds\n";
echo "   2. Check if driver responded (they won't)\n";
echo "   3. Auto-reject the ride\n";
echo "   4. Reset driver_id to null\n";
echo "   5. Add driver to rejected list\n";
echo "   6. Search for alternative driver\n";

echo "\n⏰ Check the result in {$timeoutSeconds}+ seconds by running:\n";
echo "   php artisan tinker --execute=\"\$ride = App\\Models\\Ride::find({$ride->id}); echo 'Status: ' . \$ride->status->value . ', Driver: ' . (\$ride->driver_id ?? 'null') . ', Rejected: ' . json_encode(\$ride->rejected_drivers);\"\n";

echo "\n✨ Test complete - system is now running automatically!\n";
?>
