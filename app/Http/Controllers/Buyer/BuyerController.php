<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\Profile;
use App\Models\Verification;
use App\Notification;
use App\Order;


class BuyerController extends Controller
{
    public function dashboard()
    {
        return view('auth.buyer.pages.dashboard');
    }
    public function orders()
    {
        $orders = Order::where('user_id', Auth()->id())->children()->latest()->get();
        return view('auth.buyer.pages.orders', compact('orders'));
    }
    public function news()
    {
        
         $unseenNotifications=Notification::forMe()->get();
         foreach($unseenNotifications as $item){
              $item->update([
                'seen'=>1,
              ]);
         }
        $notifications = Notification::forMe()->latest()->get();
        // @dd($notifications);
        return view('auth.buyer.pages.news', compact('notifications'));
    }
    public function userData()
    {
        $profile = Profile::where('user_id', auth()->id())->first() ?? new Profile;
        return view('auth.buyer.pages.user_data', compact('profile'));
    }
    public function verify()
    {
        $data = Verification::where('user_id', auth()->id())->first() ?? new Profile;
        return view('auth.buyer.pages.verify', compact('data'));
    }
    public function address()
    {
        $address = Address::where('user_id', auth()->id())->first() ?? new Address;
        return view('auth.buyer.pages.address', compact('address'));
    }
    public function photos(Order $order)
    {
        $images = $order->orderimages;
        return view('auth.buyer.pages.photos', compact('images', 'order'));
    }
    public function videoPlayer(Order $order)  {
        if($order->user_id !== auth()->id()){
            return abort(403,'Unathorised');
        }
        return view('auth.buyer.pages.video_player',compact('order'));
    }
}
