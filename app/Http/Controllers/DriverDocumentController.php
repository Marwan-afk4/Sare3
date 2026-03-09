<?php

namespace App\Http\Controllers;

use App\Models\DriverDocument;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use App\Http\Requests\StoreDriverDocumentRequest;
use App\Http\Requests\UpdateDriverDocumentRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\trait\ImageUpload;

class DriverDocumentController extends Controller
{
    use ImageUpload;

    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $driverDocuments = DriverDocument::with(['driver', 'documentType'])->orderBy($sortField, $sortOrder)->paginate(30);
        return view('driver-documents.index', compact('driverDocuments', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        $drivers = User::where('role', 'driver')->orderBy('name')->pluck('name', 'id')->toArray();
        $documentTypes = DocumentType::orderBy('name')->pluck('name', 'id')->toArray();
        return view('driver-documents.create', compact('drivers', 'documentTypes'));
    }

    public function store(StoreDriverDocumentRequest $request)
    {
        $data = $request->validated();
        unset($data['document_file']);

        if ($request->hasFile('document_file')) {
            $data['image_path'] = $this->uploadFile($request->file('document_file'), 'driver/documents');
        }

        DriverDocument::create($data);
        return redirect()->route('driver-documents.index')->with('success',  __('Created successfully'));
    }

    public function show(DriverDocument $driverDocument)
    {
        return view('driver-documents.show', compact('driverDocument'));
    }

    public function edit(DriverDocument $driverDocument)
    {
        $drivers = User::where('role', 'driver')->orderBy('name')->pluck('name', 'id')->toArray();
        $documentTypes = DocumentType::orderBy('name')->pluck('name', 'id')->toArray();
        return view('driver-documents.edit', compact('driverDocument', 'drivers', 'documentTypes'));
    }

    public function update(UpdateDriverDocumentRequest $request, DriverDocument $driverDocument)
    {
        $data = $request->validated();
        unset($data['document_file']);

        if ($request->hasFile('document_file')) {
            if ($driverDocument->image_path) {
                $this->deleteImage($driverDocument->image_path);
            }
            $data['image_path'] = $this->uploadFile($request->file('document_file'), 'driver/documents');
        }

        $driverDocument->update($data);
        return redirect()->route('driver-documents.index')->with('success',  __('Updated successfully.'));
    }
}
