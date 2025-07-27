<?php

namespace App\Http\Controllers;

use App\Models\PaymenentMethod;


use Illuminate\Http\Request;
use App\Http\Requests\StorePaymenentMethodRequest;
use App\Http\Requests\UpdatePaymenentMethodRequest;
use App\Http\Controllers\Controller;

class PaymenentMethodController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $paymenentMethods = PaymenentMethod::orderBy($sortField, $sortOrder)->paginate(30);
        return view('paymenent-methods.index', compact('paymenentMethods', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        return view('paymenent-methods.create');
    }

    public function store(StorePaymenentMethodRequest $request)
    {
        PaymenentMethod::create($request->validated());
        return redirect()->route('paymenent-methods.index')->with('success',  __('Created successfully'));
    }

    public function show(PaymenentMethod $paymenentMethod)
    {
        return view('paymenent-methods.show', compact('paymenentMethod'));
    }

    public function edit(PaymenentMethod $paymenentMethod)
    {
        return view('paymenent-methods.edit', compact('paymenentMethod'));
    }

    public function update(UpdatePaymenentMethodRequest $request, PaymenentMethod $paymenentMethod)
    {
        $paymenentMethod->update($request->validated());
        return redirect()->route('paymenent-methods.index')->with('success', 'Updated successfully.');
    }
}
