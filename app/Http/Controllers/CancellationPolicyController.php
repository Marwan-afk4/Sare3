<?php

namespace App\Http\Controllers;

use App\Enums\ActiveStatuses;
use App\Models\CancellationPolicy;


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
        $cancellationPolicies = CancellationPolicy::orderBy($sortField, $sortOrder)->paginate(30);
        return view('cancellation-policies.index', compact('cancellationPolicies', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        $statuses = ActiveStatuses::labels();
        return view('cancellation-policies.create', compact('statuses'));
    }

    public function store(StoreCancellationPolicyRequest $request)
    {
        CancellationPolicy::create($request->validated());
        return redirect()->route('cancellation-policies.index')->with('success',  __('Created successfully'));
    }

    public function show(CancellationPolicy $cancellationPolicy)
    {
        return view('cancellation-policies.show', compact('cancellationPolicy'));
    }

    public function edit(CancellationPolicy $cancellationPolicy)
    {
        $statuses = ActiveStatuses::labels();
        return view('cancellation-policies.edit', compact('cancellationPolicy', 'statuses'));
    }

    public function update(UpdateCancellationPolicyRequest $request, CancellationPolicy $cancellationPolicy)
    {
        $cancellationPolicy->update($request->validated());
        return redirect()->route('cancellation-policies.index')->with('success', __('Updated successfully.'));
    }
}
