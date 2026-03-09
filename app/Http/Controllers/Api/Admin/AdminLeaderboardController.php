<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BonusTier;
use App\Models\DriverBonusGrant;
use App\Models\User;
use App\Services\BonusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminLeaderboardController extends Controller
{
    public function __construct(protected BonusService $bonusService) {}

    // ─── Bonus Tiers CRUD ────────────────────────────────────────────────────

    /**
     * GET /admin/bonus/tiers
     * List all bonus tiers
     */
    public function indexTiers(Request $request)
    {
        $tiers = BonusTier::orderBy('period_type')->orderBy('rides_count')->get();

        return response()->json(['data' => $tiers]);
    }

    /**
     * POST /admin/bonus/tiers
     * Create a new bonus tier
     */
    public function storeTier(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'nullable|string|max:100',
            'rides_count' => 'required|integer|min:1',
            'bonus_amount' => 'required|numeric|min:0.01',
            'period_type' => 'required|in:weekly,monthly,all_time',
            'is_active'   => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $tier = BonusTier::create($validator->validated());

        return response()->json(['message' => 'Bonus tier created.', 'data' => $tier], 201);
    }

    /**
     * GET /admin/bonus/tiers/{tier}
     * Show a single bonus tier
     */
    public function showTier(BonusTier $tier)
    {
        return response()->json(['data' => $tier]);
    }

    /**
     * PUT /admin/bonus/tiers/{tier}
     * Update a bonus tier
     */
    public function updateTier(Request $request, BonusTier $tier)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'nullable|string|max:100',
            'rides_count' => 'sometimes|integer|min:1',
            'bonus_amount' => 'sometimes|numeric|min:0.01',
            'period_type' => 'sometimes|in:weekly,monthly,all_time',
            'is_active'   => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $tier->update($validator->validated());

        return response()->json(['message' => 'Bonus tier updated.', 'data' => $tier]);
    }

    /**
     * DELETE /admin/bonus/tiers/{tier}
     * Delete a bonus tier
     */
    public function destroyTier(BonusTier $tier)
    {
        $tier->delete();

        return response()->json(['message' => 'Bonus tier deleted.']);
    }

    /**
     * PATCH /admin/bonus/tiers/{tier}/toggle
     * Toggle active/inactive state
     */
    public function toggleTier(BonusTier $tier)
    {
        $tier->update(['is_active' => !$tier->is_active]);

        return response()->json([
            'message' => 'Tier status toggled.',
            'is_active' => $tier->is_active,
        ]);
    }

    // ─── Leaderboard ─────────────────────────────────────────────────────────

    /**
     * GET /admin/leaderboard
     * View driver leaderboard (admin perspective)
     */
    public function leaderboard(Request $request)
    {
        $periodType = $request->get('period_type', 'monthly');
        $limit = min((int) $request->get('limit', 50), 200);

        if (!in_array($periodType, ['weekly', 'monthly', 'all_time'])) {
            return response()->json(['message' => 'Invalid period_type. Use: weekly, monthly, all_time'], 422);
        }

        $leaderboard = $this->bonusService->getLeaderboard($periodType, $limit);
        $tiers = BonusTier::where('is_active', true)
            ->where('period_type', $periodType)
            ->orderBy('rides_count')
            ->get(['id', 'name', 'rides_count', 'bonus_amount']);

        return response()->json([
            'period_type' => $periodType,
            'tiers' => $tiers,
            'leaderboard' => $leaderboard,
        ]);
    }

    // ─── Manual Bonus Grant ──────────────────────────────────────────────────

    /**
     * POST /admin/bonus/grant
     * Manually grant a bonus to a driver
     */
    public function grantBonus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|exists:users,id',
            'amount'    => 'required|numeric|min:0.01',
            'note'      => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $driver = User::find($request->driver_id);

        if (!$driver->isDriver()) {
            return response()->json(['message' => 'The specified user is not a driver.'], 422);
        }

        $grant = $this->bonusService->grantManualBonus(
            driver: $driver,
            amount: (float) $request->amount,
            admin: $request->user(),
            note: $request->note,
        );

        return response()->json([
            'message' => "Bonus of {$request->amount} has been granted to driver {$driver->name} and added to their wallet.",
            'grant' => $grant,
            'driver_wallet' => $driver->fresh()->wallet,
        ]);
    }

    // ─── Bonus Grant History ─────────────────────────────────────────────────

    /**
     * GET /admin/bonus/grants
     * List all bonus grants (paginated), filterable by driver/type
     */
    public function grantHistory(Request $request)
    {
        $query = DriverBonusGrant::with(['driver:id,name,image', 'tier:id,name,rides_count', 'grantedByAdmin:id,name'])
            ->orderByDesc('created_at');

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $grants = $query->paginate($request->get('per_page', 20));

        return response()->json($grants);
    }

    /**
     * GET /admin/bonus/driver/{driver}
     * Get bonus stats and history for a specific driver
     */
    public function driverBonusStats(Request $request, User $driver)
    {
        if (!$driver->isDriver()) {
            return response()->json(['message' => 'User is not a driver.'], 422);
        }

        $periodType = $request->get('period_type', 'monthly');

        $stats = $this->bonusService->getDriverStats($driver, $periodType);
        $grants = DriverBonusGrant::with(['tier:id,name,rides_count'])
            ->where('driver_id', $driver->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'stats' => $stats,
            'bonus_history' => $grants,
        ]);
    }
}
