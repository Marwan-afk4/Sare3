<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{


    public function getTransactions(Request $request)
    {
        $user_id = $request->user()->id;
        $transactions = Transaction::where('user_id', $user_id)
            ->with(['user.zone', 'driver.zone'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'transactions' => $transactions,
        ]);
    }
}
