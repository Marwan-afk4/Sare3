<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use App\Models\User;
use App\trait\ImageUpload;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    use ImageUpload;

    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $keyword = $request->get('keyword');

        $drivers = User::where('role', 'driver')
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('phone', 'LIKE', "%{$keyword}%");
                });
            })
        ->orderBy($sortField, $sortOrder)->paginate(30);
        return view('drivers.index', compact('drivers', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        return view('drivers.create');
    }

    public function store(StoreDriverRequest $request)
    {
        $request->setRole('driver');

        User::create($request->validated());
        return redirect()->route('drivers.index')->with('success', 'Created successfully');
    }

    public function show(User $driver)
    {

        return view('drivers.show', compact('driver'));
    }

    public function edit(User $driver)
    {
        return view('drivers.edit', compact('driver'));
    }

   

    public function update(UpdateDriverRequest $request, User $driver)
{
    try {
        // Step 1: Get validated input
        $data = $request->validated();

        // Step 2: Check if image file exists and process it
        if ($request->hasFile('image')) {
            $uploadResult = $this->uploadFromFile($request->file('image'), 'drivers');

            if ($uploadResult['status'] === 'success') {
                $data['image'] = $uploadResult['paths']['original'];
            } else {
                // Manual dump instead of redirect
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Image upload failed',
                    'error' => $uploadResult['message']
                ]);
            }
        }

        // Step 3: Update driver
        $driver->update($data);

        // Step 4: Return success as JSON (because session flash won't work)
        return response()->json([
            'status' => 'success',
            'message' => 'Driver updated',
            'updated_data' => $data,
        ]);

    } catch (\Exception $e) {
        // Catch any unexpected error and show it
        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong',
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}


}
