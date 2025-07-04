<?php

namespace App\Http\Controllers\Api\Paytabs;

use App\Http\Controllers\Controller;
use App\Models\SavedCard;
use App\Models\User;
use App\trait\PayTabsPaymentTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{

    use PayTabsPaymentTrait;

    public function storeTokenizedCard(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'token' => 'required|string',
            'transactionReference' => 'required|string',
            'paymentInfo' => 'required|array',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }

        $user = $request->user();
        $cardInfo = $request->input('paymentInfo');
        $lastFour = substr($cardInfo['paymentDescription'], -4);

        $card = SavedCard::updateOrCreate(
            [
                'user_id'    => $user->id,
                'card_token' => $request->token,
            ],
            [
                'last_four'             => $lastFour,
                'expiry_month'          => $cardInfo['expiryMonth'],
                'expiry_year'           => $cardInfo['expiryYear'],
                'transaction_reference' => $request->transactionReference,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Card saved successfully.',
            'card'    => $card,
        ]);
    }

    // public function chargeSavedCard(Request $request)
    // {
    //     $validation = Validator::make($request->all(), [
    //         'card_id' => 'required|exists:saved_cards,id',
    //         'amount'  => 'required|numeric|min:0.1',
    //         'description' => 'required|string',
    //     ]);

    //     if ($validation->fails()) {
    //         return response()->json($validation->errors(), 422);
    //     }

    //     $user = $request->user();
    //     $card = SavedCard::where('user_id', $user->id)->findOrFail($request->card_id);

    //     $tranRef = 'ride_' . now()->timestamp;

    //     $response = $this->chargeWithToken(
    //         $tranRef,
    //         $card->card_token,
    //         $request->amount,
    //         $request->description
    //     );

    //     return response()->json([
    //         'success'   => true,
    //         'reference' => $tranRef,
    //         'response'  => $response,
    //     ]);
    // }

    public function chargeSavedCard(Request $request)
{
    $token = $request->input('token');
    $amount = $request->input('amount');
    $user = $request->user();

    $paytabsApiKey =  config('paytabs.server_key');// from .env
    $profileId = config('paytabs.profile_id');

    $payload = [
        "profile_id" => $profileId,
        "tran_type" => "sale",
        "tran_class" => "ecom",
        "cart_id" => "order_" . time(),
        "cart_currency" => "EGP",
        "cart_amount" => $amount,
        "cart_description" => "App Payment",
        "token" => $token,
        "customer_details" => [
            "name" => $user->name,
            "email" => $user->email,
            "phone" => $user->phone,
            "street1" => "123 Main St",
            "city" => "Cairo",
            "state" => "Cairo",
            "country" => "EG",
            "zip" => "12345"
        ]
    ];

    $response = Http::withHeaders([
        'authorization' => $paytabsApiKey,
        'content-type' => 'application/json'
    ])->post("https://secure-egypt.paytabs.com/payment/request", $payload);

    return $response->json();
}
}
