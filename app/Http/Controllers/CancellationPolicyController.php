<?php

namespace App\Http\Controllers;

use App\Enums\ActiveStatuses;
use App\Models\CancellationPolicy;
use App\Models\Zone;

use Illuminate\Http\Request;
use App\Http\Requests\StoreCancellationPolicyRequest;
use App\Http\Requests\UpdateCancellationPolicyRequest;
use App\Http\Controllers\Controller;

class CancellationPolicyController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $cancellationPolicies = CancellationPolicy::with('zone')->orderBy($sortField, $sortOrder)->paginate(30);
        return view('cancellation-policies.index', compact('cancellationPolicies', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        $statuses = ActiveStatuses::labels();
        $userTypes = [
            'rider' => __('Rider'),
            'driver' => __('Driver')
        ];
        $zones = Zone::orderBy('name')->pluck('name', 'id')->toArray();
        return view('cancellation-policies.create', compact('statuses', 'userTypes', 'zones'));
    }

    public function store(StoreCancellationPolicyRequest $request)
    {
        CancellationPolicy::create($request->validated());
        return redirect()->route('cancellation-policies.index')->with('success',  __('Created successfully'));
    }

    public function show(CancellationPolicy $cancellationPolicy)
    {
        $cancellationPolicy->load('zone');
        return view('cancellation-policies.show', compact('cancellationPolicy'));
    }

    public function edit(CancellationPolicy $cancellationPolicy)
    {
        $statuses = ActiveStatuses::labels();
        $userTypes = [
            'rider' => __('Rider'),
            'driver' => __('Driver')
        ];
        $zones = Zone::orderBy('name')->pluck('name', 'id')->toArray();
        return view('cancellation-policies.edit', compact('cancellationPolicy', 'statuses', 'userTypes', 'zones'));
    }

    public function update(UpdateCancellationPolicyRequest $request, CancellationPolicy $cancellationPolicy)
    {
        $cancellationPolicy->update($request->validated());
        return redirect()->route('cancellation-policies.index')->with('success', __('Updated successfully.'));
    }

    public function destroy(CancellationPolicy $cancellationPolicy)
    {
        try {
            $cancellationPolicy->delete();

            return redirect()
                ->route('cancellation-policies.index')
                ->with('success', __('Cancellation Policy deleted successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->route('cancellation-policies.index')
                ->with('error', __('Failed to delete Cancellation Policy. Please try again.'));
        }
    }
}
