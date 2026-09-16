<?php

namespace App\Jobs;

use App\Events\DeliveryStatusUpdated;
use App\Http\Controllers\Api\User\DeliveryController;
use App\Models\Delivery;
use App\Services\DeliveryOfferService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoRejectDeliveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $deliveryId;
    protected $riderId;
    protected $assignedAt;

    public function __construct($deliveryId, $riderId, $assignedAt)
    {
        $this->deliveryId = $deliveryId;
        $this->riderId = $riderId;
        $this->assignedAt = $assignedAt;
    }

    public function handle()
    {
        $delivery = Delivery::find($this->deliveryId);

        if (!$delivery) {
            return;
        }

        if ($delivery->status->value !== 'pending' || $delivery->rider_id !== $this->riderId) {
            return;
        }

        if ($delivery->updated_at->format('Y-m-d H:i:s') !== $this->assignedAt) {
            return;
        }

        // Consecutive-ignore cooldown, mirroring rides.
        if ($this->riderId) {
            $count = (int) Cache::get("delivery_auto_reject_count:{$delivery->id}:{$this->riderId}", 0) + 1;
            Cache::put("delivery_auto_reject_count:{$delivery->id}:{$this->riderId}", $count, now()->addMinutes(5));
            if ($count >= 2) {
                Cache::put("delivery_cooldown:{$delivery->id}:{$this->riderId}", 'auto', now()->addMinutes(2));
            }
        }

        DB::beginTransaction();

        try {
            $delivery = Delivery::where('id', $this->deliveryId)->lockForUpdate()->first();

            if (!$delivery || $delivery->status->value !== 'pending' || $delivery->rider_id !== $this->riderId) {
                DB::rollBack();
                return;
            }

            $rejected = $delivery->rejected_riders ?? [];
            $rejected = array_map(fn ($id) => is_numeric($id) ? (int) $id : $id, $rejected);
            if (!in_array((int) $this->riderId, $rejected, true)) {
                $rejected[] = (int) $this->riderId;
            }

            $delivery->update([
                'rider_id' => null,
                'rejected_riders' => $rejected,
                'status' => 'pending',
                'auto_rejected_at' => now(),
            ]);

            try {
                app(DeliveryOfferService::class)->markIgnored($delivery, (int) $this->riderId, 'auto_timeout');
            } catch (\Throwable $e) {
                Log::warning("AutoRejectDeliveryJob: offer bookkeeping failed: " . $e->getMessage());
            }

            $delivery->refresh();

            $controller = new DeliveryController();
            $newRiderId = $controller->searchAlternativeRider($delivery);

            if (!$newRiderId) {
                $delivery->update(['status' => 'rejected']);
            }

            DB::commit();

            try {
                DeliveryStatusUpdated::dispatch($delivery, (int) $this->riderId);
            } catch (\Exception $e) {
                Log::error("Failed to broadcast DeliveryStatusUpdated for delivery {$delivery->id}: " . $e->getMessage());
            }

            if ($newRiderId) {
                AutoRejectDeliveryJob::dispatch(
                    $delivery->id,
                    $newRiderId,
                    $delivery->fresh()->updated_at->format('Y-m-d H:i:s')
                )->delay(now()->addSeconds(config('ride.auto_reject_timeout_seconds', 15)));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("AutoRejectDeliveryJob failed for delivery {$this->deliveryId}: " . $e->getMessage());
            throw $e;
        }
    }
}
