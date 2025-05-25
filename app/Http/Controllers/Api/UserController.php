<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try{
            $page = $request->input('page', 1);
            $sortField = $request->get('sort', 'id');
            $sortOrder = $request->get('order', 'ASC');
            $users = User::orderBy($sortField, $sortOrder)->
            paginate(20, ['*'], 'page', $page);

            if ($users->isEmpty()){
                return response()->json([
                    'status' => 'success',
                    'message' => 'No data to retrieve',
                    'users' => []
                ]);
            }

            return response()->json([
                'status' => 'success',
                'users' => UserResource::collection($users->items()),
                'meta' => [
                    'currentPage' => $users->currentPage(),
                    'lastPage' => $users->lastPage(),
                    'total' => $users->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'server error'
            ]);
        }
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());
        

        return response()->json([
            'status' => 'success',
            'user' => new UserResource($user),
        ]);
    }

    public function show(Request $request)
    {
        try {
            $id = $request->input('id');
            if (!$id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'not found'
                ]);
            }
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'not found'
                ]);
            }

            return response()->json([
                'status' => 'success',
                'user' => new UserResource($user)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error processing'
            ]);
        }
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());
        

        return response()->json([
            'status' => 'success',
            'user' => new UserResource($user),
        ]);
    }
}
