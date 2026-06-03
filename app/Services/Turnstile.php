<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Turnstile
{

    public static function validateTurnstile($token)
    {
       
        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret_key'),
            'response' => $token,
        ]);

        return json_decode($response->body());
    }
}
