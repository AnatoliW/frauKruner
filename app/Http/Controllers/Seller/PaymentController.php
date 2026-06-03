<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Order;
use App\Payment\PaymentProcess;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function payment(Payment $payment)
    {
        return view('auth.seller.pages.payment', compact('payment'));
    }
    public function paymentProcess(Payment $payment)
    {
        $res = (new PaymentProcess)->process($payment);
     
        return redirect()->away($res);
    }

    public function success(Request $request, Payment $payment)
    {
       
        $request->validate([
            'paymentId' => 'required'
        ]);
        try {
            $response = (new PaymentProcess)->paypal($request->paymentId, $payment);
            // return $response;

            // dd($response);
            $payment->status = 'PAID';
            $payment->payment_trnx_id = $request->paymentId;
            $payment->payment_method = 'paypal';
            $payment->save();

            $payment->payable->process();
            return redirect()->route('seller.products')->with('success', 'boosted erfolgreich hochgeladen');
        } catch (Exception $e) {
            return $e->getMessage();
            Log::info($e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        } catch (Error $e) {
            // return $e->getMessage();
            Log::info($e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    // private function paypalPayment($request)
    // {
    //     $endpoint = ['local' => 'https://api.sandbox.paypal.com/v2/checkout/orders/', 'production' => 'https://api-m.paypal.com/v2/checkout/orders/'];
    //     $token = Payouts::token();
    //     $paypal_status = Http::withToken($token)->get($endpoint[env('APP_ENV')] . $request->payment_id);
    //     $paypal_body = json_decode($paypal_status->body());
    //     if ($paypal_body->status == "COMPLETED") {
    //         return $paypal_body;
    //     } else {
    //         throw  new Exception('Anscheinend haben wir ein technisches Problem. Bitte versuchen Sie es später erneut');
    //     }
    // }


    // private function stripePayment($request, $order)
    // {
    //     $stripe = setting('site.secret_key');

    //     $amount = doubleval($order->total - $order->discount);
    //     $amount = Shop::round_num($amount);
    //     try {
    //         \Stripe\Stripe::setApiKey($stripe);
    //         $response = \Stripe\Charge::create([
    //             "amount" => $amount * 100,
    //             "currency" => 'EUR',
    //             "source" => $request->payment_id,
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

    public function prepayment(Order $order)
    {
        return view('auth.buyer.pages.pre_payment', compact('order'));
    }
    public function prepaymentProve(Request $request, Order $order)
    {
        $request->validate([
            'meta.payment_prove' => 'required',
        ]);
        if ($request->has('meta')) {
            $order->createMetas($request->meta);
            return redirect()->back()->with('success', 'Please wait admin respose');
        }
        return redirect()->back()->withErrors('something wrong');
    }
}
