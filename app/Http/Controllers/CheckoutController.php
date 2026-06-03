<?php

namespace App\Http\Controllers;

use App\Mail\AdminPrepaymentOrder;
use App\Mail\OrderPlaced;
use App\Mail\UserOrderEmail;
use App\Mail\UserPrepaymentOrder;
use App\Mail\VendorOrderEmail;
use App\Models\Address;
use App\Models\Log;
use App\Order;
use App\Product;
use App\Services\Payouts;
use App\Services\Turnstile;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string',
            'additional' => 'nullable|string',
            'street' => 'required|string',
            'house_no' => 'required|string',
            'zip' => 'required|string',
            'federal_state' => 'required|string',
            // 'po_box' => 'required|string',
            'message' => 'nullable|max:200',
            'datenschutz' => 'required',
            // 'cf-turnstile-response' => env('CAPTCHA') == true ? 'required' : 'nullable',
        ]);
        if (env('CAPTCHA') == true) {
            $token = $request->input('cf-turnstile-response');
            $response = Turnstile::validateTurnstile($token);
            if (!$response->success) {
                return back()->withErrors('Please complete the CAPTCHA verification.');
            }
        }

        if (Auth::check()) {
            Address::where('user_id', Auth::id())->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'additional' => $request->additional,
                'street' => $request->street,
                'house_no' => $request->house_no,
                'zip' => $request->zip,
                'federal_state' => $request->federal_state,
                'po_box' => $request->po_box,
            ]);
        }

        // try {
        $order = $this->processOrder($request);

        // return redirect('payment')->with('thank', 'Vielen Dank! Ihre Zahlung wurde erfolgreich akzeptiert!');
        return redirect()->route('payment', $order);
        // } catch (Exception $e) {
        //     return back()->withErrors('Anscheinend haben wir ein technisches Problem. Bitte versuchen Sie es später erneut');
        // } catch (Error $e) {
        //     return back()->withErrors('Anscheinend haben wir ein technisches Problem. Bitte versuchen Sie es später erneut');
        // }
        // if ($request->payment_type == 'paypal') {
        //     $paypal_body = $this->paypalPayment($request);

        //     if ($paypal_body) {
        //         $this->processOrder($request, $paypal_body->id);
        //     } else {
        //         return back()->withErrors('There is a problem with your payment gateway');
        //     }
        // } else {
        //     $stripe_response = $this->stripePayment($request);
        //     if ($stripe_response) {
        //         $this->processOrder($request, $stripe_response->id);
        //     } else {
        //         return back()->withInput();
        //     }
        // }
        // return redirect('thankyou')->with('thank', 'Vielen Dank! Ihre Zahlung wurde erfolgreich akzeptiert!');
    }

    private function processOrder($request, $payment_id = null)
    {
        $shipping = \Cart::getContent()->sum(function ($item) {
            return $item->model->shipping_cost;
        });

        $parent_order = Order::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'additional' => $request->additional,
            'street' => $request->street,
            'house_no' => $request->house_no,
            'zip' => $request->zip,
            'email' => $request->email,
            'federal_state' => $request->federal_state,
            'po_box' => $request->po_box,
            'email' => $request->email,
            'total' => \Cart::getTotal(),
            'vendor_total' => \Cart::getContent()->pluck('attributes')->sum('vendor_total'),

            'user_id' => auth()->user() ? auth()->user()->id : null,

            'shipping_cost' => $shipping,
            'message' => $request->message,
            'tax' => \Cart::getContent()->pluck('attributes')->sum('tax'),
            'commission' => \Cart::getContent()->pluck('attributes')->sum('commission'),
            'subtotal' => \Cart::getSubTotal(),
            'status' => 0,
            'payment_gateway' => $request->payment_type,
            'payment_id' => $payment_id,
            'discount_code' => session()->get('discount_code'),
            'discount' => session()->get('discount'),
        ]);
        foreach (\Cart::getContent() as $item) {
            $seller_info = $this->sellerInfo($item);

            if (isset($item->attributes['Tragedauer'])) {
                $Tragedauer = json_encode($item->attributes['Tragedauer']); // wearing time
            } else {
                $Tragedauer = null;
            }
            if (isset($item->attributes['veredelungen'])) {
                $veredelungen = json_encode($item->attributes['veredelungen']); // finishings
            } else {
                $veredelungen = null;
            }
            if (isset($item->attributes['Zusatzoptionen'])) {
                $Zusatzoptionen = json_encode($item->attributes['Zusatzoptionen']); // aditional options
            } else {
                $Zusatzoptionen = null;
            }
            $order = Order::create([
                'parent_id' => $parent_order->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'additional' => $request->additional,
                'street' => $request->street,
                'house_no' => $request->house_no,
                'zip' => $request->zip,
                'email' => $request->email,
                'federal_state' => $request->federal_state,
                'po_box' => $request->po_box,
                'email' => $request->email,
                'total' => $item->price,
                'vendor_total' => $item->attributes['vendor_total'],
                'wearing_time' => $Tragedauer,
                'finishings' => $veredelungen,
                'addition' => $Zusatzoptionen,
                'user_id' => auth()->user() ? auth()->user()->id : null,
                'vendor_id' => $item->model->user_id,
                'product_id' => $item->model->id,
                'shipping_cost' => $item->model->shipping_cost,
                'message' => $request->message,
                'tax' => $item->attributes['tax'],
                'commission' => $item->attributes['commission'],
                'subtotal' => $item->model->price,
                'status' => 0,
                'payment_gateway' => $request->payment_type,
                'payment_id' => $payment_id,
                'discount_code' => session()->get('discount_code'),
                'discount' => session()->get('discount'),
                'seller_info' => $seller_info,
                'product_name' => $item->model->name,
            ]);

            $order->product->increment('sale_count');
            $this->logs($order);
        }
        $this->logs($parent_order);

        // DB::commit();

        \Cart::clear();
        session()->forget('discount');
        session()->forget('discount_code');

        return $parent_order;
    }

    public function childrenOrder(Order $order, $peyment_gayway)
    {
        foreach ($order->childrens as $ord) {
            $ord->status = $order->status;
            $ord->payment_status = $order->payment_status;
            $ord->payment_id = request()->payment_id;
            $ord->payment_gateway = $peyment_gayway;

            $ord->save();
            $product = $ord->product;
            $product->increment('sale_count');
            $product->update(['quantity' => $product->quantity - 1]);

            if ($peyment_gayway == 'pre_payment') {
                // Mail::to($ord->email)->send(new UserPrepaymentOrder($ord));
            // Mail::to('k@fraukruner.de')->send(new AdminPrepaymentOrder($ord));
            } else {
                Mail::to($ord->email)->send(new UserOrderEmail($ord));
                // Mail::to('k@fraukruner.de')->send(new OrderPlaced($ord));
                Mail::to($ord->vendor->email)->send(new VendorOrderEmail($ord));
            }

            if ($ord->product->selloption == false) {
                $ord->product->update([
                    'status' => 0,
                ]);
            }
        }
    }

    public function processPayment(Request $request)
    {
        if ($request->payment_type !== 'pre_payment') {
            $request->validate([
                'payment_id' => 'required|unique:orders,payment_id',
            ]);
        }

        // try {
        sleep(2);
        $order = Order::find($request->order_id);
        if ($request->payment_type == 'paypal') {
            $paypal_body = $this->paypalPayment($request);

            Log::create([
                'details' => json_encode($paypal_body),
                'email' => $order->email,
                'admin_id' => $order->vendor_id,
                'user_id' => auth()->id(),
            ]);

            if ($paypal_body->status == 'COMPLETED') {
                $order->status = 1;
                $order->payment_status = 1;
                $order->payment_id = $request->payment_id;
                $order->payment_gateway = 'paypal';
                $order->save();
                $this->childrenOrder($order, 'paypal');
            } else {
                return redirect()->route('payment', $order)->withErrors('Anscheinend haben wir ein technisches Problem. Bitte versuchen Sie es später erneut');
            }
        } elseif ($request->payment_type == 'stripe') {
            $stripe_response = $this->stripePayment($request, $order);

            $order->status = 1;
            $order->payment_status = 1;
            $order->payment_id = $request->payment_id;
            $order->payment_gateway = 'stripe';
            $order->save();
            // dd($order->payment_gateway);
            $this->childrenOrder($order, 'stripe');
        } else {
            $order->payment_gateway = 'pre_payment';
            $order->save();
            $this->childrenOrder($order, 'pre_payment');
        }

        $this->decreaseQuantities();
        if ($order->payment_gateway == 'pre_payment') {
            Mail::to($order->email)->send(new UserPrepaymentOrder($order));
            return redirect()->route('pre.thankyou', $order)->with('thank', 'Vielen Dank! Ihre Zahlung wurde erfolgreich akzeptiert!');
        } else {
            return redirect('thankyou')->with('thank', 'Vielen Dank! Ihre Zahlung wurde erfolgreich akzeptiert!');
        }
        // } catch (Exception $e) {
        //     return back()->withErrors('Anscheinend haben wir ein technisches Problem. Bitte versuchen Sie es später erneut');
        // } catch (Error $e) {
        //     return back()->withErrors('Anscheinend haben wir ein technisches Problem. Bitte versuchen Sie es später erneut');
        // }
    }

    private function paypalPayment($request)
    {
        $endpoint = ['local' => 'https://api.sandbox.paypal.com/v2/checkout/orders/', 'production' => 'https://api-m.paypal.com/v2/checkout/orders/'];
        $token = Payouts::token();
        $paypal_status = Http::withToken($token)->get($endpoint[env('APP_ENV')].$request->payment_id);
        $paypal_body = json_decode($paypal_status->body());

        return $paypal_body;
    }

    protected function decreaseQuantities()
    {
        foreach (\Cart::getContent() as $item) {
            $product = Product::find($item->model->id);
            $product->increment('sale_count');
            $product->update(['quantity' => $product->quantity - $item->quantity]);
        }
    }

    protected function productsAreNoLongerAvailable()
    {
        foreach (\Cart::getContent() as $item) {
            $product = Product::find($item->model->id);
            if ($product->quantity < $item->quantity) {
                return true;
            }
        }

        return false;
    }

    public function payment(Order $order)
    {
        return view('payment', compact('order'));
    }

    private function stripePayment($request, $order)
    {
        $stripe = setting('site.secret_key');

        $amount = doubleval($order->total - $order->discount);
        $amount = \Shop::round_num($amount);
        try {
            \Stripe\Stripe::setApiKey($stripe);
            $response = \Stripe\Charge::create([
                'amount' => $amount * 100,
                'currency' => 'EUR',
                'source' => $request->payment_id,
                'receipt_email' => $request->email,
                'description' => 'fraukruner',
                'shipping' => [
                    'name' => $order->first_name.' '.$order->last_name,
                    'address' => [
                        'city' => $order->federal_state,
                        'postal_code' => $request->zip,
                        'country' => 'Germany',
                        'line1' => $order->street,
                        'line2' => '',
                        'state' => $order->federal_state,
                    ],
                ],
            ]);

            return $response;
        } catch (\Stripe\Exception\CardException $e) {
            throw ValidationException::withMessages(['card' => ['Eine ungültige Anfrage ist aufgetreten.']]);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            throw ValidationException::withMessages(['card' => ['Eine ungültige Anfrage ist aufgetreten.']]);
        } catch (\Exception $e) {
            throw ValidationException::withMessages(['card' => ['Es ist ein weiteres Problem aufgetreten, das möglicherweise nichts mit Stripe zu tun hat.']]);
        }
    }

    protected function logs($data)
    {
        Log::create([
            'details' => json_encode($data),
            'email' => $data->email,
            'admin_id' => $data->vendor_id,
            'user_id' => $data->user_id,
        ]);
    }

    protected function sellerInfo($item)
    {
       
        $seller_info = [
            'f_name' => $item->model->user->first_name ?? $item->model->user->name ?? $item->model->user->verification->name,
            'l_name' => $item->model->user->last_name ?? $item->model->user->address->last_name,
            'street' => $item->model->user->street ?? $item->model->user->address->street ?? null,
            'house_no' => $item->model->user->house_no ?? $item->model->user->address->house_no ?? null,
            'zip' => $item->model->user->zip ?? $item->model->user->address->zip ?? null,
            'federal_state' => $item->model->user->federal_state ?? $item->model->user->address->federal_state ?? null,
            'email' => $item->model->user->email,
            'vat_number' => $item->model->user->vat,
            'is_pay_vat' => $item->model->user->is_pay_vat,
            'vat_perchatage' => setting('finance.vat'),
        ];

        return $seller_info;
    }
}
