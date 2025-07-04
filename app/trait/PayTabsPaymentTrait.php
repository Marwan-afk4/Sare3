<?php

namespace App\trait;

use Illuminate\Support\Facades\Http;

trait PayTabsPaymentTrait
{

//https://secure.paytabs.com/payment/request
    public function chargeWithToken($tranRef, $token, $amount, $description)
    {
        $profile_id = config('paytabs.profile_id');
        $server_key = config('paytabs.server_key');
        $currency   = 'EGP';

        $response = Http::withHeaders([
            'Authorization' => $server_key,          // ✅ Must be capital A
            'Content-Type'  => 'application/json',   // ✅ Proper casing
        ])->post('https://secure-egypt.paytabs.com/payment/page', [ // ✅ Correct endpoint
            'profile_id'        => $profile_id,
            'tran_type'         => 'sale',
            'tran_class'        => 'ecom',
            'cart_id'           => $tranRef,
            'cart_currency'     => $currency,
            'cart_amount'       => $amount,
            'cart_description'  => $description,
            // Optional
            // 'callback'       => route('paytabs.callback'),
            // 'return'         => route('paytabs.return'),
            'payment_token'     => $token,
        ]);

        if (! $response->successful()) {
            throw new \Exception('PayTabs error: ' . $response->body());
        }

        return $response->json();
    }

    


}
