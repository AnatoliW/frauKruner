<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Boost;
use App\Package;
use App\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Shop;

class BoostController extends Controller
{
    public function boostStore(Request $request, Product $product = null)
    {
        $request->validate([
            'package' => 'required',
        ]);
        $package = Package::find($request->package);
        $boost = null;
        $tax=Shop::tax($package->price);
        
        if ($product) {
            $boost = $product->boosts()->create([
                'package_id' => $package->id,
                'user_id' => auth()->id(),
                'price' => $package->price_with_tax,
                'base_price' => $package->price,
                'start_day' => Carbon::now(),
                'end_day' => Carbon::now()->addDays($package->days),
                'tax'=>$tax,
                'user_info'=>$this->userInfo(),
            ]);
        } else {
            $boost = auth()->user()->boosts()->create([
                'package_id' => $package->id,
                'user_id' => auth()->id(),
                'price' => $package->price_with_tax,
                'base_price' => $package->price,
                'start_day' => Carbon::now(),
                'end_day' => Carbon::now()->addDays($package->days),
                'tax'=>$tax,
                'user_info'=>$this->userInfo()
            ]);
        }
        $payment=$boost->payment()->create([
            'status' => 'PENDING',
            'tax' => $package->price * (setting('finance.vat') / 100) ,
            'amount' =>$package->price + ($package->price * (setting('finance.vat') / 100))
        ]);
        return redirect()->route('seller.payment',$payment);
    }

    protected function userInfo() {

        $user_info=[
            'f_name'=>auth()->user()->first_name ?? auth()->user()->name ,
            'l_name'=>auth()->user()->last_name ?? auth()->user()->verification->last_name,
            'street'=>auth()->user()->street ??  auth()->user()->verification->street ?? null,
            'house_no'=>auth()->user()->house_no ??  auth()->user()->verification->house_no ?? null,
            'zip'=>auth()->user()->zip ??  auth()->user()->verification->zip ?? null,
            'federal_state'=>auth()->user()->federal_state ??  auth()->user()->verification->federal_state ?? null,
            'email'=>auth()->user()->email,
            'vat_number'=>auth()->user()->vat,
            'is_pay_vat'=>auth()->user()->is_pay_vat,
            'vat_perchatage'=>setting('finance.vat'),
        ];
      
        return $user_info;
    }
}
