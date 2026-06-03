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
    public function invoice(Order $order)
    {
        $products = $order->products;
        // if($order->user_id != auth()->id() || $user->vendor_id != auth()->id()){
        // 	 return redirect('/');
        // }
        return view('auth.invoice', compact('order', 'products'));
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
        return redirect()->route('seller.verification')->with('success', 'Thank, you your email verification was successfull');
    }
    public function verifyMassage(User $user)
    {
        return view('verify_massage', compact('user'));
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

        $order->update([
            'status' => 3,
           
        ]);
        if($order->product->selloption==true){
            $order->product->update([

                'status'=>true
            ]);
        }
  
        $year = now()->format('Y');
        $mail_data = [
            'subject' => 'Storno Bestellung FK' . $year . '-' . $order->id,
            'title' => 'Storno Bestellung FK' . $year . '-' . $order->id,
            'body' => "Hey du,<br><br>leider musste ich deinen Einkauf stornieren. Für die Unannehmlichkeit entschuldige ich mich.<br><br>Weitere Informationen erhälst du per E-Mail.",
            'button_link' => route('shop'),
            'button_text' => 'ein anderes Produkt bestellen',
        ];
        $mail_data2 = [
            'subject' => 'Storno Bestellung FK' . $year . '-' . $order->id,
            'title' => 'Storno Bestellung FK' . $year . '-' . $order->id,
            'body' => "Hallo,<br><br> da du deiner Vertragspflicht als Produzentin nicht nachgekommen bist und auch auf Fristen nicht reagiert hast, habe ich deinen Verkauf storniert.<br><br> Dein Konto auf FrauKruner.de wurde gelöscht.<br><br>",
            'button_link' => '',
            'button_text' => '',
        ];
        Mail::to($order->email)->send(new UserNotifyEmail($mail_data));
        Mail::to($order->vendor->email)->send(new UserNotifyEmail($mail_data2));
        return redirect('admin/order/lists')->with([
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
