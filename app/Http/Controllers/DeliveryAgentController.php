<?php

namespace App\Http\Controllers;

use App\Enums\ActivtyType;
use App\Enums\DriverStatus;
use App\Enums\VehicleType;
use App\Http\Requests\UpdateDeliveryAgentRequest;
use App\Models\City;
use App\Models\RiderVehicle;
use App\Models\User;
use App\Models\Zone;
use App\trait\ImageUpload;
use Illuminate\Http\Request;

class DeliveryAgentController extends Controller
{
    use ImageUpload;

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

    public function edit(User $delivery_agent)
    {
        abort_unless($delivery_agent->role === 'delivery', 404);

        $delivery_agent->load('riderVehicle');

        $activityStatuses = ActivtyType::labels();
        $agentStatuses = DriverStatus::labels();
        $vehicleTypes = VehicleType::labels();
        $zones = Zone::orderBy('name')->pluck('name', 'id')->toArray();
        $cities = City::orderBy('name')->pluck('name', 'id')->toArray();
        $hasPassword = ! empty($delivery_agent->getAttributes()['password']);

        return view('delivery-agents.edit', compact(
            'delivery_agent',
            'activityStatuses',
            'agentStatuses',
            'vehicleTypes',
            'zones',
            'cities',
            'hasPassword'
        ));
    }

    public function update(UpdateDeliveryAgentRequest $request, User $delivery_agent)
    {
        abort_unless($delivery_agent->role === 'delivery', 404);

        if ($request->filled('_password_action')) {
            if ($request->input('_password_action') === 'remove') {
                $delivery_agent->forceFill(['password' => null])->save();

                return redirect()
                    ->route('delivery-agents.edit', $delivery_agent)
                    ->with('success', __('Password removed successfully.'));
            }

            $delivery_agent->password = $request->input('rider_password');
            $delivery_agent->save();
            $delivery_agent->refresh();

            if (empty($delivery_agent->getAttributes()['password'])) {
                return back()
                    ->withErrors(['rider_password' => __('Failed to save password. Please try again.')])
                    ->withInput(['_password_action' => 'set']);
            }

            return redirect()
                ->route('delivery-agents.edit', $delivery_agent)
                ->with('success', __('Password updated successfully.'));
        }

        $data = $request->safe()->except([
            'image',
            'vehicle_type',
            'rider_image',
            'identity_image',
            'vehicle_image',
            'license_image',
        ]);

        if (($data['status'] ?? null) !== DriverStatus::Rejected->value) {
            unset($data['rejected_reason']);
        }

        if ($request->hasFile('image')) {
            if ($delivery_agent->image) {
                $this->deleteImage($delivery_agent->image);
            }
            $data['image'] = $this->uploadFile($request->file('image'), 'rider/profiles');
        }

        $delivery_agent->update($data);

        $vehicle = $delivery_agent->riderVehicle ?? new RiderVehicle(['rider_id' => $delivery_agent->id]);
        $vehicle->rider_id = $delivery_agent->id;
        $vehicle->type = $request->input('vehicle_type');

        foreach (['rider_image', 'identity_image', 'vehicle_image', 'license_image'] as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }

            if ($vehicle->{$field}) {
                $this->deleteImage($vehicle->{$field});
            }

            $vehicle->{$field} = $this->uploadFile($request->file($field), 'rider/documents');
        }

        $vehicle->save();

        if ($request->hasFile('rider_image') && ! $request->hasFile('image')) {
            $delivery_agent->image = $vehicle->rider_image;
            $delivery_agent->save();
        }

        return redirect()
            ->route('delivery-agents.show', $delivery_agent)
            ->with('success', __('Delivery agent updated successfully.'));
    }

    public function destroy(User $delivery_agent)
    {
        abort_unless($delivery_agent->role === 'delivery', 404);

        $delivery_agent->delete();

        return redirect()->route('delivery-agents.index')->with('success', __('Deleted successfully'));
    }
}
