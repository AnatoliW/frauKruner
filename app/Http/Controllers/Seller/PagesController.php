<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Mail\UserNotifyEmail;
use App\Models\Address;
use App\Models\Boost;
use App\Models\Orderimage;
use App\Models\Profile;
use App\Notification;
use App\Order;
use App\Services\ExifMetadataService;
use App\Services\StripeVideoMetaData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PagesController extends Controller
{
    public function dashboard()
    {
        return view('auth.seller.pages.dashboard');
    }

    public function sales()
    {
        $orders = Order::where('vendor_id', Auth()->id())->paid()->latest()->get();

        return view('auth.seller.pages.sales', compact('orders'));
    }

    public function products()
    {
        return view('auth.seller.pages.products');
    }

    public function news()
    {
        $notifications = Notification::forMe()->latest()->get();
        $unseenNotifications = Notification::forMe()->where('seen', 0)->get();

        Notification::forMe()
            ->latest()
            ->get();
        foreach ($unseenNotifications as $item) {
            $item->update([
                'seen' => 1,
            ]);
        }

        return view('auth.seller.pages.news', compact('notifications'));
    }

    public function payments()
    {
        $payments = Order::where('vendor_id', Auth()->id())->where('status', 1)->latest()->get();

        return view('auth.seller.pages.payments', compact('payments'));
    }

    public function review()
    {
        $reviews = Auth()->user()->ratings;

        return view('auth.seller.pages.reviews', compact('reviews'));
    }

    public function userData()
    {
        $profile = Profile::where('user_id', auth()->id())->first() ?? new Profile();

        return view('auth.seller.pages.user_data', compact('profile'));
    }

    public function address()
    {
        $address = Address::where('user_id', auth()->id())->first() ?? new Address();

        return view('auth.seller.pages.address', compact('address'));
    }

    public function orderUpdate(Order $order, Request $request)
    {
        $request->validate([
            'shipping_method' => 'required|string',
            'tracking_Id' => 'nullable',
            'shipping_date' => 'required|date',
        ]);
        $order->update([
            'shipping_method' => $request->shipping_method,
            'tracking_Id' => $request->tracking_Id,
            'shipping_date' => $request->shipping_date,
        ]);

        // Build the initial part of your mail body
        $body = 'deine Bestellung mit der Bestellnummer ' . $order->id . ' wurde/wird am '
            . $order->shipping_date->format('d.m.Y')
            . ' versendet.';

        // Conditionally add shipping method
        if (!empty($request->shipping_method)) {
            $body .= '<br> Versandmethode: ' . $request->shipping_method;
        }

        // Conditionally add tracking ID
        if (!empty($request->tracking_Id)) {
            $body .= '<br> Tracking-ID: ' . $request->tracking_Id;
        }

        // Continue with the rest of your body text
        $body .= '<br><br>Wir wünschen dir viel Spaß und tolle Erlebnisse. <br><br>
                  Vergiss anschließend nicht, dein Einkaufserlebnis zu bewerten.<br><br>
                  Bei Fragen oder Problemen kontaktiere den Support unter<br>
                  <a href="tel:03096607799">030 96607799</a>.<br><br>';

        $mail_data = [
            'subject' => 'Versandbestätigung ' . $order->id,
            'title' => 'Hi ' . $order->first_name . ',',
            'body' => $body,
            'button_link' => route('shop'),
            'button_text' => 'Weiter im Shop stöbern',
        ];
        Mail::to($order->email)->send(new UserNotifyEmail($mail_data));

        return back()->with('success', 'Tracking erfolgreich erstellen');
    }

    public function payoutsRequest(Order $order)
    {
        $order->update([
            'payouts_rerquest' => 1,
        ]);

        return back()->with('success', 'Auszahlung erfolgreich angefragt');
    }

    public function photoUpload(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
        ]);

        // $order=Order::where('id',$request->order_id)->first();
        if ($request->upload_photo) {
            // if (Storage::exists($order->photo)) Storage::delete($order->photo);
            foreach ($request->upload_photo as $photo) {
                $orderImage = Orderimage::Create([
                    'image' => $photo->store('upload_photo'),
                    'order_id' => $request->order_id,
                ]);
                $exifService = new ExifMetadataService();
                $processImage = $exifService->removeExifMetadata($orderImage->image);
                if ($processImage) {
                    $orderImage->update([
                        'meta_remove_status' => 1,
                    ]);
                }
            }
        }

        return back()->with('success', 'Foto erfolgreich hochgeladen.');
    }

    public function VideoUpload(Request $request)
    {
        // ini_set('memory_limit', '512M');
        // ini_set('upload_max_filesize', '2000M');
        // ini_set('post_max_size', '2000M');
        // ini_set('max_execution_time', '1000');
        set_time_limit(1000);
        $request->validate([
            'order_id' => 'required',
        ]);
        if ($request->hasFile('video')) {

            $order = Order::where('id', $request->order_id)->first();
            if ($order->vendor_id != auth()->id()) {
                return back()->withError('Unauthorized access.');
            }

            if ($request->video) {
                if ($order->video !== null && Storage::disk('s3')->exists($order->video)) {
                    Storage::disk('s3')->delete($order->video);
                }

                if ($request->hasFile('video')) {
                    $order->update([
                        'video' => $request->video->store('Video'),
                        'video_uploaded_at' => now(),
                    ]);
                }

                // $exifService = new StripeVideoMetaData();
                // $processImage = $exifService->removeExifMetadata($order->video);

                // if ($processImage) {
                //     $order->update([
                //         'meta_remove_status' => 1,
                //     ]);
                // }
            }
        }


        return back()->with('success', 'Video erfolgreich hochgeladen.');
    }

    public function visibility()
    {
        $user = Auth()->user();
        if ($user->status == true) {
            $status = false;
        } else {
            $status = true;
        }
        $user->update([
            'status' => $status,
        ]);

        return back()->with('success', 'Sichtbarkeit erfolgreich geändert');
    }

    public function photos(Order $order)
    {
        return view('auth.seller.pages.photos', compact('order'));
    }

    public function photoDelete(Orderimage $orderimage)
    {
        Storage::delete($orderimage->image);
        $orderimage->delete();

        return back()->with('success', 'Foto erfolgreich gelöscht');
    }

    public function charges()
    {
        $boosts = Boost::where('user_id', auth()->id())
            ->latest()->get();

        return view('auth.seller.pages.charges', compact('boosts'));
    }

    public function chargeInvoice(Boost $boost)
    {
        return view('auth.seller.pages.charge_invoice', compact('boost'));
    }
}
