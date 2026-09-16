<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DeliveryAgentController extends Controller
{
    /**
     * List delivery captains (role = delivery).
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'delivery')->with('riderVehicle');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $agents = $query->orderByDesc('created_at')->paginate(30)->withQueryString();

        return view('delivery-agents.index', compact('agents'));
    }

    public function show(User $delivery_agent)
    {
        abort_unless($delivery_agent->role === 'delivery', 404);

        $delivery_agent->load('riderVehicle', 'zone');
        $deliveries = $delivery_agent->riderDeliveries()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('delivery-agents.show', compact('delivery_agent', 'deliveries'));
    }

    /**
     * Approve / reject / set pending.
     */
    public function updateStatus(Request $request, User $delivery_agent)
    {
        abort_unless($delivery_agent->role === 'delivery', 404);

        $request->validate([
            'status' => 'required|in:approved,pending,rejected',
            'rejected_reason' => 'nullable|string|max:1000',
        ]);

        $delivery_agent->status = $request->status;
        if ($request->status === 'rejected') {
            $delivery_agent->rejected_reason = $request->rejected_reason;
        }
        $delivery_agent->save();

        return back()->with('success', __('Updated successfully.'));
    }

    public function destroy(User $delivery_agent)
    {
        abort_unless($delivery_agent->role === 'delivery', 404);

        $delivery_agent->delete();

        return redirect()->route('delivery-agents.index')->with('success', __('Deleted successfully'));
    }
}
