<?php

namespace App\Http\Controllers;

use App\Models\Ad;


use Illuminate\Http\Request;
use App\Http\Requests\StoreAdRequest;
use App\Http\Requests\UpdateAdRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class AdController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $ads = Ad::orderBy($sortField, $sortOrder)->paginate(30);
        return view('ads.index', compact('ads', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        return view('ads.create');
    }

    public function store(StoreAdRequest $request)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('ads/images', 'public');
            $validatedData['image'] = $path;
        }

        Ad::create($validatedData);
        return redirect()->route('ads.index')->with('success',  __('Created successfully'));
    }

    public function show(Ad $ad)
    {
        return view('ads.show', compact('ad'));
    }

    public function edit(Ad $ad)
    {
        return view('ads.edit', compact('ad'));
    }

    public function update(UpdateAdRequest $request, Ad $ad)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($ad->image && Storage::disk('public')->exists($ad->image)) {
                Storage::disk('public')->delete($ad->image);
            }

            // Store new image
            $path = $request->file('image')->store('ads/images', 'public');
            $validatedData['image'] = $path;
        } else {
            // Keep the old image if no new image is uploaded
            unset($validatedData['image']);
        }

        $ad->update($validatedData);
        return redirect()->route('ads.index')->with('success',  __('Updated successfully.'));
    }

    public function destroy(Ad $ad)
    {
        try {
            // Delete associated image if exists
            if ($ad->image && Storage::disk('public')->exists($ad->image)) {
                Storage::disk('public')->delete($ad->image);
            }

            $ad->delete();

            return redirect()
                ->route('ads.index')
                ->with('success', __('Ad deleted successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->route('ads.index')
                ->with('error', __('Failed to delete ad. Please try again.'));
        }
    }
}
