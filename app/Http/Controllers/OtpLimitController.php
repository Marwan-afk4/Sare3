<?php

namespace App\Http\Controllers;

use App\Enums\OtpTypes;
use App\Models\OtpLimit;


use Illuminate\Http\Request;
use App\Http\Requests\StoreOtpLimitRequest;
use App\Http\Requests\UpdateOtpLimitRequest;
use App\Http\Controllers\Controller;
use App\Models\User;

class OtpLimitController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $otpLimits = OtpLimit::orderBy($sortField, $sortOrder)->paginate(30);
        return view('otp-limits.index', compact('otpLimits', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        $otpTypes = OtpTypes::labels();
        return view('otp-limits.create', compact('otpTypes'));
    }

    public function store(StoreOtpLimitRequest $request)
    {
        OtpLimit::create($request->validated());
        return redirect()->route('otp-limits.index')->with('success',  __('Created successfully'));
    }

    public function show(OtpLimit $otpLimit)
    {
        return view('otp-limits.show', compact('otpLimit'));
    }

    public function edit(OtpLimit $otpLimit)
    {
        $otpTypes = OtpTypes::labels();
        return view('otp-limits.edit', compact('otpLimit', 'otpTypes'));
    }

    public function update(UpdateOtpLimitRequest $request, OtpLimit $otpLimit)
    {
        $otpLimit->update($request->validated());
        return redirect()->route('otp-limits.index')->with('success',  __('Updated successfully.'));
    }

    public function destroy(OtpLimit $otpLimit)
    {
        try {
            $otpLimit->delete();

            return redirect()
                ->route('otp-limits.index')
                ->with('success', __('Otp limit deleted successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->route('otp-limits.index')
                ->with('error', __('Failed to delete Otp limit. Please try again.'));
        }
    }

    public function resetDrivers(OtpLimit $otpLimit)
    {
        $drivers = User::where('role', 'driver')->get();
        foreach ($drivers as $driver) {
            $driver->otp_limit = $otpLimit->otp_limit;
            $driver->otp_used = 0;
            $driver->save();
        }
        return redirect()->route('otp-limits.index', $otpLimit)->with('success', __('Drivers OTP limits have been reset.'));
    }

    public function resetUsers(OtpLimit $otpLimit)
    {
        $users = User::where('role', 'user')->get();
        foreach ($users as $user) {
            $user->otp_limit = $otpLimit->otp_limit;
            $user->otp_used = 0;
            $user->save();
        }
        return redirect()->route('otp-limits.index', $otpLimit)->with('success', __('Users OTP limits have been reset.'));
    }
}
