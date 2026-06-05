<?php

namespace App\Services;

use App\Support\Settings;
use Illuminate\Support\Facades\Http;

class Payouts
{

    public static function token()
    {
        $client_id = Settings::paypalClientId();
        $secret_id = Settings::paypalSecretId();
        $endpoint = ['local' => 'https://api.sandbox.paypal.com/v1/oauth2/token', 'production' => 'https://api-m.paypal.com/v1/oauth2/token','development' => "https://api.sandbox.paypal.com/v1/oauth2/token"];
        $res = Http::withBasicAuth($client_id, $secret_id)
            ->asForm()
            ->post($endpoint[env('APP_ENV')], ['grant_type' => 'client_credentials']);
        return (json_decode($res->body())->access_token);
    }

    public static function validateTurnstile($token)
    {
       
        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret_key'),
            'response' => $token,
        ]);

        return $response->json()['success'];
    }
}
