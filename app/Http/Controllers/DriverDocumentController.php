<?php

namespace App\Http\Controllers;

use App\Models\DriverDocument;
use App\Models\Driver;


use Illuminate\Http\Request;
use App\Http\Requests\StoreDriverDocumentRequest;
use App\Http\Requests\UpdateDriverDocumentRequest;
use App\Http\Controllers\Controller;
use App\Models\User;

class DriverDocumentController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $driverDocuments = DriverDocument::with(['driver'])->orderBy($sortField, $sortOrder)->paginate(30);
        return view('driver-documents.index', compact('driverDocuments', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        $drivers = User::orderBy('name')->pluck('name', 'id')->toArray();
        return view('driver-documents.create', compact('drivers'));
    }

    public function store(StoreDriverDocumentRequest $request)
    {
        DriverDocument::create($request->validated());
        return redirect()->route('driver-documents.index')->with('success', 'Created successfully');
    }

    public function show(DriverDocument $driverDocument)
    {
        return view('driver-documents.show', compact('driverDocument'));
    }

    public function edit(DriverDocument $driverDocument)
    {
        $drivers = User::orderBy('name')->pluck('name', 'id')->toArray();
        return view('driver-documents.edit', compact('driverDocument', 'drivers'));
    }

    public function update(UpdateDriverDocumentRequest $request, DriverDocument $driverDocument)
    {
        $driverDocument->update($request->validated());
        return redirect()->route('driver-documents.index')->with('success', 'Updated successfully.');
    }
}
