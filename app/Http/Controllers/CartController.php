<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Cart;
use App\Product;
use Shop;
class CartController extends Controller
{
	public function add(Request $request)
	{
    
		$wearing_time = $request->wearing_time;
		$finishings = $request->finishings;
		$additions = $request->additions;

		$product = Product::find($request->product_id);
        
		if ( $product && Cart::get($product->id)) {
			return back()->withErrors( 'Produkt liegt bereits im Warenkorb.');
		}
		Log::create([
			'details'=>json_encode($product),
			'email'=>auth()->user()?->email,
			'admin_id'=>$product->id,
			'user_id'=>auth()->id(),
		   ]);


		$price = $product->price();
		$attributes = [];
        $wearing_time_cost=0;
        if ($wearing_time) {
            $wearing_time_cost = $product->wearing_time[$wearing_time];
			$attributes['Tragedauer'] = [$wearing_time.'-'.Shop::price($wearing_time_cost)];
		}
        $finishings_cost=0;
		if ($finishings) {
            foreach($finishings as $finishing){
                $veredelungen[] = $finishing.'-'.Shop::price($product->finishings[$finishing]) ;
                $finishings_cost= $finishings_cost + $product->finishings[$finishing];
            }
            //return $finishings_cost;
			 $attributes['veredelungen'] = $veredelungen;
		}
        $additions_cost=0;
		if ($additions) {
            foreach($additions as $addition){
                $Zusatzoptionen[] = $addition.'-'.Shop::price($product->addition[$addition]) ;
                $additions_cost= $additions_cost+ $product->addition[$addition];
            }
            //$additions_cost;
			$attributes['Zusatzoptionen'] = $Zusatzoptionen;
		}
        $total = $price + $wearing_time_cost +$finishings_cost + $additions_cost;
		
        $commission = Shop::commission($total,$product);
        $tax=Shop::tax($commission);
        $attributes['tax']=$tax;
        $attributes['commission']=$commission;
        $attributes['vendor_total']=$total;
        $final_total = $total + $tax + $commission;
		Cart::add($product->id, $product->name, $final_total, 1, $attributes)->associate('App\Product');
		return redirect('/cart')->with('success', 'Produkt wurde erfolgreich zum Warenkorb hinzugefügt!');
	}



	public function update(Request $request)
	{
		Cart::update($request->product_id, array(
			'quantity' => array(
				'relative' => false,
				'value' => $request->quantity
			),
		));
		return back()->with('success_msg', 'Produkt wurde erfolgreich aktualisiert!');
	}
	public function destroy($id)
	{
		Cart::remove($id);
		return back()->with('success_msg', 'Produkt wurde gelöscht!');
	}
}
