<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\BonusTier;
use App\Models\DriverBonusGrant;
use App\Services\BonusService;
use Illuminate\Http\Request;

class DriverLeaderboardController extends Controller
{
    public function __construct(protected BonusService $bonusService) {}

    /**
     * GET /driver/leaderboard
     * Get the leaderboard and the authenticated driver's position.
     *
     * Query params:
     *   - period_type: weekly | monthly | all_time  (default: monthly)
     *   - limit: max number of entries to return (default: 50, max: 100)
     */
    public function leaderboard(Request $request)
    {
        $periodType = $request->get('period_type', 'monthly');
        $limit = min((int) $request->get('limit', 50), 100);

        if (!in_array($periodType, ['weekly', 'monthly', 'all_time'])) {
            return response()->json(['message' => 'Invalid period_type. Use: weekly, monthly, all_time'], 422);
        }

        $driver = $request->user();
        $leaderboard = $this->bonusService->getLeaderboard($periodType, $limit);
        $myStats = $this->bonusService->getDriverStats($driver, $periodType);

        // Active bonus tiers for this period (so driver knows what to aim for)
        $tiers = BonusTier::where('is_active', true)
            ->where('period_type', $periodType)
            ->orderBy('rides_count')
            ->get(['id', 'name', 'rides_count', 'bonus_amount']);

        return response()->json([
            'period_type' => $periodType,
            'my_stats' => $myStats,
            'tiers' => $tiers,
            'leaderboard' => $leaderboard,
        ]);
    }

    /**
     * GET /driver/leaderboard/my-stats
     * Get the authenticated driver's detailed stats and bonus history.
     *
     * Query params:
     *   - period_type: weekly | monthly | all_time  (default: monthly)
     */
    public function myStats(Request $request)
    {
        $periodType = $request->get('period_type', 'monthly');

        if (!in_array($periodType, ['weekly', 'monthly', 'all_time'])) {
            return response()->json(['message' => 'Invalid period_type. Use: weekly, monthly, all_time'], 422);
        }

        $driver = $request->user();
        $stats = $this->bonusService->getDriverStats($driver, $periodType);

        // All bonus grants for this driver
        $bonusHistory = DriverBonusGrant::with(['tier:id,name,rides_count'])
            ->where('driver_id', $driver->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($g) => [
                'id' => $g->id,
                'amount' => $g->amount,
                'type' => $g->type,
                'note' => $g->note,
                'rides_count' => $g->rides_count,
                'period_label' => $g->period_label,
                'tier' => $g->tier ? [
                    'name' => $g->tier->name,
                    'rides_count' => $g->tier->rides_count,
                ] : null,
                'created_at' => $g->created_at,
            ]);

        // All active tiers across all period types so the driver sees the full picture
        $allTiers = BonusTier::where('is_active', true)
            ->orderBy('period_type')
            ->orderBy('rides_count')
            ->get(['id', 'name', 'rides_count', 'bonus_amount', 'period_type']);

        return response()->json([
            'stats' => $stats,
            'bonus_history' => $bonusHistory,
            'all_tiers' => $allTiers,
        ]);
    }
}
