<?php

namespace App\Http\Controllers;

use App\Models\User;


use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $users = User::orderBy($sortField, $sortOrder)->paginate(30);
        return view('users.index', compact('users', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request)
    {
        User::create($request->validated());
        return redirect()->route('users.index')->with('success', 'Created successfully');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());
        return redirect()->route('users.index')->with('success', 'Updated successfully.');
    }
}
