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
use App\Services\ProductStock;
use App\Services\Turnstile;
use App\Support\CouponSession;
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

    /**
     * Prüft den im Warenkorb hinterlegten Gutschein erneut (Ablauf, Limit,
     * Mindestbestellwert), bevor die Bestellung angelegt wird. Passt er nicht
     * mehr, bricht der Checkout mit einer Meldung ab: Der Kunde hat im
     * Warenkorb einen Gesamtbetrag gesehen, und der darf sich nicht
     * stillschweigend erhöhen.
     *
     * Gebucht wird die Einlösung erst beim Abschluss der Bestellung, siehe
     * processPayment().
     */
    private function verifyCouponOrFail()
    {
        // Dieselbe Prüfung wie beim Aufruf von Warenkorb und Kasse – hier aber
        // als harter Abbruch statt als Hinweis, weil ab dieser Stelle Beträge
        // festgeschrieben werden.
        if ($message = CouponSession::revalidate()) {
            throw ValidationException::withMessages([
                'discount_code' => [$message],
            ]);
        }
    }

    /**
     * Zielseite nach der Bestätigung. Auch ein zweiter Aufruf von
     * processPayment() landet hier – ohne erneute Mails, aber mit derselben
     * Danke-Seite, damit ein doppeltes Absenden für die Kundin unauffällig
     * bleibt.
     */
    private function confirmationRedirect(Order $order)
    {
        if ($order->payment_gateway == 'pre_payment') {
            return redirect()->route('pre.thankyou', $order)->with('thank', 'Vielen Dank! Ihre Zahlung wurde erfolgreich akzeptiert!');
        }

        return redirect('thankyou')->with('thank', 'Vielen Dank! Ihre Zahlung wurde erfolgreich akzeptiert!');
    }

    /**
     * Wert einer Warenkorbposition: Stückpreis mal Menge.
     *
     * Steht bewusst als eine Funktion da, weil sich Zwischensumme,
     * Positionsbetrag der Unterbestellung und die Gewichtung der
     * Rabattaufteilung sonst auseinanderentwickeln – genau daran ist die
     * Aufteilung vorher gescheitert.
     */
    private function lineTotal($item)
    {
        return $item->price * $item->quantity;
    }

    /**
     * Zieht einen Stückwert aus den Warenkorb-Attributen auf die Menge hoch.
     *
     * CartController::add() rechnet tax, commission und vendor_total pro Stück
     * aus. Alles, was in einer Bestellung landet, ist dagegen ein Positionswert.
     * Solange Cart::add() fest mit Menge 1 arbeitet, ändert das nichts – aber es
     * hält Haupt- und Unterbestellung an derselben Definition fest.
     */
    private function lineAttribute($item, string $key)
    {
        return ($item->attributes[$key] ?? 0) * $item->quantity;
    }

    /**
     * Verteilt den Gutscheinbetrag anteilig auf die Positionen.
     *
     * Ohne die Aufteilung trägt jede Unterbestellung den vollen Rabatt, und
     * Rechnung und Käuferübersicht ziehen ihn pro Position erneut ab. Die
     * Summe der Anteile entspricht exakt dem Rabatt der Hauptbestellung: Es
     * wird auf ganze Cent abgerundet und der Rest nach der Größe des
     * Rundungsverlusts verteilt.
     *
     * @return array<int|string, float> Anteil je Warenkorb-Position
     */
    private function splitDiscount($items, $discount)
    {
        $shares = [];

        foreach ($items as $key => $item) {
            $shares[$key] = 0.0;
        }

        $base = $items->sum(fn ($item) => $this->lineTotal($item));

        if ($discount <= 0 || $base <= 0) {
            return $shares;
        }

        $totalCents = (int) round($discount * 100);
        $remainders = [];
        $assigned = 0;

        foreach ($items as $key => $item) {
            $exact = $totalCents * $this->lineTotal($item) / $base;
            $cents = (int) floor($exact);

            $shares[$key] = $cents;
            $remainders[$key] = $exact - $cents;
            $assigned += $cents;
        }

        arsort($remainders);

        foreach (array_keys($remainders) as $key) {
            if ($assigned >= $totalCents) {
                break;
            }

            $shares[$key]++;
            $assigned++;
        }

        return array_map(fn ($cents) => (float) ($cents / 100), $shares);
    }

    private function processOrder($request, $payment_id = null)
    {
        $this->verifyCouponOrFail();

        // Einmal einlesen: getContent() lädt bei jedem Aufruf die Produkte neu,
        // und die Rabattaufteilung muss dieselben Positionen sehen wie die
        // Schleife weiter unten. Aus dem gleichen Grund werden Zwischen- und
        // Gesamtsumme hier gerechnet statt über \Cart::getSubTotal() bzw.
        // \Cart::getTotal() – die würden den Warenkorb erneut aus der Datenbank
        // laden und könnten dabei einen anderen Stand sehen.
        $items = \Cart::getContent();

        $subtotal = (float) $items->sum(fn ($item) => $this->lineTotal($item));
        $discount = CouponSession::discount();

        $figures = [
            'subtotal' => $subtotal,
            // Identisch zu \Cart::getTotal(): Der Rabatt ist im Gesamtbetrag
            // der Hauptbestellung bereits enthalten.
            'total' => max(0, $subtotal - $discount),
            'discount' => $discount,
            'discount_code' => CouponSession::code(),
            'discount_shares' => $this->splitDiscount($items, $discount),
            // Versand bewusst je Position und nicht je Stück: Mehrere Exemplare
            // desselben Artikels gehen in einem Paket raus.
            'shipping' => $items->sum(fn ($item) => $item->model->shipping_cost),
            'vendor_total' => $items->sum(fn ($item) => $this->lineAttribute($item, 'vendor_total')),
            'tax' => $items->sum(fn ($item) => $this->lineAttribute($item, 'tax')),
            'commission' => $items->sum(fn ($item) => $this->lineAttribute($item, 'commission')),
        ];

        // Haupt- und Unterbestellungen ergeben nur gemeinsam eine gültige
        // Bestellung. Bricht eine der Einfügungen ab, darf kein halber
        // Bestand zurückbleiben, auf den Order::redeemCoupon() später einen
        // Gutschein bucht.
        $parent_order = DB::transaction(
            fn () => $this->createOrders($request, $items, $figures, $payment_id)
        );

        \Cart::clear();
        CouponSession::forget();

        return $parent_order;
    }

    /**
     * Legt die Hauptbestellung und je eine Unterbestellung pro Warenkorbposition
     * an. Läuft immer innerhalb der Transaktion aus processOrder().
     *
     * @param  array  $figures  vorberechnete Beträge aus processOrder()
     */
    private function createOrders($request, $items, array $figures, $payment_id = null)
    {
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
            'total' => $figures['total'],
            'vendor_total' => $figures['vendor_total'],

            'user_id' => auth()->user() ? auth()->user()->id : null,

            'shipping_cost' => $figures['shipping'],
            'message' => $request->message,
            'tax' => $figures['tax'],
            'commission' => $figures['commission'],
            'subtotal' => $figures['subtotal'],
            'status' => 0,
            'payment_gateway' => $request->payment_type,
            'payment_id' => $payment_id,
            'discount_code' => $figures['discount_code'],
            'discount' => $figures['discount'],
        ]);
        foreach ($items as $itemKey => $item) {
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
                'total' => $this->lineTotal($item),
                'vendor_total' => $this->lineAttribute($item, 'vendor_total'),
                'wearing_time' => $Tragedauer,
                'finishings' => $veredelungen,
                'addition' => $Zusatzoptionen,
                'user_id' => auth()->user() ? auth()->user()->id : null,
                'vendor_id' => $item->model->user_id,
                'product_id' => $item->model->id,
                'shipping_cost' => $item->model->shipping_cost,
                'message' => $request->message,
                'tax' => $this->lineAttribute($item, 'tax'),
                'commission' => $this->lineAttribute($item, 'commission'),
                'subtotal' => $item->model->price * $item->quantity,
                'status' => 0,
                'payment_gateway' => $request->payment_type,
                'payment_id' => $payment_id,
                'discount_code' => $figures['discount_code'],
                // Anteiliger Rabatt: 'total' ist hier der Bruttopreis der
                // Position, Rechnung und Käuferübersicht rechnen total - discount.
                // Beide Werte sind mengenbezogen, damit die Rechnung auch bei
                // einer Menge über 1 aufgeht.
                'discount' => $figures['discount_shares'][$itemKey] ?? 0,
                'seller_info' => $seller_info,
                'product_name' => $item->model->name,
            ]);

            $this->logs($order);
        }
        $this->logs($parent_order);

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

            if ($peyment_gayway == 'pre_payment') {
                // Vorkasse reserviert nichts: Das Produkt bleibt bis zum Zahlungseingang
                // im Shop sichtbar und kaufbar. Abgebucht wird es erst, wenn die
                // Bestellung im Admin als bezahlt markiert wird (Order::markAsPaid()).
                // Die Zahlungsinformationen gehen per UserPrepaymentOrder raus.
                // Mail::to('k@fraukruner.de')->send(new AdminPrepaymentOrder($ord));
                continue;
            }

            ProductStock::bookSale($ord);

            Mail::to($ord->email)->send(new UserOrderEmail($ord));
            // Mail::to('k@fraukruner.de')->send(new OrderPlaced($ord));
            Mail::to($ord->vendor->email)->send(new VendorOrderEmail($ord));
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
        } else {
            $order->payment_gateway = 'pre_payment';
            $order->save();
        }

        // Ab hier ist die Zahlung geprüft und die Bestellung verbindlich. Alles
        // Weitere darf pro Bestellung genau einmal passieren: Für Stripe und
        // PayPal fängt `payment_id|unique` einen zweiten Durchlauf ab, bei
        // Vorkasse wird nichts validiert – ein erneut abgeschicktes Formular
        // würde Kundin und Verkäufer dieselben Mails noch einmal schicken.
        //
        // Der Anspruch wird bewusst erst hier gestellt und nicht am Anfang:
        // Eine abgelehnte Karte muss wiederholbar bleiben, sonst wäre die
        // Bestellung nach dem ersten Fehlversuch dauerhaft blockiert.
        if (! $order->claimConfirmation()) {
            return $this->confirmationRedirect($order);
        }

        $this->childrenOrder($order, $order->payment_gateway);

        // Der Gutschein ist mit dem Abschluss der Bestellung aufgebraucht – für
        // Vorkasse genauso wie für Stripe und PayPal. Maßgeblich ist die
        // verbindliche Bestellung, nicht der Zahlungseingang: Eine Vorkasse-
        // Bestellung, die nie bezahlt wird, hat den Gutschein trotzdem genutzt.
        $order->redeemCoupon();

        if ($order->payment_gateway == 'pre_payment') {
            Mail::to($order->email)->send(new UserPrepaymentOrder($order));
        }

        return $this->confirmationRedirect($order);
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

        // $order->total kommt aus \Cart::getTotal() und enthält den Rabatt bereits.
        $amount = doubleval($order->total);
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
       
        $user = $item->model->user;

        // firstFilled statt ??: ein leerer String aus der Datenbank darf die
        // Fallback-Kette auf die Verifizierungsadresse nicht abbrechen, sonst
        // wird z. B. eine leere PLZ dauerhaft in den Beleg geschrieben.
        $seller_info = [
            'f_name' => Order::firstFilled($user->first_name, $user->name, $user->verification?->name),
            'l_name' => Order::firstFilled($user->last_name, $user->address?->last_name, $user->verification?->last_name),
            'street' => Order::firstFilled($user->street, $user->address?->street, $user->verification?->street),
            'house_no' => Order::firstFilled($user->house_no, $user->address?->house_no, $user->verification?->house_no),
            'zip' => Order::firstFilled($user->zip, $user->address?->zip, $user->verification?->zip),
            'federal_state' => Order::firstFilled($user->federal_state, $user->address?->federal_state, $user->verification?->city),
            'email' => $user->email,
            'vat_number' => $user->vat,
            'is_pay_vat' => $user->is_pay_vat,
            'vat_perchatage' => setting('finance.vat'),
        ];

        return $seller_info;
    }
}
