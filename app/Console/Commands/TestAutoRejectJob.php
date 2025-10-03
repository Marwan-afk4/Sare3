<?php

namespace App\Console\Commands;

use App\Jobs\AutoRejectRideJob;
use App\Models\Ride;
use Illuminate\Console\Command;

class TestAutoRejectJob extends Command
{
    protected $signature = 'test:auto-reject {ride_id}';
    protected $description = 'Test the auto-reject job for a specific ride';

    public function handle()
    {
        $rideId = $this->argument('ride_id');
        $ride = Ride::find($rideId);

        if (!$ride) {
            $this->error("Ride with ID {$rideId} not found");
            return;
        }

        if (!$ride->driver_id) {
            $this->error("Ride {$rideId} has no driver assigned");
            return;
        }

        $this->info("Dispatching auto-reject job for ride {$rideId} with driver {$ride->driver_id}");

        AutoRejectRideJob::dispatch(
            $ride->id,
            $ride->driver_id,
            $ride->updated_at->format('Y-m-d H:i:s')
        )->delay(now()->addSeconds(5)); // 5 seconds for testing

        $this->info("Job dispatched! It will execute in 5 seconds.");
        $this->info("Make sure your queue worker is running: php artisan queue:work");
    }
}
