<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Rater;
use App\Models\Ratee;


use Illuminate\Http\Request;
use App\Http\Requests\StoreRatingRequest;
use App\Http\Requests\UpdateRatingRequest;
use App\Http\Controllers\Controller;
use App\Models\User;

class RatingController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $ratings = Rating::with(['rater', 'ratee'])->orderBy($sortField, $sortOrder)->paginate(30);
        return view('ratings.index', compact('ratings', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        $raters = User::orderBy('name')->pluck('name', 'id')->toArray();
        $ratees = User::orderBy('name')->pluck('name', 'id')->toArray();
        return view('ratings.create', compact('raters', 'ratees'));
    }

    public function store(StoreRatingRequest $request)
    {
        Rating::create($request->validated());
        return redirect()->route('ratings.index')->with('success', 'Created successfully');
    }

    public function show(Rating $rating)
    {
        return view('ratings.show', compact('rating'));
    }

    public function edit(Rating $rating)
    {
        $raters = User::orderBy('name')->pluck('name', 'id')->toArray();
        $ratees = User::orderBy('name')->pluck('name', 'id')->toArray();
        return view('ratings.edit', compact('rating', 'raters', 'ratees'));
    }

    public function update(UpdateRatingRequest $request, Rating $rating)
    {
        $rating->update($request->validated());
        return redirect()->route('ratings.index')->with('success', 'Updated successfully.');
    }
}
