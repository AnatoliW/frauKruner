<?php

namespace App\Payment;

use App\Services\Payouts;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class PaymentProcess
{


    public function paypal($trnxid, $payment = null)
    {
        $endpoint = ['local' => "https://api.sandbox.paypal.com/v1/payments/payment/{$trnxid}/execute", 'production' => "https://api-m.paypal.com/v1/payments/payment/{$trnxid}/execute",'development' => "https://api.sandbox.paypal.com/v1/payments/payment/{$trnxid}/execute"];
        $payerId = request('PayerID');
        $token = Payouts::token();
        $response = Http::withToken($token)
            ->post($endpoint[env('APP_ENV')], [
                'payer_id' => $payerId,
            ]);
        if ($response->successful()) {
            return json_decode($response->body());
        } else {
            throw  new Exception('Anscheinend haben wir ein technisches Problem. Bitte versuchen Sie es später erneut');
        }

        // $endpoint = ['local' => 'https://api.sandbox.paypal.com/v2/checkout/orders/', 'production' => 'https://api-m.paypal.com/v2/checkout/orders/'];
        // $token = Payouts::token();
        // $paypal_status = Http::withToken($token)->get($endpoint[env('APP_ENV')] . $trnxid);
        // $paypal_body = json_decode($paypal_status->body());
        //  dd($paypal_body);
        // if ($paypal_body->status == "COMPLETED") {
        //     return $paypal_body;
        // } else {
        //     throw  new Exception('Anscheinend haben wir ein technisches Problem. Bitte versuchen Sie es später erneut');
        // }
    }
    public function process($payment)
    {
        
        $details="Name :".$payment->payable->user->name. ",".
            "Email :" .$payment->payable->user->email .",".
            "Product :" .$payment->payable->boostable->name .','.
            'Package name :'.$payment->payable->package->name ;
   
     
        $clientId = setting('site.client_id');
        $clientSecret = setting('site.paypal_secret_id');
        $endpoint = ['local' => 'https://api.sandbox.paypal.com/v1/payments/payment', 'production' => 'https://api-m.paypal.com/v1/payments/payment','development' => 'https://api.sandbox.paypal.com/v1/payments/payment'];
       
        $response = Http::withBasicAuth($clientId, $clientSecret)
            ->post($endpoint[env('APP_ENV')], [
                'intent' => 'sale',
                'payer' => [
                    'payment_method' => 'paypal',
                ],
                'redirect_urls' => [
                    'return_url' => url('/seller/dashboard/payment/success/' . $payment->id),
                    'cancel_url' => url('/seller/dashboard/payment/' . $payment->id . '?error=1'),
                ],
                'transactions' => [
                    [
                        'amount' => [
                            'total' => $payment->amount,
                            'currency' => 'EUR',
                        ],
                        'description' => $details,
                    ],
                ],
            ]);
     

        // save the response body
   
        if ($payment) {
            $payment->update([
                'response_body' => json_encode($response)
            ]);
        }
        $approvalUrl = collect($response->json()['links'])
            ->firstWhere('rel', 'approval_url')['href'];

        return $approvalUrl;
    }

    // private function stripe($amount, $trnxid, $data = ['first_name' => '', 'last_name' => '', 'city' => '', 'zip' => '',])
    // {
    //     $stripe = setting('site.secret_key');

    //     $amount = Shop::round_num($amount);
    //     try {
    //         \Stripe\Stripe::setApiKey($stripe);
    //         $response = \Stripe\Charge::create([
    //             "amount" => $amount * 100,
    //             "currency" => 'EUR',
    //             "source" => $trnxid,
    //             "receipt_email" => $request->email,
    //             "description" => "fraukruner",
    //             "shipping" => [
    //                 "name" => $order->first_name . ' ' . $order->last_name,
    //                 "address" => [
    //                     "city" => $order->federal_state,
    //                     "postal_code" => $request->zip,
    //                     "country" => 'Germany',
    //                     "line1" => $order->street,
    //                     "line2" => '',
    //                     "state" => $order->federal_state,
    //                 ]
    //             ]
    //         ]);

    //         return $response;
    //     } catch (\Stripe\Exception\CardException $e) {
    //         throw ValidationException::withMessages([
    //             'card' => ['Eine ungültige Anfrage ist aufgetreten.'],
    //         ]);
    //     } catch (\Stripe\Exception\InvalidRequestException $e) {
    //         throw ValidationException::withMessages([
    //             'card' => ['Eine ungültige Anfrage ist aufgetreten.'],
    //         ]);
    //     } catch (Exception $e) {
    //         throw ValidationException::withMessages([
    //             'card' => ['Es ist ein weiteres Problem aufgetreten, das möglicherweise nichts mit Stripe zu tun hat.'],
    //         ]);
    //     }
    // }
}
