<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;


use Illuminate\Http\Request;
use App\Http\Requests\StoreDocumentTypeRequest;
use App\Http\Requests\UpdateDocumentTypeRequest;
use App\Http\Controllers\Controller;

class DocumentTypeController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $documentTypes = DocumentType::orderBy($sortField, $sortOrder)->paginate(10);
        return view('document-types.index', compact('documentTypes', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        return view('document-types.create');
    }

    public function store(StoreDocumentTypeRequest $request)
    {
        DocumentType::create($request->validated());
        return redirect()->route('document-types.index')->with('success', 'Created successfully');
    }

    public function show(DocumentType $documentType)
    {
        return view('document-types.show', compact('documentType'));
    }

    public function edit(DocumentType $documentType)
    {
        return view('document-types.edit', compact('documentType'));
    }

    public function update(UpdateDocumentTypeRequest $request, DocumentType $documentType)
    {
        $documentType->update($request->validated());
        return redirect()->route('document-types.index')->with('success', 'Updated successfully.');
    }
}
