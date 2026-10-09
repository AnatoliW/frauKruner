<?php

namespace App\Http\Controllers;

use App\Events\StatusProcessed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Rules\MatchOldPassword;
use App\Models\User;
use App\Order;
use App\Product;
use App\Mail\OrderPlaced;
use App\Mail\UserNotifyEmail;
use App\Models\User as ModelsUser;
use App\Services\ProductStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    public function dashboard()
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        return match ((int) $user->role_id) {
            1 => redirect('/admin'),
            2 => redirect()->route('buyer.dashboard'),
            3 => redirect()->route('seller.dashboard'),
            default => redirect()->route('home'),
        };
    }
    public function update(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'max:40'],
            'last_name' => ['required', 'max:40'],
            'address' => ['required', 'max:200'],
            'city' => ['required', 'max:50'],
            'post_code' => ['required', 'max:10'],
            'state' => ['required', 'max:20'],
        ]);
        User::where('id', auth()->id())->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'address' => $request->address,
            'city' => $request->city,
            'post' => $request->post_code,
        ]);
        return back()->with('success_msg', 'Profile updated successfully!');
    }
    function ChangePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);

        User::find(auth()->user()->id)->update(['password' => Hash::make($request->new_password)]);

        return back()->with('success_msg', 'Password changed successfully');
    }
    public function orders()
    {
        $orders = Order::where('user_id', auth()->id())->latest()->get();
        return view('auth.orders', compact('orders'));
    }
    /**
     * Beleg zu einer Bestellung.
     *
     * Dieselbe Adresse bedient zwei Belege, weil beide dieselbe Vorlage nutzen:
     *
     *  - Die Herstellerin sieht die **Gutschrift** für ihre Position. Sie gilt
     *    je Position, denn jede Herstellerin rechnet einzeln ab.
     *  - Der Käufer sieht die **Rechnung**, und deren Form hängt am Stichtag
     *    aus `app.invoice_bundle_cutoff_date` (Order::usesBundledInvoice()).
     *
     * Vor dem Stichtag gab es je Artikel eine eigene Rechnung, mit der
     * Belegnummer in der Kopfzeile (`Rechnungs-Nr. FK2024-3552`). Diese Blätter
     * sind ausgestellt und archiviert – sie müssen sich unverändert wieder
     * erzeugen lassen, deshalb wird dafür auf die Position aufgelöst.
     *
     * Ab dem Stichtag bekommt er **eine Rechnung für die ganze Bestellung**: Er
     * hat einmal bezahlt und bekommt einen Beleg. Sie trägt keine eigene Nummer,
     * sondern nennt die Bestellnummer als Bezug – die Nummer von seinem
     * Kontoauszug – und führt je Artikel dessen Belegnummer auf. Deshalb wird
     * dafür auf die Bestellung aufgelöst, auch wenn der Aufruf die ID einer
     * Position trägt; ältere Links tragen sie noch.
     *
     * Die Belegnummern selbst hängen nicht am Stichtag: Sie gelten immer je
     * Position (Order::invoiceNumber()), die Gutschrift der Herstellerin baut
     * darauf auf, und sie stehen fest in der Spalte `invoice_no`.
     *
     * Die Zugriffsprüfung: Die Adresse war einmal nur durch `auth` geschützt.
     * Wer angemeldet war, konnte über eine geratene Nummer Name, Adresse und
     * Bestellung fremder Kundinnen lesen – und die Nummern sind fortlaufend.
     */
    public function invoice(Order $order)
    {
        $user = auth()->user();

        if ((int) ($user->role_id ?? 0) === 3) {
            // Gutschrift: nur die Herstellerin der Position, nicht die der
            // Nachbarposition in derselben Bestellung.
            abort_unless((int) $order->vendor_id === (int) $user->id, 404);

            $order->load(['product', 'vendor.address', 'vendor.verification', 'products']);

            return view('auth.invoice', [
                'order' => $order,
                'products' => $order->products,
                'positions' => collect([$order]),
            ]);
        }

        $invoice = $order->mainOrder();

        // Der Adminbereich darf jeden Beleg sehen, die Kundin nur ihren eigenen.
        abort_unless(
            (int) ($user->role_id ?? 0) === 1 || (int) $invoice->user_id === (int) $user->id,
            404
        );

        // Vor dem Stichtag: je Artikel eine eigene Rechnung, mit der Belegnummer
        // in der Kopfzeile. Genau so wurde sie damals ausgestellt und archiviert.
        if (! $invoice->usesBundledInvoice()) {
            // Der Kopf einer Bestellung war damals kein Beleg. Weiter zur ersten
            // Position, die einen trägt.
            if (! $order->parent_id) {
                $erste = $order->childrens()->orderBy('id')->first();

                if ($erste) {
                    return redirect()->route('invoice', $erste);
                }
            }

            $order->load(['product', 'vendor.address', 'vendor.verification', 'products']);

            return view('auth.invoice', [
                'order' => $order,
                'products' => $order->products,
                'positions' => collect([$order]),
                'istSammelrechnung' => false,
            ]);
        }

        // Ab dem Stichtag: eine Rechnung für die Bestellung als Ganzes.
        $invoice->load([
            'childrens.product',
            'childrens.vendor.address',
            'childrens.vendor.verification',
            'product',
            'vendor.address',
            'vendor.verification',
            'products',
        ]);

        // Eine Bestellung ohne Positionen (Altdatensatz) ist ihre eigene einzige
        // Position; die Rechnung sieht dann aus wie bisher.
        $positions = $invoice->childrens->isNotEmpty() ? $invoice->childrens : collect([$invoice]);

        return view('auth.invoice', [
            'order' => $invoice,
            'products' => $invoice->products,
            'positions' => $positions,
            'istSammelrechnung' => true,
        ]);
    }
    public function printemail()
    {
        $order =  Order::find(34);
        return new OrderPlaced($order);
    }
    public function userDelete(Request $request)
    {
        $user = Auth()->user();
        session()->put('user', $user);
        $user->delete();
        return redirect()->route('seller.registration');
    }
    public function verifyEmail()
    {
        $user = Auth()->user();
        $user->update([
            'verifi_token' => request('token'),
            'email_verified_at' => now(),
        ]);

        $redirectRoute = (int) $user->role_id === 3
            ? 'seller.verification'
            : 'buyer.dashboard';

        return redirect()->route($redirectRoute)->with('success', 'Thank, you your email verification was successfull');
    }
    public function verifyMassage()
    {
        return view('verify_massage', ['user' => auth()->user()]);
    }
    public function userActive(User $user)
    {
        $user->update([
            'status' => true,
            'verified'=>true,
        ]);

        $verification = $user->verification;
        if ($verification) {
            $storageDisk = config('voyager.storage.disk', config('filesystems.default'));
            $imagePaths = [
                $verification->person_id_shot_img,
                $verification->id_card_front_img,
                $verification->id_card_back_img,
            ];

            foreach ($imagePaths as $imagePath) {
                if (empty($imagePath)) {
                    continue;
                }
                try {
                    $disk = Storage::disk($storageDisk);
                    if ($disk->exists($imagePath)) {
                        $disk->delete($imagePath);
                    }
                    if ($storageDisk !== config('filesystems.default')) {
                        $defaultDisk = Storage::disk(config('filesystems.default'));
                        if ($defaultDisk->exists($imagePath)) {
                            $defaultDisk->delete($imagePath);
                        }
                    }
                } catch (\Exception $e) {
                    report($e);
                }
            }

            $verification->update([
                'person_id_shot_img' => null,
                'id_card_front_img' => null,
                'id_card_back_img' => null,
            ]);
        }
        // event(new StatusProcessed($user));
        $mail_data = [
            'subject' => 'Dein Konto wurde erfolgreich verifiziert',
            'title' => 'Dein Konto wurde erfolgreich verifiziert',
            'body' => 'Du kannst nun deine Produkte einstellen oder Käufe tätigen.<br> Ich wünsche dir viel Spaß und tolle Erlebnisse.',
            'button_link' => route('seller.dashboard'),
            'button_text' => 'Login zum Profil',
        ];
        Mail::to($user->email)->send(new UserNotifyEmail($mail_data));
        return back()->with([
            'message'    => "Benutzer ist verifiziert",
            'alert-type' => 'success',
        ]);
    }
    public function userDeactive(User $user)
    {
        $user->update([
            'status' => false,
            'verified' => false,
        ]);
        $mail_data = [
            'subject' => 'Deine Verifizierung wurde abgelehnt',
            'title' => 'Deine Verifizierung wurde abgelehnt',
            'body' => 'Deine Verifizierung ist fehlgeschlagen.',
            'button_link' => route('home'),
            'button_text' => 'Home',
        ];
        Mail::to($user->email)->send(new UserNotifyEmail($mail_data));
        return redirect('admin/users')->with([
            'message'    => "Benutzer ist verifiziert",
            'alert-type' => 'success',
        ]);
    }
    public function orderCancel(Order $order)
    {
        $wasPaid = (int) $order->payment_status === 1;

        $order->update([
            'status' => 3,
        ]);

        // War das die letzte offene Position, gilt die ganze Bestellung als
        // storniert. Die Belege lesen den Stand ohnehin aus den Positionen,
        // hier bleiben zusätzlich die Daten schlüssig.
        $order->syncCancellation();

        // Nur zurückbuchen, wenn der Verkauf auch abgebucht war. Eine unbezahlte
        // Vorkasse-Bestellung hat nie etwas aus dem Shop genommen.
        if ($wasPaid) {
            ProductStock::releaseSale($order);
        }

        // Die Bestellnummer, die die Kundin kennt – nicht die ID dieser Position
        // und nicht das laufende Jahr: Beides stand vorher in der Storno-Mail und
        // passte bei einer älteren Bestellung mit mehreren Artikeln zu nichts.
        $bestellnummer = $order->orderNumber();

        // Storniert wird eine Position. Bei mehreren Artikeln muss die Kundin
        // erkennen, welcher davon betroffen ist.
        $artikel = $order->product_name ?? $order->product?->name;

        $mail_data = [
            'subject' => 'Storno Bestellung ' . $bestellnummer,
            'title' => 'Storno Bestellung ' . $bestellnummer,
            'body' => "Hey du,<br><br>leider musste ich "
                . (filled($artikel) ? 'den Artikel „' . e($artikel) . '“ aus deiner Bestellung ' . $bestellnummer : 'deinen Einkauf')
                . " stornieren. Für die Unannehmlichkeit entschuldige ich mich.<br><br>Weitere Informationen erhälst du per E-Mail.",
            'button_link' => route('shop'),
            'button_text' => 'ein anderes Produkt bestellen',
        ];
        $mail_data2 = [
            'subject' => 'Storno Bestellung ' . $bestellnummer,
            'title' => 'Storno Bestellung ' . $bestellnummer,
            'body' => "Hallo,<br><br> da du deiner Vertragspflicht als Produzentin nicht nachgekommen bist und auch auf Fristen nicht reagiert hast, habe ich deinen Verkauf storniert.<br><br> Dein Konto auf FrauKruner.de wurde gelöscht.<br><br>",
            'button_link' => '',
            'button_text' => '',
        ];
        Mail::to($order->email)->send(new UserNotifyEmail($mail_data));
        Mail::to($order->vendor->email)->send(new UserNotifyEmail($mail_data2));
        return redirect()->route('filament.admin.resources.orders.index')->with([
            'message'    => "Bestellung storniert",
            'alert-type' => 'success',
        ]);
    }
    public function isCommercial(User $user) {
        $user->update([
            'is_commercial'=>1,
        ]);
        return redirect()->back()->with([
            'message'    => "Gewerbe storniert",
            'alert-type' => 'success',
        ]);
    }
}
