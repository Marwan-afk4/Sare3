<?php

namespace App\Http\Controllers;

use App\Enums\OtpTypes;
use App\Models\CancellationReason;


use Illuminate\Http\Request;
use App\Http\Requests\StoreCancellationReasonRequest;
use App\Http\Requests\UpdateCancellationReasonRequest;
use App\Http\Controllers\Controller;

class CancellationReasonController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $cancellationReasons = CancellationReason::orderBy($sortField, $sortOrder)->paginate(30);
        return view('cancellation-reasons.index', compact('cancellationReasons', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        $types = OtpTypes::labels();
        return view('cancellation-reasons.create', compact('types'));
    }

    public function store(StoreCancellationReasonRequest $request)
    {
        CancellationReason::create($request->validated());
        return redirect()->route('cancellation-reasons.index')->with('success',  __('Created successfully'));
    }

    public function show(CancellationReason $cancellationReason)
    {
        return view('cancellation-reasons.show', compact('cancellationReason'));
    }

    public function edit(CancellationReason $cancellationReason)
    {
        $types = OtpTypes::labels();
        return view('cancellation-reasons.edit', compact('cancellationReason', 'types'));
    }

    public function update(UpdateCancellationReasonRequest $request, CancellationReason $cancellationReason)
    {
        $cancellationReason->update($request->validated());
        return redirect()->route('cancellation-reasons.index')->with('success',  __('Updated successfully.'));
    }

    public function destroy(CancellationReason $cancellationReason)
    {
        try {
            $cancellationReason->delete();

            return redirect()
                ->route('cancellation-reasons.index')
                ->with('success', __('Cancellation Reason deleted successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->route('cancellation-reasons.index')
                ->with('error', __('Failed to delete Cancellation Reason. Please try again.'));
        }
    }
}
