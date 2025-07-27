<?php

namespace App\Http\Controllers;

use App\Enums\DriverStatus;
use App\Models\WalletRequest;
use App\Models\Driver;


use Illuminate\Http\Request;
use App\Http\Requests\StoreWalletRequestRequest;
use App\Http\Requests\UpdateWalletRequestRequest;
use App\Http\Controllers\Controller;
use App\Models\User;

class WalletRequestController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $walletRequests = WalletRequest::with(['driver'])->orderBy($sortField, $sortOrder)->paginate(30);
        return view('wallet-requests.index', compact('walletRequests', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        $drivers = User::orderBy('name')->pluck('name', 'id')->toArray();
        return view('wallet-requests.create', compact('drivers'));
    }

    public function store(StoreWalletRequestRequest $request)
    {
        WalletRequest::create($request->validated());
        return redirect()->route('wallet-requests.index')->with('success',  __('Created successfully'));
    }

    public function show(WalletRequest $walletRequest)
    {
        return view('wallet-requests.show', compact('walletRequest'));
    }

    public function edit(WalletRequest $walletRequest)
    {
        $drivers = User::orderBy('name')->pluck('name', 'id')->toArray();
        $statuses = DriverStatus::labels();
        return view('wallet-requests.edit', compact('walletRequest', 'drivers','statuses'));
    }

    public function update(UpdateWalletRequestRequest $request, WalletRequest $walletRequest)
    {
        $walletRequest->update($request->validated());
        return redirect()->route('wallet-requests.index')->with('success', __('Updated successfully.'));
    }
}
