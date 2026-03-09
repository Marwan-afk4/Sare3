<?php

namespace App\Http\Controllers;

use App\Models\BonusTier;
use App\Models\DriverBonusGrant;
use App\Models\User;
use App\Services\BonusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function __construct(protected BonusService $bonusService) {}

    /**
     * Main leaderboard page — shows rankings, bonus tiers, and grant history.
     */
    public function index(Request $request)
    {
        $periodType = $request->get('period_type', 'monthly');
        if (!in_array($periodType, ['weekly', 'monthly', 'all_time'])) {
            $periodType = 'monthly';
        }

        $leaderboard = $this->bonusService->getLeaderboard($periodType, 100);

        $tiers = BonusTier::orderBy('period_type')->orderBy('rides_count')->get();

        $recentGrants = DriverBonusGrant::with([
                'driver:id,name,image',
                'tier:id,name,rides_count',
                'grantedByAdmin:id,name',
            ])
            ->orderByDesc('created_at')
            ->paginate(15);

        $drivers = User::where('role', 'driver')
            ->select('id', 'name', 'phone')
            ->orderBy('name')
            ->get();

        // Summary stats
        $totalBonusGranted = DriverBonusGrant::sum('amount');
        $manualBonusGranted = DriverBonusGrant::where('type', 'manual')->sum('amount');
        $tierBonusGranted = DriverBonusGrant::where('type', 'tier')->sum('amount');
        $totalDriversRanked = count($leaderboard);

        return view('leaderboard.index', compact(
            'periodType',
            'leaderboard',
            'tiers',
            'recentGrants',
            'drivers',
            'totalBonusGranted',
            'manualBonusGranted',
            'tierBonusGranted',
            'totalDriversRanked',
        ));
    }

    /**
     * Store a new bonus tier.
     */
    public function storeTier(Request $request)
    {
        $request->validate([
            'name'         => 'nullable|string|max:100',
            'rides_count'  => 'required|integer|min:1',
            'bonus_amount' => 'required|numeric|min:0.01',
            'period_type'  => 'required|in:weekly,monthly,all_time',
        ]);

        BonusTier::create($request->only('name', 'rides_count', 'bonus_amount', 'period_type'));

        return back()->with('success', 'Bonus tier created successfully.');
    }

    /**
     * Update an existing bonus tier.
     */
    public function updateTier(Request $request, BonusTier $tier)
    {
        $request->validate([
            'name'         => 'nullable|string|max:100',
            'rides_count'  => 'required|integer|min:1',
            'bonus_amount' => 'required|numeric|min:0.01',
            'period_type'  => 'required|in:weekly,monthly,all_time',
        ]);

        $tier->update($request->only('name', 'rides_count', 'bonus_amount', 'period_type'));

        return back()->with('success', 'Bonus tier updated.');
    }

    /**
     * Toggle a tier's active/inactive state.
     */
    public function toggleTier(BonusTier $tier)
    {
        $tier->update(['is_active' => !$tier->is_active]);

        return back()->with('success', 'Tier status updated.');
    }

    /**
     * Delete a bonus tier.
     */
    public function destroyTier(BonusTier $tier)
    {
        $tier->delete();

        return back()->with('success', 'Bonus tier deleted.');
    }

    /**
     * Grant a manual bonus to a driver.
     */
    public function grantBonus(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id',
            'amount'    => 'required|numeric|min:0.01',
            'note'      => 'nullable|string|max:500',
        ]);

        $driver = User::findOrFail($request->driver_id);

        if (!$driver->isDriver()) {
            return back()->withErrors(['driver_id' => 'The selected user is not a driver.']);
        }

        $this->bonusService->grantManualBonus(
            driver: $driver,
            amount: (float) $request->amount,
            admin: $request->user(),
            note: $request->note,
        );

        return back()->with('success', "Bonus of {$request->amount} granted to {$driver->name} and added to their wallet.");
    }
}
