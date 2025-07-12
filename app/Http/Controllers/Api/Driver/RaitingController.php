<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RaitingController extends Controller
{


    public function raiting(Request $request)
    {
        $driver = $request->user();

        $validation = Validator::make($request->all(), [
            'ratee_id' => 'required|exists:users,id',
            'comment'=> 'nullable|string',
            'rate' => 'required|numeric|min:1|max:5',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()->first()], 422);
        }

        $raiting = Rating::create([
            'rater_id' => $driver->id,
            'ratee_id' => $request->ratee_id,
            'ratee_type'=> 'user',
            'comment' => $request->comment ?? null ,
            'rate' => $request->rate
        ]);

        return response()->json([
            'message'=> 'Raiting created',
            'raiting'=> $raiting
        ]);
    }
}
