<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ride_offers', function (Blueprint $table) {
            $table->boolean('was_accepted')->default(false)->after('response');
            $table->timestamp('cancelled_at')->nullable()->after('responded_at');
        });

        DB::table('ride_offers')
            ->where('response', 'accepted')
            ->update(['was_accepted' => true]);

        $phantoms = DB::table('ride_offers')
            ->where('response', 'cancelled_after_accept')
            ->where('response_seconds', 0)
            ->orderBy('id')
            ->get();

        foreach ($phantoms as $phantom) {
            $accepted = DB::table('ride_offers')
                ->where('ride_id', $phantom->ride_id)
                ->where('driver_id', $phantom->driver_id)
                ->where('response', 'accepted')
                ->orderByDesc('id')
                ->first();

            if ($accepted) {
                DB::table('ride_offers')->where('id', $accepted->id)->update([
                    'response' => 'cancelled_after_accept',
                    'was_accepted' => true,
                    'cancelled_at' => $phantom->responded_at,
                    'note' => $phantom->note ?: $accepted->note,
                ]);
            }

            DB::table('ride_offers')->where('id', $phantom->id)->delete();
        }

        // An accept that was followed by another offer did not stay accepted.
        // The captain who accepted is the one who cancelled.
        $followed = DB::table('ride_offers as offer')
            ->where('offer.response', 'accepted')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('ride_offers as later')
                    ->whereColumn('later.ride_id', 'offer.ride_id')
                    ->whereColumn('later.offered_at', '>', 'offer.offered_at');
            })
            ->pluck('offer.id');

        if ($followed->isNotEmpty()) {
            DB::table('ride_offers')->whereIn('id', $followed)->update([
                'response' => 'cancelled_after_accept',
                'was_accepted' => true,
            ]);
        }

        // A cancelled ride must not end the log on a bare "accepted" row.
        $stillAccepted = DB::table('ride_offers as offer')
            ->join('rides', 'rides.id', '=', 'offer.ride_id')
            ->where('rides.status', 'cancelled')
            ->where('offer.response', 'accepted')
            ->select('offer.id', 'offer.ride_id', 'offer.driver_id', 'offer.note')
            ->get();

        foreach ($stillAccepted as $offer) {
            $cancel = DB::table('cancelation_rides')
                ->where('ride_id', $offer->ride_id)
                ->orderByDesc('id')
                ->first();

            $driverCancelled = $cancel
                && $cancel->canceled_by === 'driver'
                && (int) $cancel->driver_id === (int) $offer->driver_id;

            DB::table('ride_offers')->where('id', $offer->id)->update([
                'response' => $driverCancelled ? 'cancelled_after_accept' : 'cancelled_by_user',
                'was_accepted' => true,
                'cancelled_at' => $cancel->canceled_at ?? null,
                'note' => $driverCancelled
                    ? ($cancel->reason ? 'reason_id:'.$cancel->reason : $offer->note)
                    : ($offer->note ?: 'passenger_cancelled'),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('ride_offers', function (Blueprint $table) {
            $table->dropColumn(['was_accepted', 'cancelled_at']);
        });
    }
};
