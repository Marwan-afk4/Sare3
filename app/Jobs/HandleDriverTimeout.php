<?php

namespace App\Jobs;

use App\Http\Controllers\Api\User\RideEstimateController;
use App\Models\Ride;
use App\Services\RideService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleDriverTimeout implements ShouldQueue
{
    use Queueable ,Dispatchable ,InteractsWithQueue, Queueable, SerializesModels;

    protected $rideId;
    protected $driverId;

    /**
     * Create a new job instance.
     */
    public function __construct($rideId, $driverId)
    {
        $this->rideId = $rideId;
        $this->driverId = $driverId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ride = Ride::find($this->rideId);

        // لو الرحلة لسه معلقة ومربوطة بنفس السواق
        if ($ride && $ride->status === 'pending' && $ride->driver_id == $this->driverId) {

            $rejected = $ride->rejected_drivers ?? [];

            if (!in_array($this->driverId, $rejected)) {
                $rejected[] = $this->driverId;
            }

            // فضي السواق
            $ride->update([
                'driver_id' => null,
                'rejected_drivers' => $rejected,
            ]);
            // دور على سواق بديل
            $rideService = new RideEstimateController();
            $rideService->searchAlternativeDriver($ride);
        }
    }
}
