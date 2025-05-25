<?php

namespace App\trait;

use Illuminate\Support\Facades\Log;
use Twilio\Http\CurlClient;
use Twilio\Rest\Client;

trait twilio
{

    protected function twilioClient()
    {
// 34an bs el https lakn el production haib2a mn8ir di
    $curlClient = new CurlClient([
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ]);

    return new Client(
        config('services.twilio.sid'),
        config('services.twilio.auth_token'),
        config('services.twilio.sid'),
        null,
        $curlClient
    );
    }

//========== haib2a kda ===========
// protected function twilioClient()
//     {
//         return new Client(
//             config('services.twilio.sid'),
//             config('services.twilio.auth_token')
//         );
//     }

    public function sendOtp(string $phoneNumber)
{
    Log::info('Sending OTP to '.$phoneNumber);
    $client = $this->twilioClient();
    Log::info('Twilio client created');
    $result = $client->verify->v2->services(config('services.twilio.verify_sid'))->verifications->create($phoneNumber, 'sms');
    Log::info('OTP sent', ['result' => $result]);
    return $result;
}


    public function verifyOtp(string $phoneNumber, string $code)
    {
        $client = $this->twilioClient();
        return $client->verify->v2->services(config('services.twilio.verify_sid'))->verificationChecks->create([
            'to' => $phoneNumber,
            'code' => $code
        ]);
    }


}
