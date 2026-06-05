<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Notification;
use Illuminate\Http\Request;
use App\Order;
use App\Package;
use App\Payment\PaymentProcess;
use App\Product;
use App\Services\Payouts;
use Carbon\Carbon;
use Error;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class PayoutsController extends Controller
{
    public function payouts(Order $order,$page=null)
    {
    
        // if (!isset($order->vendor->address->paypal_email)) {
        //     return back()->with([
        //         'message'    => "Vendor does not have paypal email",
        //         'alert-type' => 'success',
        //     ]);
        // }
        // $total_format = Shop::round_num($order->vendor_total);
        // $token = Payouts::token();
   
        // $body =  [
        //     "sender_batch_header" => [
        //         "sender_batch_id" => "Payout_$order->id",
        //         "recipient_type" => "EMAIL",
        //         "email_subject" => "Du hast eine neue bestellung: $order->id!",
        //         "email_message" => "Du hast eine Zahlung erhalten. Vielen Dank für das Nutzen unserer Plattform."
        //     ],
        //     "items" => [
        //         [
        //             "recipient_type" => "EMAIL",
        //             "amount" => [
        //                 "value" => "$total_format",
        //                 "currency" => "EUR"
        //             ],
        //             "sender_item_id" => "$order->id",
        //             "recipient_wallet" => "PAYPAL",
        //             "receiver" => $order->vendor->address->paypal_email
                    
        //         ]
        //     ]
        // ];
        // try {
        //     $response = Http::withToken($token)
        //         ->withHeaders(['Content-Type' => 'application/json'])
        //         ->withBody(json_encode($body), 'application/json')
        //         ->post('https://api-m.paypal.com/v1/payments/payouts');

        //     if (json_decode($response->status()) == 200 || json_decode($response->status()) == 201) {
        //         $order->update(['payouts_status' => 1]);
        //         Notification::Create([
        //             'price' => $total_format,
        //             'user_id' => $order->vendor_id,
        //             'title' => 'Auszahlungen',
        //             'description' => 'Auszahlung erfolgreich. Bitte überprüfe in 1 - 2 Werktagen dein Konto.',
        //         ]);
        //     } else {
        //         return back()->with([
        //             'message'    => $response->object()->message,
        //             'alert-type' => 'error',
        //         ]);
        //     }
        // } catch (Exception $e) {
        //     return $e->getMessage();
        // }

        $total_format = number_format($order->vendor_total, 2, '.', '');
        $order->update(['payouts_status' => 1]);
        // Notification::Create([
        //     'price' => $total_format,
        //     'user_id' => $order->vendor_id,
        //     'title' => 'Auszahlungen',
        //     'description' => 'Auszahlung erfolgreich. Bitte überprüfe in 1 - 2 Werktagen dein Konto.',
        // ]);
        return redirect()->route('filament.admin.resources.payouts.index')->with([
            'message'    => "Auszahlung erfolgreich!",
            'alert-type' => 'success',
        ]);
    }

    public function payment(Payment $payment)
    {
        if ($payment->status == 'PAID') {
            $payment->payable->process();
            if ($payment->payable->boostable_type == 'App\Models\User') {
                return redirect()->route('filament.admin.pages.dashboard')->with([
                    'message'    => "User is now pushed",
                    'alert-type' => 'success',
                ]);
            } else {
                return redirect()->route('filament.admin.pages.dashboard')->with([
                    'message'    => "Product is now pushed",
                    'alert-type' => 'success',
                ]);
            }
        }
        return view('admin.payment', compact('payment'));
    }


    public function paymentProcess(Request $request, Payment $payment)
    {
        
        if ($request->input('method') !== 'free') {
            $request->validate([
                'payment_id' => 'required|unique:orders,payment_id'
            ]);
        }
        try {
           
            if ($request->input('method') === 'free') {
                $payment->status = 'PAID';
                $payment->payment_trnx_id = 'Free';
                $payment->payment_method = 'Free';
                $payment->save();

                $payment->payable->process();
                if ($payment->payable->boostable_type == 'App\Models\User') {
                    return redirect()->route('filament.admin.pages.dashboard')->with([
                        'message'    => "User is now pushed",
                        'alert-type' => 'success',
                    ]);
                } else {
                    return redirect()->route('filament.admin.pages.dashboard')->with([
                        'message'    => "Product is now pushed",
                        'alert-type' => 'success',
                    ]);
                }
            } else {
                (new PaymentProcess)->paypal($request->payment_id);

                $payment->status = 'PAID';
                $payment->payment_trnx_id = $request->payment_id;
                $payment->payment_method = 'paypal';
                $payment->save();

                $payment->payable->process();
                if ($payment->payable->boostable_type == 'App\Models\User') {
                    return redirect()->route('filament.admin.pages.dashboard')->with([
                        'message'    => "User is now pushed",
                        'alert-type' => 'success',
                    ]);
                } else {
                    return redirect()->route('filament.admin.pages.dashboard')->with([
                        'message'    => "Product is now pushed",
                        'alert-type' => 'success',
                    ]);
                }
            }
        } catch (Exception $e) {
            return redirect()->back()->with([
                'message'    => $e->getMessage(),
                'alert-type' => 'error',
            ]);
        } catch (Error $e) {
            return redirect()->back()->with([
                'message'    => $e->getMessage(),
                'alert-type' => 'error',
            ]);
        }
    }
    public function boost($type, $id)
    {
        switch ($type) {
            case 'Product':
                $packages = Package::where('type', 'Product')->get();
                $model = Product::find($id);
                break;

            default:
                $packages = Package::where('type', 'Profile')->get();
                $model = User::find($id);

                break;
        }
        if ($model->boosted) {
            if ($model instanceof User) {
                return redirect()->route('filament.admin.pages.dashboard')->with([
                    'message'    => "User is already pushed",
                    'alert-type' => 'success',
                ]);
            } else {
                return redirect()->route('admin.boosts.list')->with([
                    'message'    => "Product is already pushed",
                    'alert-type' => 'success',
                ]);
            }
        }
        return view('admin.boost', compact('packages', 'model', 'type'));
    }

    public function boostStore($type, $id, Request $request)
    {
        $request->validate([
            'package' => 'required'
        ]);
        $package = Package::find($request->package);
        switch ($type) {
            case 'Product':
                $model = Product::find($id);
                break;
            default:
                $model = User::find($id);
                break;
        }
        $boost = $model->boosts()->create([
            'package_id' => $package->id,
            'user_id' => Auth::id(),
            'price' => $package->price,
            'base_price' => $package->price,
            'start_day' => Carbon::now(),
            'end_day' => Carbon::now()->addDays($package->days),
        ]);

        $payment = $boost->charge($package->price);
        return redirect()->route('admin.payment', $payment);
    }

}
