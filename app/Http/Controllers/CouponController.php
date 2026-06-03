<?php

namespace App\Http\Controllers;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Coupon;
use Cart;
use Illuminate\Support\Facades\Session;

class CouponController extends Controller
{
    public function add(request $request){
		$coupon = Coupon::where('code',$request->coupon_code)->first();
		if(!$coupon){

			return redirect()->back()->withErrors("Falscher Gutscheincode");
		}
		if(Carbon::create($coupon->expire_at) < now()){
			return redirect()->back()->withErrors("Gutschein ist abgelaufen");
			// session()->flash('errors', collect(['Gutschein ist abgelaufen']));
			// return back();
		}
		if($coupon->limit <= $coupon->used){
			return redirect()->back()->withErrors("Gutschein ist abgelaufen");

		}
		if(Cart::getSubTotal() < $coupon->minimum_cart){
			return redirect()->back()->withErrors("Mindesteinkaufswert erforderlich, um diesen Gutschein zu verwenden ".$coupon->minimum_cart);
			// session()->flash('errors', collect(['Mindesteinkaufswert erforderlich, um diesen Gutschein zu verwenden '.$coupon->minimum_cart]));
			// return back();
		}
		Session::put('discount', $coupon->discount);
		Session::put('discount_code', $coupon->code);
		$coupon->increment('used');

		return back()->with('success', 'Gutschein wurde erfolgreich angewendet');
	}
	public function destroy(){
		session()->forget('discount');
		session()->forget('discount_code');
		return back()->with('success', 'Gutschein erfolgreich entfernt');
	}
}
