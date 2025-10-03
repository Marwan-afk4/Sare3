<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Ride;
use App\Jobs\AutoRejectRideJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

echo "=== Auto-Reject System Test ===\n";

// Create a test ride
$ride = new Ride();
$ride->user_id = 1;
$ride->driver_id = 2;
$ride->status = 'pending';
$ride->pickup_lat = 30.0444;
$ride->pickup_lng = 31.2357;
$ride->zone_id = 1;
$ride->car_category_id = 1;
$ride->save();

echo "✓ Created test ride ID: {$ride->id}\n";
echo "✓ Driver ID: {$ride->driver_id}\n";
echo "✓ Status: {$ride->status->value}\n";

// Dispatch the job with 3 second delay for testing
AutoRejectRideJob::dispatch(
    $ride->id,
    $ride->driver_id,
    $ride->updated_at->format('Y-m-d H:i:s')
)->delay(now()->addSeconds(3));

echo "✓ Auto-reject job dispatched (3 second delay)\n";

// Check jobs in queue
$jobCount = DB::table('jobs')->count();
echo "✓ Jobs in queue: {$jobCount}\n";

echo "\nWaiting 5 seconds for job to process...\n";
sleep(5);

// Process the job
echo "Processing queue...\n";
Artisan::call('queue:work', ['--once' => true, '--timeout' => 10]);

// Check the ride again
$ride->refresh();
echo "\n=== Results ===\n";
echo "Ride ID: {$ride->id}\n";
echo "Status: {$ride->status->value}\n";
echo "Driver ID: " . ($ride->driver_id ?? 'null') . "\n";
echo "Auto rejected at: " . ($ride->auto_rejected_at ?? 'null') . "\n";
echo "Rejected drivers: " . json_encode($ride->rejected_drivers) . "\n";

if ($ride->driver_id === null && in_array(2, $ride->rejected_drivers ?? [])) {
    echo "\n✅ SUCCESS: Auto-reject system is working!\n";
} else {
    echo "\n❌ FAILED: Auto-reject system is not working properly.\n";
}
